<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;

/**
 * The updates provisioning Model
 */
class J2StoreModelUpdates extends F0FUtilsUpdate
{
	/**
	 * Public constructor. Initialises the protected members as well.
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$isPro = defined('J2STORE_PRO') ? J2STORE_PRO : 0;

		JLoader::import('joomla.application.component.helper');
		if(!class_exists('J2Store')) {
			require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
		}
		$this->extraQuery = null;
		$this->updateSiteName = 'J2Store Professional';
		$this->updateSite = 'https://cdn.j2store.net/j2store4.xml';
	}
}