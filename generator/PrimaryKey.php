<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class PrimaryKey extends Constraint
{
  /** @var array */
  private $fields;

  /**
   * @param string $fields
   */
  public function setFields($fields)
  {
    $this->fields = $fields;
  }

  /**
   * @return array
   */
  public function getFields()
  {
    return $this->fields;
  }

  /**
   * @param string $field
   */
  public function addField($field)
  {
    $this->fields[] = $field;
  }
}