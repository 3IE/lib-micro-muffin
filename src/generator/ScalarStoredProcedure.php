<?php
/**
 * Created by PhpStorm.
 * User: Mathieu
 * Date: 11/01/14
 * Time: 17:36
 */

namespace MicroMuffin\Generator;

class ScalarStoredProcedure extends StoredProcedure
{

    public function getCleanReturnType()
    {
        return 'array';
    }
}