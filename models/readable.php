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
  /** @var array */
  protected static $_fields = array();

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
          throw new \Exception('readable::handleOrder : Trying to order by a column that doesn\'t exist on '. static::getTableName());
      }

      return substr($sReturningOrder, 0, -2);
    }
    else if (is_string($order))
    {
      Log::write('Warning ! Readable::all(string) and Readable::take(int, int, string) are deprecated.
      Please use Readable::all(Array) and Readable::take(int, int, Array) instead.');

      return $order;
    }
    else
      return null;
  }

  /**
   * @param string $where_clause
   * @return self[]
   */
  public static function where($where_clause)
  {
    $class = strtolower(get_called_class());
    $proc  = static::$procstock_all != null ? static::$procstock_all : 'getall' . $class . 's';
    $pdo   = PDOS::getInstance();

    $query = $pdo->prepare('SELECT * FROM ' . $proc . '() WHERE ' . $where_clause);
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