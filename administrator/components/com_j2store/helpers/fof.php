<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2F0F extends \stdClass {
    public static $instance = null;
    public static function getInstance(array $config = array())
    {
        if (!self::$instance)
        {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    function loadTableFilePath(){
        F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
        $paths = array();
        J2Store::plugin()->event('CustomTablePath',array(&$paths));
        foreach ($paths as $path){
            F0FTable::addIncludePath($path);
        }
    }

    function loadModelFilePath(){
        F0FModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_j2store/models');
        $paths = array();
        J2Store::plugin()->event('CustomModelPath',array(&$paths));
        foreach ($paths as $path){
            F0FModel::addIncludePath($path);
        }
    }
    /**
     * @param $table_type
     * @param $table_prefix
     * @param array $params
     * @return object
     */
    function loadTable($table_type, $table_prefix, $params = array())
    {
        if(empty($table_type) || empty($table_prefix)){
            return new \stdClass();
        }
        if(!is_array($params)){
            $params = (array) $params;
        }
        $this->loadTableFilePath();
        $table = F0FTable::getInstance($table_type, $table_prefix)->getClone();
        if(!empty($params)){
            $table->load($params);
        }
        return $table;
    }

    function getModel($model_type,$model_prefix,$fields = array()){
        if(empty($model_type) || empty($model_prefix)){
            return new \stdClass();
        }
        $this->loadModelFilePath();
        $model = F0FModel::getTmpInstance($model_type, $model_prefix);
        if(!empty($fields) && is_array($fields)){
            foreach ($fields as $key => $value){
                $model->$key($value);
            }
        }
        return $model;
    }
}