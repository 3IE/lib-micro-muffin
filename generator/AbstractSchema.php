<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:52
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

class AbstractSchema
{
  /** @var Table[] */
  private $tables;

  /**
   * @param \Lib\Generator\Table[] $tables
   */
  public function setTables($tables)
  {
    $this->tables = $tables;
  }

  /**
   * @return \Lib\Generator\Table[]
   */
  public function getTables()
  {
    return $this->tables;
  }

  public function writeModels()
  {

  }

  public function writeStoredProcedures()
  {

  }
}