<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class Generator
{
  public static function run()
  {
    $driver = new PostgreSqlDriver();
    $schema = $driver->getAbstractSchema();

    var_dump($schema);

    $schema->writeFiles();
  }
}