<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 10:23
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class Field
{
  /** @var string */
  private $name;

  /** @var string */
  private $type;

  /** @var string */
  private $defaultValue;

  public function __construct($name)
  {
    $this->name         = $name;
    $this->type         = null;
    $this->defaultValue = null;
  }

  /**
   * @param string $defaultValue
   */
  public function setDefaultValue($defaultValue)
  {
    $this->defaultValue = $defaultValue;
  }

  /**
   * @return string
   */
  public function getDefaultValue()
  {
    return $this->defaultValue;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $type
   */
  public function setType($type)
  {
    $this->type = $type;
  }

  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
}