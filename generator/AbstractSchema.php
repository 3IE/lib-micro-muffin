<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mathieu.savy
 * Date: 21/08/13
 * Time: 11:52
 * To change this template use File | Settings | File Templates.
 */

namespace Lib\Generator;

use Lib\Tools;

class AbstractSchema
{
  /** @var Table[] */
  private $tables;

  /** @var \Twig_Environment */
  private $twig;

  public function __construct()
  {
    $this->tables = array();
    $this->twig   = null;
  }

  /**
   * @param \Lib\Generator\Table[] $tables
   */
  public function setTables($tables)
  {
    $this->tables = $tables;
  }

  /**
   * @return \Lib\Generator\Table[]
   */
  public function getTables()
  {
    return $this->tables;
  }

  private function init()
  {
    $twig_options = array('cache' => false, 'autoescape' => false, 'strict_variables' => true);

    $loader     = new \Twig_Loader_Filesystem(__DIR__ . '/layouts');
    $this->twig = new \Twig_Environment($loader, $twig_options);
  }

  public function writeFiles()
  {
    $this->init();

    $this->writeT_Models();
  }

  private function writeModels()
  {
  }

  private function writeT_Models()
  {
    foreach ($this->tables as $table)
    {
      $save_dir = __DIR__ . '/tmp/';
      $fileName = 't_' . Tools::removeSFromTableName($table->getName()) . '.php';

      $file = fopen($save_dir . $fileName, "w");
      fwrite($file, $this->T_ModelToString($table));
      fclose($file);
    }
  }

  private function T_ModelToString(Table $table)
  {
    $variables = array();

    $variables['className'] = $table->getT_ClassName();
    $variables['tableName'] = $table->getName();
    $variables['sequenceName'] = $table->getSequenceName();

    $str = '';
    foreach ($table->getPrimaryKey()->getFields() as $field)
      $str .= "'$field', ";
    $variables['primaryKey'] = substr($str, 0, -2);

    $variables['fields'] = $table->getFields();
    $variables['manyToOne'] = $table->getManyToOneJoins();
    $variables['oneToMany'] = $table->getOneToManyJoins();

    return $this->twig->render('t_model.php.twig', $variables);
  }

  private function writeStoredProcedures()
  {
  }
}