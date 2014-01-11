<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

use Lib\MicroMuffin;

class Generator
{
  private static function init()
  {
    require_once(__DIR__ . '/../autoloader.php');
    require_once(__DIR__ . '/../config.php');
    require_once(__DIR__ . '/../../config/config.php');
  }

  public static function run()
  {
    self::init();

    $driver = MicroMuffin::getDBDriver();
    $schema = $driver->getAbstractSchema();

    //var_dump($schema);

    //$schema->writeFiles();
  }
}