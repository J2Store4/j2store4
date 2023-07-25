<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class J2Plugins
{


	public static $instance = null;

	public function __construct($properties=null) {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	/**
	 * Only returns plugins that have a specific event
	 *
	 * @param $eventName
	 * @param $folder
	 * @return array of JTable objects
	 */
public function getPluginsWithEvent( $eventName, $folder='J2Store' )
	{
		$return = array();
		if ($plugins = $this->getPlugins( $folder ))
		{
			foreach ($plugins as $plugin)
			{
				if ($this->hasEvent( $plugin, $eventName ))
				{
					$return[] = $plugin;
				}
			}
		}
        JPluginHelper::importPlugin('j2store');
        $app = JFactory::getApplication();
        $app->triggerEvent('onJ2StoreAfterGetPluginsWithEvent', array(&$return));
		return $return;
	}

	/**
	 * Returns Array of active Plugins
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	public static function getPlugins( $folder='J2Store' )
	{
		$database = JFactory::getDBO();

		$order_query = " ORDER BY ordering ASC ";
		$folder = strtolower( $folder );

		$query = "
		SELECT
		*
		FROM
		#__extensions
		WHERE  enabled = '1'
		AND
		LOWER(`folder`) = '{$folder}'
		{$order_query}
		";

		$database->setQuery( $query );
		$data = $database->loadObjectList();
		return $data;
	}
	
	/**
	 * Returns an active Plugin
	 * 
	 * @param
	 *        	mixed Boolean
	 * @param
	 *        	mixed Boolean
	 * @return array
	 */
	public static function getPlugin($element, $folder = 'j2store') {
		if (empty ( $element ))
			return false;
		$row = false;
		$db = JFactory::getDBO ();
		
		$folder = strtolower ( $folder );
		$query = $db->getQuery ( true )->select ( '*' )->from ( '#__extensions' )
						->where ( $db->qn ( 'enabled' ) . ' = ' . $db->q ( 1 ) )
						->where ( $db->qn ( 'folder' ) . ' = ' . $db->q ( $folder ) )
						->where ( $db->qn ( 'element' ) . ' = ' . $db->q ( $element ) );
		
		$db->setQuery ( $query );
		try {
			$row = $db->loadObject ();
		} catch ( Exception $e ) {
			return false;
		}
		return $row;
	}

	/**
	 * Returns HTML
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	public function getPluginsContent( $event, $options, $method='vertical' )
	{
		$text = "";
		jimport('joomla.html.pane');

		if (!$event) {
			return $text;
		}

		$args = array();

		$results = JFactory::getApplication()->triggerEvent( $event, $options );

		if ( !count($results) > 0 ) {
			return $text;
		}

		// grab content
		switch( strtolower($method) ) {
			case "vertical":
				for ($i=0; $i<count($results); $i++) {
					$result = $results[$i];
					$title = $result[1] ? JText::_( $result[1] ) : JText::_( 'Info' );
					$content = $result[0];

					// Vertical
					$text .= '<p>'.$content.'</p>';
				}
				break;
			case "tabs":
				break;
		}

		return $text;
	}

	/**
	 * Checks if a plugin has an event
	 *
	 * @param obj      $element    the plugin JTable object
	 * @param string   $eventName  the name of the event to test for
	 * @return unknown_type
	 */
	public function hasEvent( $element, $eventName )
	{
		$success = false;
		if (!$element || !is_object($element)) {
			return $success;
		}

		if (!$eventName || !is_string($eventName)) {
			return $success;
		}

		// Check if they have a particular event
		$import 	= JPluginHelper::importPlugin( strtolower('J2Store'), $element->element );

		$result 	= JFactory::getApplication()->triggerEvent( $eventName, array( $element ) );
		if (in_array(true, $result, true))
		{
			$success = true;
		}
		return $success;
	}

	public function enableJ2StorePlugin() {
		$db = JFactory::getDBO();

		$folder = strtolower( 'j2store');

		$query = $db->getQuery(true)->update('#__extensions')->set('enabled=1')
					->where($db->qn('folder').' = '.$db->q('system'))
					->where($db->qn('element').' = '.$db->q('j2store'));
		$db->setQuery($query);
		$db->execute();
		return true;
	}

	public function importCatalogPlugins() {
		JPluginHelper::importPlugin('content');
	}
	public function event($event, $args=array(), $prefix='onJ2Store') {
		if(empty($event)) return '';
		$this->importCatalogPlugins();
		JPluginHelper::importPlugin('j2store');
        $platform = J2Store::platform();
        $result = $platform->eventTrigger($prefix.$event, $args);
		/*$app = JFactory::getApplication();
        $result = $app->triggerEvent($prefix.$event, $args);*/
		return $result;
	}


	/**
	 * Method to get the html output of an event
	 * @param string $event
	 * @param array $args
	 * @return string
	 */

	public function eventWithHtml($event, $args=array(), $prefix='onJ2Store') {
		if(empty($event)) return '';
		JPluginHelper::importPlugin('j2store');
		$app = JFactory::getApplication();
		$html = '';
        $platform = J2Store::platform();
        $results = $platform->eventTrigger($prefix.$event, $args);
		foreach($results as $result) {
			$html .= $result;
		}
		return $html;
	}

	public function eventWithArray($event, $args=array(), $prefix='onJ2Store') {
		if(empty($event)) return '';
		JPluginHelper::importPlugin('j2store');
		$app = JFactory::getApplication();
        $platform = J2Store::platform();
        $results = $platform->eventTrigger($prefix.$event, $args);
		$array = array();
		if(isset($results[0])) {
			$array = $results[0];
		}
		return $array;
	}
}