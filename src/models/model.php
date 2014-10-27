<?php
/**
 * User: mathieu.savy
 * Date: 25/05/13
 * Time: 16:41
 */

namespace MicroMuffin\Models;

abstract class Model
{
  /** @var string|null */
  protected static $_table_name = null;
  /** @var array */
  protected static $_fields = array();
  /** @var array */
  protected static $_primary_keys = array();
  /** @var array */
  protected static $_sequences = array();

  /**
   * @return string
   */
  public static function getTableName()
  {
    return static::$_table_name;
  }

  /**
   * @return string JSON object
   */
  public function toJson()
  {
    $reflection = new \ReflectionClass($this);
    $attributes = $this->getAttributes($reflection);
    return json_encode($attributes);
  }

  /**
   * @param \ReflectionClass $r
   * @return array
   */
  public function getAttributes(\ReflectionClass $r)
  {
    $attributes       = array();
    $class            = $r->getShortName();

    foreach ($r->getProperties() as $att)
    {
      if ($att->class == 'T_' . $class)
      {
        $name = $att->name;
        if (in_array(substr($name, 1), static::$_fields))
        {
          $property = $r->getProperty($name);
          $property->setAccessible(true);
          $attributes[substr($name, 1)] = $property->getValue($this);
          $property->setAccessible(false);
        }
      }
    }
    return $attributes;
  }

  /**
   * @param \ReflectionClass $r
   * @return Writable[]
   */
  protected function getModelAttributes(\ReflectionClass $r)
  {
    $attributes = array();

    foreach ($r->getProperties() as $att)
    {
      $att->setAccessible(true);
      if (!$att->isPrivate() && !$att->isStatic())
      {
        $object = $att->getValue($this);
        if ($object instanceof Writable && $object->getMMModified())
          $attributes[] = $object;
      }
      $att->setAccessible(false);
    }

    return $attributes;
  }

  /**
   * @param Model $object
   * @param $data
   * @return void
   */
  protected static function hydrate(Model &$object, $data)
  {
    $r = new \ReflectionClass($object);
    foreach ($data as $k => $v)
    {
      $k[0]       = strtoupper($k[0]);
      $methodName = "set" . $k;
      if ($r->hasMethod($methodName))
      {
        $method = $r->getMethod($methodName);
        $method->setAccessible(true);
        $method->invoke($object, $v);
        $method->setAccessible(false);
      }
    }

    /**
     * The object come from the database so it's not edited, but setter were used, so we need to restore
     * the _modified state by calling private function _objectNotModified
     */
    if ($r->hasMethod("_objectNotEdited"))
    {
      $method = $r->getMethod("_objectNotEdited");
      $method->setAccessible(true);
      $method->invoke($object);
      $method->setAccessible(false);
    }
  }
}