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
$platform = J2Store::platform();
$app = $platform->application();
$language = $app->getLanguage();
$language->load('com_j2store', JPATH_ADMINISTRATOR);
$layout =  $params->get('layout', 'default');

$moduleclass_sfx = $params->get('moduleclass_sfx','');
$link_type = $params->get('link_type','link');
$currency = J2Store::currency();
J2Store::utilities()->nocache();
$document = $app->getDocument();
$ajax_url = $platform->getCartUrl(array('task' => 'ajaxmini'));
$script = "
if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}		
(function($) {
	$(document).bind('after_adding_to_cart', function(element,data, type){

		var murl = '{$ajax_url}';

		$.ajax({
			url : murl,
			type : 'get',
			cache : false,
			contentType : 'application/json; charset=utf-8',
			dataType : 'json',
			success : function(json) {
				if (json != null && json['response']) {
					$.each(json['response'], function(key, value) {
						if ($('.j2store_cart_module_' + key).length) {
							$('.j2store_cart_module_' + key).each(function() {
								$(this).html(value);
							});
						}
					});
				}
			}

		});

	});
})(j2store.jQuery);
		";
//$doc = new \Joomla\CMS\WebAsset\WebAssetManager();
//$doc->
$platform->addInlineScript($script);
$platform->addStyle('j2store-cart','modules/mod_j2store_cart/css/j2store_cart.css');
//$document->addScriptDeclaration($script);
//$document->addStyleSheet(JUri::root().'modules/mod_j2store_cart/css/j2store_cart.css');
$list = modJ2StoreCartHelper::getItems();

//this is required only if the layout is detail cart on homver
if (strpos($layout, 'detailcartonhover') !== false)
{
	$advanced_list = modJ2StoreCartHelper::getAdavcedItems();
	$order = modJ2StoreCartHelper::getOrder ();
	$model = F0FModel::getTmpInstance('Carts','J2StoreModel');
	$checkout_url = $model->getCheckoutUrl();
}


$custom_css = $params->get('custom_css', '');
$custom_css = $params->get('custom_css', '');
if(!empty($custom_css)) {
    $platform->addInlineStyle(strip_tags($custom_css));
}
require( JModuleHelper::getLayoutPath('mod_j2store_cart', $layout));