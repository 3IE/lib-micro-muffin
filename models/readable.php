<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mathieu
 * Date: 08/06/13
 * Time: 15:58
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Models;

use Lib\Log;
use Lib\PDOS;

abstract class Readable extends Model
{
  const WHERE_MODE_AND = 1;
  const WHERE_MODE_OR  = 2;

  /** @var string|null */
  protected static $procstock_find = null;
  /** @var string|null */
  protected static $procstock_all = null;
  /** @var string|null */
  protected static $procstock_count = null;
  /** @var string|null */
  protected static $procstock_take = null;
  /** @var array */
  protected static $primary_keys = array();

  /**
   * Find all models in database
   *
   * @param array|string $order
   * @return self[]
   */
  public static function all($order = null)
  {
    $class = strtolower(get_called_class());
    $proc  = self::$procstock_all != null ? self::$procstock_all : $class . 's';
    $pdo   = PDOS::getInstance();

    $order = self::handleOrder($order);

    if (is_null($order))
      $query = $pdo->prepare('SELECT * FROM getall' . $proc . '()');
    else
      $query = $pdo->prepare('SELECT * FROM getall' . $proc . '() ORDER BY ' . $order);
    $query->execute();

    $datas = $query->fetchAll();

    $outputs = array();
    foreach ($datas as $d)
    {
      $object = new $class();
      self::hydrate($object, $d);
      $outputs[] = $object;
    }
    return $outputs;
  }

  /**
   * @param array|string $order
   * @throws \Exception
   * @return string|null
   */
  private static function handleOrder($order)
  {
    if (is_array($order))
    {
      $sReturningOrder = '';
      foreach ($order as $sRow)
      {
        $aChunks = explode(' ', $sRow);
        if (in_array($aChunks[0], static::$_fields))
        {
          $sReturningOrder .= $aChunks[0];
          if (count($aChunks) > 1)
          {
            $direction = $aChunks[1];
            if (strtolower($direction) == 'asc' || strtolower($direction) == 'desc')
              $sReturningOrder .= ' ' . $direction;
          }
          $sReturningOrder .= ', ';
        }
        else
          throw new \Exception('readable::handleOrder : Trying to order by a column that doesn\'t exist on ' . static::getTableName());
      }

      return substr($sReturningOrder, 0, -2);
    }
    else if (is_string($order))
    {
      Log::write('Warning ! Readable::all(string) and Readable::take(int, int, string) are deprecated !
      Please use Readable::all(Array) and Readable::take(int, int, Array) instead.');

      return $order;
    }
    else
      return null;
  }

  /**
   * @param array|string $where_clause
   * @param array $order
   * @param int $where_mode
   * @throws \Exception
   * @return self[]
   */
  public static function where($where_clause, $order = null, $where_mode = self::WHERE_MODE_AND)
  {
    $class = strtolower(get_called_class());
    $proc  = static::$procstock_all != null ? static::$procstock_all : 'getall' . $class . 's';
    $pdo   = PDOS::getInstance();

    $sWhereClause = '';
    if (is_array($where_clause))
    {
      $sSeparator = $where_mode == self::WHERE_MODE_OR ? 'OR' : 'AND';

      //array(array(field, operator, value), array(field, operator, value))
      foreach ($where_clause as $aCondition)
      {
        if (is_array($aCondition) && count($aCondition) == 3)
        {
          $column   = $aCondition[0];
          $operator = $aCondition[1];
          $value    = $aCondition[2];

          if (in_array($column, static::$_fields))
          {
            $valid_operators = array('=', '>', '<', '>=', '<=', '<>', 'IS', 'IS NOT', 'LIKE');
            if (in_array($operator, $valid_operators))
            {
              if (is_bool($value))
                $value = $value ? 'true' : 'false';
              else if (is_string($value))
              {
                $value = pg_escape_string($value);
                $value = '\'' . $value . '\'';
              }
              else if (is_null($value))
                $value = 'NULL';

              $sWhereClause .= $column . ' ' . $operator . ' ' . $value . ' ' . $sSeparator . ' ';
            }
            else
              throw new \Exception('Readable::where : operator ' . $operator . ' is not a valid operator. Authorized operators are : ' . implode(', ', $valid_operators));
          }
          else
            throw new \Exception('Readable::where : ' . $column . ' is not a column of table ' . static::getTableName());
        }
        else
          throw new \Exception('Readable::where : Conditions must be arrays of this kind array(column, operator, value)');
      }

      //Removing that last AND or OR
      $sWhereClause = substr($sWhereClause, 0, -1 * (strlen($sSeparator) + 2));
    }
    else
    {
      Log::write('Warning ! Readable::where(string) is deprecated !
      Please use Readable::where(Array) or stored procedures for advanced queries.');
      $sWhereClause = $where_clause;
    }

    $order = self::handleOrder($order);
    if (!is_null($order))
      $order = ' ORDER BY ' . $order;

    $query = $pdo->prepare('SELECT * FROM ' . $proc . '() WHERE ' . $sWhereClause . $order);
    $query->execute();

    $datas = $query->fetchAll();

    $outputs = array();
    foreach ($datas as $d)
    {
      $object = new $class();
      self::hydrate($object, $d);
      $outputs[] = $object;
    }
    return $outputs;
  }

  /**
   * @return int
   */
  public static function count()
  {
    $class = strtolower(get_called_class());
    $proc  = self::$procstock_count != null ? self::$procstock_count : 'count' . $class . 's';
    $pdo   = PDOS::getInstance();

    $query = $pdo->prepare('SELECT * FROM ' . $proc . '()');
    $query->execute();

    $result = $query->fetch();

    return intval($result[$proc]);
  }

  /**
   * @param $number
   * @param int $offset
   * @param array|string $order
   * @return $this[]
   */
  public static function take($number, $offset = 0, $order = null)
  {
    $class = strtolower(get_called_class());
    $proc  = self::$procstock_take != null ? self::$procstock_count : 'take' . $class . 's';
    $pdo   = PDOS::getInstance();

    $order = self::handleOrder($order);

    $query = $pdo->prepare('SELECT * FROM ' . $proc . '(:start, :number, :order)');
    $query->bindValue(':start', $offset);
    $query->bindValue(':number', $number);
    $query->bindValue(':order', is_null($order) ? 'null' : $order);
    $query->execute();

    $datas   = $query->fetchAll();
    $outputs = array();
    foreach ($datas as $d)
    {
      $object = new $class();
      self::hydrate($object, $d);
      $outputs[] = $object;
    }
    return $outputs;
  }
}