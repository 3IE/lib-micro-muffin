<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mathieu
 * Date: 08/06/13
 * Time: 15:58
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Models;

use Lib\EPO;
use Lib\PDOS;
use Lib\Tools;

abstract class Writable extends Readable
{
  /** @var bool */
  private $_modified = true;
  /** @var string|null */
  protected static $sequence_name = null;

  protected function _objectEdited()
  {
    $this->_modified = true;
  }

  private function _objectNotEdited()
  {
    $this->_modified = false;
  }

  public function getModified()
  {
    return $this->_modified;
  }

  /**
   * Add or update the model in database
   *
   * @return void
   */
  public function save()
  {
    if ($this->_modified)
    {
      $reflection = new \ReflectionClass($this);
      $class      = $reflection->getShortName();
      $table      = self::$table_name != null ? self::$table_name : strtolower($class) . 's';

      $attributes = $this->getAttributes($reflection);

      //Joints model saving
      $modelAttributes = $this->getModelAttributes($reflection);
      foreach ($modelAttributes as $model)
        $model->save();

      $fields = '(';
      $values = '(';
      foreach ($attributes as $k => $v)
      {
        if ($k != 'id')
        {
          $fields .= $k . ', ';
          $values .= ':' . $k . ', ';
        }
      }
      $fields = substr($fields, 0, -2) . ')';
      $values = substr($values, 0, -2) . ')';

      $pdo = PDOS::getInstance();

      if ($this->_id == 0)
        $this->add($pdo, $table, $fields, $values, $attributes);
      else
        $this->update();
    }
  }

  /**
   * @param self[] $objects
   */
  public static function saveAll(Array $objects)
  {
    if (count($objects) == 0)
      return;

    /** @var self $class */
    $class        = '\\' . get_called_class();
    $table        = $class::getTableName();
    $attributes   = $objects[0]->getAttributes(new \ReflectionClass($objects[0]));
    $nbColumns    = count($attributes);
    $nbTotalRows  = count($objects);
    $rowsByInsert = 0;

    /* Building attribute => getter list */
    $attrGetter = array();
    $fields     = '(';
    foreach ($attributes as $k => $v)
    {
      if ($k != 'id')
      {
        $attrGetter[$k] = 'get' . Tools::capitalize($k);
        $fields .= $k . ', ';
      }
    }
    $fields = substr($fields, 0, -2) . ')';
    unset($attributes);

    /* Depending of how many columns we have to insert, we don't put the same rows number in a single query */
    if ($nbColumns > 0 && $nbColumns <= 4)
      $rowsByInsert = 50;
    else if ($nbColumns <= 10)
      $rowsByInsert = 10;
    else
      $rowsByInsert = 2;

    $rowsInPartialInsert = $nbTotalRows % $rowsByInsert;

    /* Constructing queries, one for a full insertion, one for remaining rows that cannont fill a full insert */
    $pdo        = PDOS::getInstance();
    $sql        = 'INSERT INTO ' . $table . ' ' . $fields . ' VALUES ';
    $sqlValues1 = '';
    $sqlValues2 = '';
    for ($i = 0; $i < $rowsByInsert; $i++)
    {
      $sqlValues1 .= '(';
      if ($i < $rowsInPartialInsert)
        $sqlValues2 .= '(';
      foreach ($attrGetter as $k => $v)
      {
        $sqlValues1 .= ':' . $k . '_' . $i . ', ';
        if ($i < $rowsInPartialInsert)
          $sqlValues2 .= ':' . $k . '_' . $i . ', ';
      }
      $sqlValues1 = substr($sqlValues1, 0, -2) . '), ';
      if ($i < $rowsInPartialInsert)
        $sqlValues2 = substr($sqlValues2, 0, -2) . '), ';
    }
    $sqlValues1 = substr($sqlValues1, 0, -2);
    $sqlValues2 = substr($sqlValues2, 0, -2);

    $queryFull = $pdo->prepare($sql . $sqlValues1);
    if ($rowsInPartialInsert > 0)
      $queryPartial = $pdo->prepare($sql . $sqlValues2);
    else
      $queryPartial = null;

    /* Executing loop, filling values and executing queries */
    $nbFullInserts = (int)($nbTotalRows / $rowsByInsert);

    $reflection = new \ReflectionClass(get_called_class());

    $pdo->beginTransaction();

    $i = 0;
    for ($j = 0; $j < $nbFullInserts; $j++)
    {
      for ($k = 0; $k < $rowsByInsert; $k++)
      {
        $object = $objects[$i];
        foreach ($attrGetter as $attr => $getter)
        {
          $method = $reflection->getMethod($getter);
          $method->setAccessible(true);
          $queryFull->bindValue(':' . $attr . '_' . $k, $object->$getter());
          $method->setAccessible(false);
        }
        $i++;
      }
      $queryFull->execute();
    }
    if ($rowsInPartialInsert > 0)
    {
      for ($k = 0; $k < $rowsInPartialInsert; $k++)
      {
        $object = $objects[$i];
        foreach ($attrGetter as $attr => $getter)
        {
          $method = $reflection->getMethod($getter);
          $method->setAccessible(true);
          $queryPartial->bindValue(':' . $attr . '_' . $k, $object->$getter());
          $method->setAccessible(false);
        }
        $i++;
      }
      $queryPartial->execute();
    }

    $pdo->commit();
  }

  /**
   * @param \Lib\EPO $pdo
   * @param $table
   * @param string $fields
   * @param string $values
   * @param array $attributes
   */
  private function add(EPO $pdo, $table, $fields, $values, Array $attributes)
  {
    $query = 'INSERT INTO ' . $table . ' ' . $fields . ' VALUES ' . $values;

    $pdo->beginTransaction();
    $query = $pdo->prepare($query);
    foreach ($attributes as $k => $v)
    {
      if ($k != 'id')
      {
        if (is_bool($v))
          $query->bindValue(':' . $k, $v, \PDO::PARAM_BOOL);
        else
          $query->bindValue(':' . $k, $v);
      }
    }
    $query->execute();
    $this->setId($pdo->lastInsertId(static::$sequence_name));
    $pdo->commit();
  }

  protected abstract function update();
}