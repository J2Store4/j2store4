<?php
/**
 * --------------------------------------------------------------------------------
 * Report Plugin - Products
 * --------------------------------------------------------------------------------
 * @package     Joomla 3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2015 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined( '_JEXEC' ) or die( 'Restricted access' );

class J2StoreTableReportProducts extends F0FTable
{

	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__j2store_orders','j2store_order_id', $db, $config);
	}

}