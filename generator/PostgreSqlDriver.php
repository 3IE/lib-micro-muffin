<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

use Lib\PDOS;

class PostgreSqlDriver extends Driver
{
  protected function readDatabaseSchema()
  {
    $schema = new AbstractSchema();

    $tables = $this->readTables();
    $this->readPrimaryKeys($tables);
    $schema->setTables($tables);

    $this->abstractSchema = $schema;
  }

  /**
   * @return Table[]
   */
  private function readTables()
  {
    $pdo = PDOS::getInstance();

    //Getting all fields of all tables from selected schema
    $query = $pdo->prepare("
    SELECT
      table_name,
      column_name,
      column_default,
      pg_get_serial_sequence(table_name, column_name) AS sequence_name
    FROM
      information_schema.columns
    WHERE
      table_schema = '" . DBSCHEMA . "'
    ORDER BY
      table_name");
    $query->execute();

    $fields = $query->fetchAll();

    /** @var Table[] $tables */
    $tables = array();

    foreach ($fields as $f)
    {
      //Creating table if it doesn't exist yet
      if (!array_key_exists($f['table_name'], $tables))
      {
        $tables[$f['table_name']] = new Table($f['table_name']);
      }

      $table = & $tables[$f['table_name']];
      $field = new Field($f['column_name']);
      $field->setDefaultValue(is_null($f['sequence_name']) ? $f['column_default'] : null);

      if (!is_null($f['sequence_name']))
      {
        $array = explode(DBSCHEMA . '.', $f['sequence_name']);
        $table->setSequenceName($array[1]);
        $field->setHasSequence(true);
      }
      $table->addField($field);
    }

    return $tables;
  }

  /**
   * @param Table[] $tables
   */
  private function readPrimaryKeys(&$tables)
  {
    $pdo = PDOS::getInstance();

    $query = $pdo->prepare("
    SELECT
        tc.table_name,
        ccu.column_name,
        c.data_type
    FROM
        information_schema.table_constraints AS tc
        INNER JOIN information_schema.constraint_column_usage AS ccu ON tc.constraint_name = ccu.constraint_name
        INNER JOIN information_schema.columns AS c ON c.column_name = ccu.column_name AND c.table_name = tc.table_name
    WHERE tc.constraint_type = 'PRIMARY KEY' AND tc.constraint_schema = '" . DBSCHEMA . "'");
    $query->execute();

    /** @var PrimaryKey[] $primaryKeys */
    $primaryKeys = array();

    /*
     * There is one primary key by table, but it can contains several columns, each iteration of this loop is for a column
     */
    foreach ($query->fetchAll() as $pk)
    {
      if (!array_key_exists($pk['table_name'], $primaryKeys))
        $primaryKeys[$pk['table_name']] = new PrimaryKey();
      $object = & $primaryKeys[$pk['table_name']];
      $object->addField($pk['column_name']);
    }

    foreach ($primaryKeys as $table => $pk)
    {
      if (array_key_exists($table, $tables))
        $tables[$table]->setPrimaryKey($pk);
    }
  }
}