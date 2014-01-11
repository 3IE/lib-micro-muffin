<?php
/**
 * Created by PhpStorm.
 * User: Mathieu
 * Date: 11/01/14
 * Time: 17:37
 */

namespace Lib\Generator;

class RecordStoredProcedure extends StoredProcedure
{

    /**
     * @return string
     */
    public function getCleanReturnType()
    {
        return $this->getClassName() . '[]';
    }
}