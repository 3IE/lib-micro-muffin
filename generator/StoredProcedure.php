<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 10:21
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

use Lib\Tools;

abstract class StoredProcedure
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $returnType;
    /** @var SPParameter[] */
    protected $parameters;

    /**
     * @param string        $name
     * @param string        $returnType
     * @param SPParameter[] $parameters
     */
    function __construct($name, $returnType, $parameters)
    {
        $this->name       = $name;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return 'SP_' . Tools::capitalize($this->name);
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
     * @param \Lib\Generator\SPParameter[] $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return \Lib\Generator\SPParameter[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @return string
     */
    public abstract function getCleanReturnType();
}