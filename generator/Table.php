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

  /** @var PrimaryKey */
  private $primaryKey;

  /**
   * All constraints - Primary Key excluded
   * @var Constraint[]
   */
  private $constraints;

  /** @var string */
  private $sequenceName;

  /**
   * @param string $name
   */
  public function __construct($name)
  {
    $this->name         = $name;
    $this->fields       = array();
    $this->primaryKey   = null;
    $this->constraints  = array();
    $this->sequenceName = null;
  }

  /**
   * @param Field $field
   */
  public function addField(Field $field)
  {
    $this->fields[] = $field;
  }

  /**
   * @param \Lib\Generator\Constraint[] $constraints
   */
  public function setConstraints($constraints)
  {
    $this->constraints = $constraints;
  }

  /**
   * @return \Lib\Generator\Constraint[]
   */
  public function getConstraints()
  {
    return $this->constraints;
  }

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
   * @param \Lib\Generator\PrimaryKey $primaryKey
   */
  public function setPrimaryKey($primaryKey)
  {
    $this->primaryKey = $primaryKey;
  }

  /**
   * @return \Lib\Generator\PrimaryKey
   */
  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }

  /**
   * @param string $sequenceName
   */
  public function setSequenceName($sequenceName)
  {
    $this->sequenceName = $sequenceName;
  }

  /**
   * @return string
   */
  public function getSequenceName()
  {
    return $this->sequenceName;
  }
}