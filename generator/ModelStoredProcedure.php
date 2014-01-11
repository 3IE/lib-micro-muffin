<?php
/**
 * Created by PhpStorm.
 * User: Mathieu
 * Date: 11/01/14
 * Time: 18:00
 */

namespace Lib\Generator;

use Lib\Tools;

class ModelStoredProcedure extends StoredProcedure
{
    /**
     * @return string
     */
    public function getCleanReturnType()
    {
        return Tools::capitalize(Tools::removeSFromTableName($this->returnType)) . '[]';
    }
}