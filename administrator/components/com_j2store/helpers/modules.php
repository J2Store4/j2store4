<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;

class J2Modules
{
	
	public static $instance = null;
	protected $state;
	
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
	
	
	public function loadposition($position, $style = 'xhtml')
	{
		$document	= JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$params		= array('style'=>$style);

		$contents = '';
		foreach (JModuleHelper::getModules($position) as $mod)  {
			$contents .= $renderer->render($mod, $params);
		}
		return $contents;
	}
}