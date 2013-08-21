<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class ForeignKey
{
  /** @var Field */
  private $field;

  /** @var Field */
  private $targetField;

  /** @var Table */
  private $targetTable;
}