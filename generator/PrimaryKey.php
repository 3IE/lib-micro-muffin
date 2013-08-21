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
  /** @var Field[] */
  private $fields;

  /**
   * @param \Lib\Generator\Field[] $fields
   */
  public function setFields($fields)
  {
    $this->fields = $fields;
  }

  /**
   * @return \Lib\Generator\Field[]
   */
  public function getFields()
  {
    return $this->fields;
  }

  public function addField(Field $field)
  {
    $this->fields[] = $field;
  }
}