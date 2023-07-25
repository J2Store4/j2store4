<?php
/**
 * --------------------------------------------------------------------------------
 * App Plugin - Currency Updater
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2017 J2Store . All rights reserved.
 * @license     GNU/GPL v3 or latest
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/appcontroller.php');
class J2StoreControllerAppCurrencyUpdater extends J2StoreAppController
{
    var $_element   = 'app_currencyupdater';
}