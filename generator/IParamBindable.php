<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 18/10/13
 * Time: 22:06
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

interface IParamBindable
{
  /**
   * @param \PDOStatement $statement
   * @param string $sParamName
   * @param mixed $paramValue
   * @return bool
   */
  public function bindPDOValue(\PDOStatement &$statement, $sParamName, $paramValue);
}