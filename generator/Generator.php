<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

use Lib\MicroMuffin;

class Generator
{
    const RELATIVE_MODEL_SAVE_DIR    = '/../../app/model/';
    const RELATIVE_T_MODEL_SAVE_DIR  = '/../../app/t_model/';
    const RELATIVE_SP_MODEL_SAVE_DIR = '/../../app/sp_model/';
    const W_CHMOD                    = 640;

    private static function init()
    {
        require_once(__DIR__ . '/../autoloader.php');
        require_once(__DIR__ . '/../config.php');
        require_once(__DIR__ . '/../../config/config.php');
    }

    private static function emptyDirectory($dirName)
    {
        /** @var $file \DirectoryIterator */
        foreach (new \DirectoryIterator($dirName) as $file)
        {
            if (!$file->isDot() && $file->getFilename() != 'empty')
            {
                chmod($file->getPathname(), self::W_CHMOD);
                unlink($file->getPathname());
            }
        }
    }

    public static function run()
    {
        self::init();
        self::emptyDirectory(__DIR__ . self::RELATIVE_T_MODEL_SAVE_DIR);
        self::emptyDirectory(__DIR__ . self::RELATIVE_SP_MODEL_SAVE_DIR);

        $driver = MicroMuffin::getDBDriver();
        $schema = $driver->getAbstractSchema();

        //var_dump($schema);

        $schema->writeFiles();
    }
}