<?php
/**
 * Created by PhpStorm.
 * User: Mathieu
 * Date: 11/01/14
 * Time: 17:37
 */

namespace Lib\Generator;

class RecordStoredProcedure extends StoredProcedure implements ISPReturnClass
{

    /**
     * @return string
     */
    public function getCleanReturnType()
    {
        return $this->getReturnedClassName() . '[]';
    }

    public function getReturnedClassName()
    {
        return $this->getClassName();
    }
}