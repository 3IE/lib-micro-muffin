<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin\Generator;

use MicroMuffin\MicroMuffin;
use MicroMuffin\PDOS;

class Generator
{
    const RELATIVE_MODEL_SAVE_DIR    = '/../../app/model/';
    const RELATIVE_T_MODEL_SAVE_DIR  = '/../../app/t_model/';
    const RELATIVE_SP_MODEL_SAVE_DIR = '/../../app/sp_model/';
    const W_CHMOD                    = 640;

    private static function init()
    {
        require_once(__DIR__ . '/../MicroMuffin.php');
        MicroMuffin::init();
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

    private static function writeLine($str, $color = null)
    {
        if ($color == 'green')
            $str = "\033[0;32m" . $str . "\033[0m";
        else if ($color == 'red')
            $str = "\033[41m" . $str . "\033[0m";

        echo $str . "\n";
    }

    public static function run()
    {
        self::init();
        self::writeLine("micro-muffin v" . LIB_VERSION_NUMBER . " generator");
        self::writeLine("Emptying t_model directory");
        self::emptyDirectory(__DIR__ . self::RELATIVE_T_MODEL_SAVE_DIR);
        self::writeLine("Emptying sp_model directory");
        self::emptyDirectory(__DIR__ . self::RELATIVE_SP_MODEL_SAVE_DIR);

        self::writeLine("Connecting to " . DBNAME . " on " . DBHOST . "...");
        try
        {
            $pdo    = PDOS::getInstance();
            $driver = MicroMuffin::getDBDriver();
            self::writeLine("Success !", 'green');
            self::writeLine("Retrieving database " . DBSCHEMA . " schema...");
            $schema = $driver->getAbstractSchema();
            self::writeLine(count($schema->getTables()) . " tables founds");

            self::writeLine("Writing models...");
            $schema->writeFiles();
            self::writeLine("Done !");
            self::writeLine("Enjoy ! ;)");
        }
        catch (\Exception $e)
        {
            self::writeLine("Error ! Connection to database failed.", 'red');
            self::writeLine("Error returned : " . $e->getMessage(), 'red');
            die();
        }
    }
}