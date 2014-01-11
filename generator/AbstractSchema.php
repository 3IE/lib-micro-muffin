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

    /** @var StoredProcedure[] */
    private $storedProcedures;

    /** @var \Twig_Environment */
    private $twig;

    /** @var Driver */
    private $driver;

    public function __construct(Driver $driver)
    {
        $this->tables = array();
        $this->twig   = null;
        $this->driver = $driver;
    }

    /**
     * @param \Lib\Generator\StoredProcedure[] $storedProcedures
     */
    public function setStoredProcedures($storedProcedures)
    {
        $this->storedProcedures = $storedProcedures;
    }

    /**
     * @return \Lib\Generator\StoredProcedure[]
     */
    public function getStoredProcedures()
    {
        return $this->storedProcedures;
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
        $this->twig->addFilter("removeS", new \Twig_Filter_Function("\\Lib\\Tools::removeSFromTableName"));
    }

    public function writeFiles()
    {
        $this->init();

        $this->writeT_Models();
        $this->writeModels();
        $this->writeSPModels();
    }

    private function writeSPModels()
    {
        $save_dir = __DIR__ . '/tmp/';
        foreach ($this->storedProcedures as $sp)
        {
            $fileName = 'sp_' . $sp->getName() . '.php';

            $file = fopen($save_dir . $fileName, "w");
            fwrite($file, $this->SP_ModelToString($sp));
            fclose($file);
        }
    }

    private function writeModels()
    {
        $save_dir = __DIR__ . '/tmp/';
        foreach ($this->tables as $table)
        {
            $fileName = '' . Tools::removeSFromTableName($table->getName()) . '.php';

            $file = fopen($save_dir . $fileName, "w");
            fwrite($file, $this->ModelToString($table));
            fclose($file);
        }
    }

    private function writeT_Models()
    {
        $save_dir = __DIR__ . '/tmp/';
        foreach ($this->tables as $table)
        {
            $fileName = 't_' . Tools::removeSFromTableName($table->getName()) . '.php';

            $file = fopen($save_dir . $fileName, "w");
            fwrite($file, $this->T_ModelToString($table));
            fclose($file);
        }
    }

    private function SP_ModelToString(StoredProcedure $sp)
    {
        $variables = array();

        $variables['className']  = $sp->getClassName();
        $variables['modelClass'] = '\\Lib\\Models\\Model';
        $variables['name']       = $sp->getName();
        $variables['returnType'] = $sp->getCleanReturnType();

        if ($sp instanceof ScalarStoredProcedure)
            $variables['fetchMode'] = 'PDO::FETCH_COLUMN';
        else
            $variables['fetchMode'] = '';

        //TODO : proto and sp params. Out params binding to attributes for record sp

        return $this->twig->render('sp_model.php.twig', $variables);
    }

    private function ModelToString(Table $table)
    {
        $variables              = array();
        $variables['className'] = $table->getClassName();

        return $this->twig->render('model.php.twig', $variables);
    }

    private function T_ModelToString(Table $table)
    {
        $variables = array();

        $variables['tClassName']     = $table->getT_ClassName();
        $variables['finalClassName'] = $table->getClassName();
        $variables['tableName']      = $table->getName();

        $str = '';
        foreach ($table->getPrimaryKey()->getFields() as $field)
            $str .= '\'' . $field->getName() . '\', ';
        $variables['primaryKey'] = substr($str, 0, -2);

        $str = '';
        foreach ($table->getFields() as $field)
            $str .= '\'' . $field->getName() . '\', ';
        $variables['field_list'] = substr($str, 0, -2);

        $variables['fields']    = $this->removeJoinsFields($table->getFields(), $table->getManyToOneJoins());
        $variables['manyToOne'] = $table->getManyToOneJoins();

        $otm = $table->getOneToManyJoins();
        if (!is_null($otm))
        {
            foreach ($table->getOneToManyJoins() as $o)
            {
                $cleanName = $o->getTargetField();
                if (substr($cleanName, strlen($cleanName) - 3) == '_id')
                    $cleanName = substr($cleanName, 0, -3);
                else if (substr($cleanName, strlen($cleanName) - 2) == 'Id')
                    $cleanName = substr($cleanName, 0, -2);
                $cleanName = strtolower($cleanName);

                $o->setCleanField($cleanName);
                $procedureName = $this->driver->writeOneToManyProcedure($o->getTargetTable(), $o->getTargetField(), $cleanName,
                    $table->getName(), $table->getField($o->getField())->getType());
                $o->setProcedureName($procedureName);
            }
        }
        $variables['oneToMany'] = is_null($otm) ? array() : $table->getOneToManyJoins();

        //Find
        $find_params      = '';
        $find_proto       = '';
        $find_placeholder = '';
        $find_checkNull   = '';
        $find_result      = '$result';

        foreach ($table->getPrimaryKey()->getFields() as $f)
        {
            $find_params .= " * @param \$" . $f->getName() . "\n";
            $find_proto .= "\$" . $f->getName() . ", ";
            $find_placeholder .= ":" . $f->getName() . ", ";
            $find_checkNull .= '!is_null(' . $find_result . '[\'' . $f->getName() . '\']) && ';
        }

        $variables['find_params']       = substr($find_params, 0, -1);
        $variables['find_proto']        = substr($find_proto, 0, -2);
        $variables['find_placeholder']  = substr($find_placeholder, 0, -2);
        $variables['find_checkNull']    = substr($find_checkNull, 0, -4);
        $variables['find_result']       = $find_result;
        $variables['pkFields']          = $table->getPrimaryKey()->getFields();
        $variables['findProcedureName'] = $this->driver->writeFindProcedure($table);

        return $this->twig->render('t_model.php.twig', $variables);
    }

    /**
     * @param Field[]     $fields
     * @param ManyToOne[] $mto
     * @return Field[]
     */
    private function removeJoinsFields($fields, $mto)
    {
        if (count($mto) > 0)
        {
            $output = array();
            foreach ($fields as $f)
            {
                $join = false;
                $name = $f->getName();
                foreach ($mto as $m)
                    if ($m->getField() == $name)
                        $join = true;
                if (!$join)
                    $output[] = $f;
            }
            return $output;
        }
        else
            return $fields;
    }
}