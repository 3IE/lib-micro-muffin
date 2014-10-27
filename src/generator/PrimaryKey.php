<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin\Generator;

class PrimaryKey extends Constraint
{
  /** @var Field[] */
  private $fields;

  /**
   * @param Field[] $fields
   */
  public function setFields($fields)
  {
    $this->fields = $fields;
  }

  /**
   * @return Field[]
   */
  public function getFields()
  {
    return $this->fields;
  }

  /**
   * @param Field $field
   */
  public function addField(Field $field)
  {
    $this->fields[] = $field;
  }
}