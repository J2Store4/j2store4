<?php
/**
 * --------------------------------------------------------------------------------
 * System plugin - J2Store page cache clear
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport('joomla.html.parameter');

// Make sure FOF is loaded, otherwise do not run
if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

if (!defined('F0F_INCLUDED') || !class_exists('F0FLess', true))
{
	return;
}

// Do not run if Akeeba Subscriptions is not enabled
JLoader::import('joomla.application.component.helper');

if (!JComponentHelper::isEnabled('com_j2store', true))
{
	return;
}

class plgSystemJ2pagecache extends JPlugin
{
	var $_cache_key = null;
	function __construct ( &$subject, $config )
	{
		parent::__construct ( $subject, $config );

		$this->_cache = false;
		$plugin = JPluginHelper::getPlugin('system','cache');
		if($plugin) {
			$params = json_decode($plugin->params);
			$options = array(
				'defaultgroup'	=> 'page',
				'browsercache'	=> $params->browsercache,
				'caching'		=> false
			);
			$this->_cache	= JCache::getInstance('page', $options);
		}
		$this->_cache_key = JUri::getInstance()->toString();
	}

	function __destruct ()
	{
		$jinput = JFactory::getApplication ()->input;
		$option = $jinput->get ( 'option','' );
		if ( $this->_cache !== false && $option == "com_j2store" ) {
			$this->_cache->remove ( $this->_cache_key );
			$j2store_cache = $this->params->get ( 'conservative_cache' ,0);
			if($j2store_cache){
				$cache = JFactory::getCache('com_j2store');
				$cache->clean();
			}

		}
	}
}