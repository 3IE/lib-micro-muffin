<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 22/08/13
 * Time: 10:43
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class ManyToOne
{
  /** @var string  */
  private $field;

  /** @var string */
  private $targetField;

  /** @var string  */
  private $targetTable;

  /**
   * @param string $field
   * @param string $targetField
   * @param string $targetTable
   */
  function __construct($field, $targetField, $targetTable)
  {
    $this->field       = $field;
    $this->targetField = $targetField;
    $this->targetTable = $targetTable;
  }

  /**
   * @param string $field
   */
  public function setField($field)
  {
    $this->field = $field;
  }

  /**
   * @return string
   */
  public function getField()
  {
    return $this->field;
  }

  /**
   * @param string $targetField
   */
  public function setTargetField($targetField)
  {
    $this->targetField = $targetField;
  }

  /**
   * @return string
   */
  public function getTargetField()
  {
    return $this->targetField;
  }

  /**
   * @param string $targetTable
   */
  public function setTargetTable($targetTable)
  {
    $this->targetTable = $targetTable;
  }

  /**
   * @return string
   */
  public function getTargetTable()
  {
    return $this->targetTable;
  }
}