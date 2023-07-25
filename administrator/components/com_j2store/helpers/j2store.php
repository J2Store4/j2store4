<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/config.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/article.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/product.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/currency.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/weight.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/length.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/cart.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/user.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/plugin.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/email.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/invoice.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/utilities.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/modules.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/help.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/view.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/selectable/base.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/selectable/fields.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/queue.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/platform.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/fof.php');
/**
 * J2Store helper.
  */
class J2Store
{

	public static function addSubmenu($vName)
	{
		if(!defined('F0F_INCLUDED'))
        {
			include_once JPATH_ADMINISTRATOR.'/components/com_j2store/fof/include.php';
		}
		return F0FToolbar::getAnInstance('com_j2store')->j2storeHelperRenderSubmenu($vName);
	}

	public static function storeProfile($config=array()) {
		//backward compatibility
		return J2Config::getInstance($config);
	}

	public static function product($config=array()) {

		return J2Product::getInstance($config);
	}

	public static function currency($config=array()) {

		return J2Currency::getInstance($config);
	}

	public static function length() {

		return J2Length::getInstance();
	}

	public static function weight() {

		return J2Weight::getInstance();
	}

	public static function config($config=array()) {

		return J2Config::getInstance($config);
	}

	public static function cart($config=array()) {

		return J2Cart::getInstance($config);
	}

	public static function user($config=array()) {

		return J2User::getInstance($config);
	}

	public static function plugin($config=array()) {

		return J2Plugins::getInstance($config);
	}

	public static function email($config=array()) {

		return J2Email::getInstance($config);
	}

	public static function invoice($config=array()) {

		return J2Invoice::getInstance($config);
	}

	public static function utilities($config=array()) {

		return J2Utilities::getInstance($config);
	}

	public static function article($config=array()) {

		return J2Article::getInstance($config);
	}

	public static function modules($config=array()) {

		return J2Modules::getInstance($config);
	}

	public static function getSelectableBase() {
		return J2StoreSelectableBase::getInstance();
	}

	public static function getSelectableFields() {
		return J2StoreSelectableFields::getInstance();
	}
	
	public static function help($config=array()) {
		return J2Help::getInstance($config);
	}
	
	public static function view($config=array()) {
		return J2ViewHelper::getInstance($config);
	}

	public static function isPro() {
		$isPro = defined('J2STORE_PRO') ? J2STORE_PRO : 0;
		return $isPro;
	}

	public static function buildHelpLink($url, $content='app') {

		$source = 'free';
		if(self::isPro()) {
			$source = 'pro';
		}
		$utm_query ='?utm_source='.$source.'&utm_medium=component&utm_campaign=inline&utm_content='.$content;
		$domain = 'https://www.j2store.org';

		$fullurl = $domain.'/'.$url.$utm_query;
		return $fullurl;

	}

	public static function queue($config=array()) {

		return J2Queue::getInstance($config);
	}

	public static function strapper($config = array()){
        return J2StoreStrapper::getInstance($config);
    }
    public static function platform($config = array()){
	    return J2StorePlatform::getInstance($config);
    }
    public static function fof($config = array()){
        return J2F0F::getInstance($config);
    }
}
