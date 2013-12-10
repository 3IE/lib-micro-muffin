<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 17/05/13
 * Time: 11:41
 * To change this template use File | Settings | File Templates.
 */

namespace Lib;

class Log
{
  private static $instance;
  private $file;

  private static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new Log();

      if (self::$instance->file)
      {
        fwrite(self::$instance->file, "\n====== ");
        fwrite(self::$instance->file, "Starting execution ");
        fwrite(self::$instance->file, "======\n");
      }
    }

    return self::$instance;
  }

  private function __construct()
  {
    $sDirPath  = __DIR__ . "/../log";
    $sFilePath = $sDirPath . "/real-time.log";

    if (is_writable($sDirPath))
    {
      if (!file_exists($sFilePath) || is_writable($sFilePath))
        $this->file = fopen($sFilePath, "a");
      else
        $this->file = fopen('0-' . $sFilePath, 'a');
    }
  }

  public function __destruct()
  {
    if ($this->file)
      fclose($this->file);
  }

  /**
   * @param $string
   */
  public static function write($string)
  {
    $instance = self::getInstance();

    if ($instance->file)
    {
      $date = date("d/m H:i:s");
      fwrite($instance->file, "[" . $date . "]" . $string . "\n");
    }
  }

  /**
   * @param mixed $source
   * @return string
   */
  public static function dumpInVar($source)
  {
    ob_start();
    var_export($source);
    return ob_get_clean();
  }
}