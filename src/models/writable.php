<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mathieu
 * Date: 08/06/13
 * Time: 15:58
 * To change this template use File | Settings | File Templates.
 */

namespace MicroMuffin\Models;

use MicroMuffin\EPO;
use MicroMuffin\MicroMuffin;
use MicroMuffin\PDOS;
use MicroMuffin\Tools;

abstract class Writable extends Readable
{
    /** @var bool */
    private $_MM_modified = true;
    /** @var bool */
    protected $_MM_notInserted = true;

    protected function _objectEdited()
    {
        $this->_MM_modified = true;
    }

    private function _objectNotEdited()
    {
        $this->_MM_modified    = false;
        $this->_MM_notInserted = false;
    }

    public function getMMModified()
    {
        return $this->_MM_modified;
    }

    /**
     * Add or update the model in database
     *
     * @return void
     */
    public function save()
    {
        if ($this->_MM_modified)
        {
            $reflection = new \ReflectionClass($this);

            //Joints model saving
            $modelAttributes = $this->getModelAttributes($reflection);
            foreach ($modelAttributes as $model)
                $model->save();

            if ($this->_MM_notInserted)
                $this->add();
            else
                $this->update();
        }
    }

    private function update()
    {
        $sql        = 'UPDATE ' . static::$_table_name . ' SET ';
        $set        = '';
        $where      = '';
        $attributes = $this->getAttributes(new \ReflectionClass($this));

        foreach ($attributes as $k => $v)
            if (!in_array($k, static::$_primary_keys))
                $set .= $k . ' = :' . $k . ', ';

        foreach (static::$_primary_keys as $pk)
            $where .= $pk . ' = :' . $pk . ' AND ';

        $where = substr($where, 0, -5);
        $set   = substr($set, 0, -2);
        $sql .= $set . ' WHERE ' . $where;

        $pdo   = PDOS::getInstance();
        $query = $pdo->prepare($sql);
        foreach ($attributes as $k => $v)
        {
            $driver = MicroMuffin::getDBDriver();
            $driver->bindPDOValue($query, ':' . $k, $v);
        }
        $query->execute();
    }

    /**
     * @param self[] $objects
     */
    public static function saveAll(Array $objects)
    {
        if (count($objects) == 0)
            return;

        /** @var self $class */
        $class        = '\\' . get_called_class();
        $table        = $class::getTableName();
        $attributes   = $objects[0]->getAttributes(new \ReflectionClass($objects[0]));
        $nbColumns    = count($attributes);
        $nbTotalRows  = count($objects);
        $rowsByInsert = 0;

        /* Building attribute => getter list */
        $attrGetter = array();
        $fields     = '(';
        foreach ($attributes as $k => $v)
        {
            if ($k != 'id' || $v != 0)
            {
                $attrGetter[$k] = 'get' . Tools::capitalize($k);
                $fields .= $k . ', ';
            }
        }
        $fields = substr($fields, 0, -2) . ')';
        unset($attributes);

        /* Depending of how many columns we have to insert, we don't put the same rows number in a single query */
        if ($nbColumns > 0 && $nbColumns <= 4)
            $rowsByInsert = 50;
        else if ($nbColumns <= 10)
            $rowsByInsert = 10;
        else
            $rowsByInsert = 2;

        $rowsInPartialInsert = $nbTotalRows % $rowsByInsert;

        /* Constructing queries, one for a full insertion, one for remaining rows that cannont fill a full insert */
        $pdo        = PDOS::getInstance();
        $sql        = 'INSERT INTO ' . $table . ' ' . $fields . ' VALUES ';
        $sqlValues1 = '';
        $sqlValues2 = '';
        for ($i = 0; $i < $rowsByInsert; $i++)
        {
            $sqlValues1 .= '(';
            if ($i < $rowsInPartialInsert)
                $sqlValues2 .= '(';
            foreach ($attrGetter as $k => $v)
            {
                $sqlValues1 .= ':' . $k . '_' . $i . ', ';
                if ($i < $rowsInPartialInsert)
                    $sqlValues2 .= ':' . $k . '_' . $i . ', ';
            }
            $sqlValues1 = substr($sqlValues1, 0, -2) . '), ';
            if ($i < $rowsInPartialInsert)
                $sqlValues2 = substr($sqlValues2, 0, -2) . '), ';
        }
        $sqlValues1 = substr($sqlValues1, 0, -2);
        $sqlValues2 = substr($sqlValues2, 0, -2);

        $queryFull = $pdo->prepare($sql . $sqlValues1);
        if ($rowsInPartialInsert > 0)
            $queryPartial = $pdo->prepare($sql . $sqlValues2);
        else
            $queryPartial = null;

        /* Executing loop, filling values and executing queries */
        $nbFullInserts = (int)($nbTotalRows / $rowsByInsert);

        $reflection = new \ReflectionClass(get_called_class());

        $pdo->beginTransaction();

        $i = 0;
        for ($j = 0; $j < $nbFullInserts; $j++)
        {
            for ($k = 0; $k < $rowsByInsert; $k++)
            {
                $object = $objects[$i];
                foreach ($attrGetter as $attr => $getter)
                {
                    $method = $reflection->getMethod($getter);
                    if ($method->isPrivate())
                    {
                        $property = $reflection->getProperty('_' . $attr);
                        $property->setAccessible(true);
                        $val = $property->getValue($object);
                        $property->setAccessible(false);
                    }
                    else
                        $val = $object->$getter();
                    $queryFull->bindValue(':' . $attr . '_' . $k, is_bool($val) ? ($val ? 'true' : 'false') : $val);
                }
                $i++;
            }
            $queryFull->execute();
        }
        if ($rowsInPartialInsert > 0)
        {
            for ($k = 0; $k < $rowsInPartialInsert; $k++)
            {
                $object = $objects[$i];
                foreach ($attrGetter as $attr => $getter)
                {
                    $method = $reflection->getMethod($getter);
                    if ($method->isPrivate())
                    {
                        $property = $reflection->getProperty('_' . $attr);
                        $property->setAccessible(true);
                        $val = $property->getValue($object);
                        $property->setAccessible(false);
                    }
                    else
                        $val = $object->$getter();
                    $queryPartial->bindValue(':' . $attr . '_' . $k, is_bool($val) ? ($val ? 'true' : 'false') : $val);
                }
                $i++;
            }
            $queryPartial->execute();
        }

        $pdo->commit();
    }

    private function add()
    {
        $aAttributes = $this->getAttributes(new \ReflectionClass($this));

        //Detection of attributes to insert : non sequence holder and sequence holder that are null
        $aAttributesToInsert  = array();
        $aSequencedAttributes = array();
        foreach ($aAttributes as $att => $val)
        {
            if (!array_key_exists($att, static::$_sequences) || !is_null($aAttributes[$att]))
                $aAttributesToInsert[$att] = $val;
            else
                $aSequencedAttributes[] = $att;
        }

        //Request building
        $sFields       = '';
        $sPlaceholders = '';

        foreach ($aAttributesToInsert as $att => $val)
        {
            $sFields .= $att . ', ';
            $sPlaceholders .= ':' . $att . ', ';
        }
        $sFields = substr($sFields, 0, -2);
        $sPlaceholders = substr($sPlaceholders, 0, -2);

        $pdo    = PDOS::getInstance();
        $driver = MicroMuffin::getDBDriver();
        $query  = $pdo->prepare('INSERT INTO ' . static::getTableName() . ' (' . $sFields . ') VALUES(' . $sPlaceholders . ')');

        foreach ($aAttributesToInsert as $att => $val)
        {
            $driver::bindPDOValue($query, ':' . $att, $val);
        }

        $query->execute();

        //Retrieving sequence values for concerned attributes
        foreach ($aSequencedAttributes as $att)
        {
            $attributeName        = '_' . $att;
            $this->$attributeName = $pdo->lastInsertId(static::$_sequences[$att]);
        }

        $this->_MM_notInserted = false;
    }
}