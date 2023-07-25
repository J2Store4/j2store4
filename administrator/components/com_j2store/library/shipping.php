<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');

class J2StoreShipping
{
    /**
     * Returns the list of shipping method types
     * @return array of objects
     */
    public static function getTypes()
    {
        static $instance;

        if (!is_array($instance)) {
            $instance = array();
        }
        if (empty($instance)) {

            $object = new \stdClass();
            $object->id = '0';
            $object->title = JText::_('J2STORE_SHIPM_FLAT_RATE_PER_ORDER');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '1';
            $object->title = JText::_('J2STORE_SHIPM_QUANTITY_BASED_PER_ORDER');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '2';
            $object->title = JText::_('J2STORE_SHIPM_PRICE_BASED_PER_ORDER');
            $instance[$object->id] = $object;


            $object = new \stdClass();
            $object->id = '3';
            $object->title = JText::_('J2STORE_SHIPM_FLAT_RATE_PER_ITEM');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '4';
            $object->title = JText::_('J2STORE_SHIPM_WEIGHT_BASED_PER_ITEM');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '5';
            $object->title = JText::_('J2STORE_SHIPM_WEIGHT_BASED_PER_ORDER');
            $instance[$object->id] = $object;


            $object = new \stdClass();
            $object->id = '6';
            $object->title = JText::_('J2STORE_SHIPM_PRICE_BASED_PER_ITEM');
            $instance[$object->id] = $object;
        }

        return $instance;
    }

    /**
     * Returns the requested shipping method object
     *
     * @param $id
     * @return object
     */
    public static function getType($id)
    {
        $items = self::getTypes();
        return $items[$id];
    }

    /**
     * Returns the list of shipping method types
     * @return array of objects
     */
    public static function getIncrementTypes()
    {
        static $instance;

        if (!is_array($instance)) {
            $instance = array();
        }
        if (empty($instance)) {

            $object = new \stdClass();
            $object->id = '0';
            $object->title = JText::_('J2STORE_SHIPPING_ADDITIONAL_INCREMENT');
            $instance[$object->id] = $object;

            $object = new \stdClass();
            $object->id = '1';
            $object->title = JText::_('J2STORE_SHIPPING_ADDITIONAL_DECREMENT');
            $instance[$object->id] = $object;
        }

        return $instance;
    }


    /**
     * Returns the requested shipping method object
     *
     * @param $id
     * @return object
     */
    public static function getIncrementType($id)
    {
        $items = self::getIncrementTypes();
        return $items[$id];
    }


}
