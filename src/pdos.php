<?php
/**
 * Created by JetBrains PhpStorm.
 * User: savy_m
 * Date: 24/05/13
 * Time: 16:21
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin;

class PDOS
{
    private static $nbQuery_ = 0;
    private static $instance_;

    /**
     * @throws \Exception
     * @return EPO
     */
    public static function getInstance()
    {
        if (!isset($_instance))
        {
            try
            {
                self::$instance_ = new EPO('pgsql:host=' . DBHOST . ';dbname=' . DBNAME, DBUSER, DBPASS);
                self::$instance_->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                if (DISPLAY_SQL_ERROR)
                {
                    self::$instance_->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                }

            } catch (\PDOException $e)
            {
                $error  = 'Error : ' . $e->getMessage() ;
                throw new \Exception($error);
            }
        }
        return self::$instance_;
    }

    static function incNbQuery()
    {
        self::$nbQuery_++;
    }

    /**
     * @return int
     */
    public static function getNbQuery()
    {
        return self::$nbQuery_;
    }
}