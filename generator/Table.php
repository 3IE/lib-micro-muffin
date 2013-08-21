<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 10:21
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class Table
{
  /** @var string */
  private $name;

  /** @var Field[] */
  private $fields;

  /** @var Constraint[] */
  private $constraints;
}