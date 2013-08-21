<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

abstract class Driver
{
  /** @var AbstractSchema */
  protected $abstractSchema;

  protected abstract function readDatabaseSchema();

  /**
   * @return AbstractSchema
   */
  public function getAbstractSchema()
  {
    $this->readDatabaseSchema();

    return $this->abstractSchema;
  }
}