<?php
/*
 * --------------------------------------------------------------------------------
   Weblogicx India  - J2Store - Paypal standard plugin
 * --------------------------------------------------------------------------------
 * @package		Joomla! 2.5x
 * @subpackage	J2Store
 * @author    	Weblogicx India http://www.weblogicxindia.com
 * @copyright	Copyright (c) 2010 - 2015 Weblogicx India Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link		http://weblogicxindia.com
 * --------------------------------------------------------------------------------
*/
/* Initialize Joomla framework */
if(!defined('_JEXEC')) define( '_JEXEC', 1 );

header("Content-type: text/plain; charset=UTF-8");
$plg_name 				= basename(dirname(dirname(__FILE__)));
define( 'DS', DIRECTORY_SEPARATOR );
define('JPATH_BASE',str_replace(DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."j2store".DIRECTORY_SEPARATOR.$plg_name.DIRECTORY_SEPARATOR.$plg_name.DIRECTORY_SEPARATOR."tmpl","",dirname(__FILE__)));
require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'defines.php' );
require_once ( JPATH_BASE .DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'framework.php' );

jimport('joomla.registry.registry');
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.session.session' );
jimport( 'joomla.uri.uri' );

// Instantiate the application.
$app = JFactory::getApplication('site');

$post = $app->input->getArray($_REQUEST);
$rawUrl = JUri::root();
//first remove references to the plugin names
$baseUrl = str_replace("plugins".DIRECTORY_SEPARATOR."j2store".DIRECTORY_SEPARATOR.$plg_name.DIRECTORY_SEPARATOR.$plg_name.DIRECTORY_SEPARATOR."tmpl","", $rawUrl);
$url = ltrim($baseUrl, '/');
$siteurl = rtrim($url, '/');
$request = '';
foreach ($post as $key => $value) {
	$request .= '&' . $key . '=' .$value;
}

$redirect = $siteurl.'/index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type='.$plg_name.'&paction=process&tmpl=component'.$request;
header("location:". $redirect );
