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

    $schema->setTables($this->readTables());

    $this->abstractSchema = $schema;
  }

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

  private function readConstraints()
  {
  }
}