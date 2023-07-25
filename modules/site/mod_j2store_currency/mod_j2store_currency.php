<?php
/*------------------------------------------------------------------------
# mod_j2store_cart - J2 Store Cart
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/



// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

require_once( dirname(__FILE__).'/helper.php' );
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
JFactory::getLanguage()->load('com_j2store', JPATH_ADMINISTRATOR);
$currencies = ModJ2StoreCurrencyHelper::getCurrencies($params);

$currency = J2Store::currency();
$currency_code = $currency->getCode();

$moduleclass_sfx = $params->get('moduleclass_sfx','');
$background_color = $params->get('background_color', '#FFFFFF');
$text_color = $params->get('text_color', '#000000');
$link_color = $params->get('link_color', '#CCCCCC');
$link_hover_color = $params->get('link_hover_color', '#000000');
$active_link_color = $params->get('active_link_color', '#000000');

require( JModuleHelper::getLayoutPath('mod_j2store_currency') );