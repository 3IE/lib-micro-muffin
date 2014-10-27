<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 10:21
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin\Generator;

use MicroMuffin\Tools;

class Table
{
  /** @var string */
  private $name;

  /** @var Field[] */
  private $fields;

  /** @var PrimaryKey */
  private $primaryKey;

  /** @var OneToMany[] */
  private $oneToManyJoins;

  /** @var ManyToOne[] */
  private $manyToOneJoins;

  /**
   * @param string $name
   */
  public function __construct($name)
  {
    $this->name         = $name;
    $this->fields       = array();
    $this->primaryKey   = null;
  }

  /**
   * @return string
   */
  public function getT_ClassName()
  {
    return 'T_' . Tools::capitalize(Tools::removeSFromTableName($this->name));
  }

  /**
   * @return string
   */
  public function getClassName()
  {
    return Tools::capitalize(Tools::removeSFromTableName($this->name));
  }

  /**
   * @param ManyToOne $mto
   */
  public function addManyToOne(ManyToOne $mto)
  {
    $this->manyToOneJoins[] = $mto;
  }

  /**
   * @param OneToMany $otm
   */
  public function addOneToMany(OneToMany $otm)
  {
    $this->oneToManyJoins[] = $otm;
  }

  /**
   * @param \MicroMuffin\Generator\ManyToOne[] $manyToOneJoins
   */
  public function setManyToOneJoins($manyToOneJoins)
  {
    $this->manyToOneJoins = $manyToOneJoins;
  }

  /**
   * @return \MicroMuffin\Generator\ManyToOne[]
   */
  public function getManyToOneJoins()
  {
    return $this->manyToOneJoins;
  }

  /**
   * @param \MicroMuffin\Generator\OneToMany[] $oneToManyJoins
   */
  public function setOneToManyJoins($oneToManyJoins)
  {
    $this->oneToManyJoins = $oneToManyJoins;
  }

  /**
   * @return \MicroMuffin\Generator\OneToMany[]
   */
  public function getOneToManyJoins()
  {
    return $this->oneToManyJoins;
  }

  /**
   * @param Field $field
   */
  public function addField(Field $field)
  {
    $this->fields[] = $field;
  }

  /**
   * @param \MicroMuffin\Generator\Field[] $fields
   */
  public function setFields($fields)
  {
    $this->fields = $fields;
  }

  /**
   * @return \MicroMuffin\Generator\Field[]
   */
  public function getFields()
  {
    return $this->fields;
  }

  public function getField($name)
  {
    foreach ($this->fields as $field)
    {
      if ($field->getName() == $name)
        return $field;
    }
    return null;
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
   * @param \MicroMuffin\Generator\PrimaryKey $primaryKey
   */
  public function setPrimaryKey($primaryKey)
  {
    $this->primaryKey = $primaryKey;
  }

  /**
   * @return \MicroMuffin\Generator\PrimaryKey
   */
  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }
}