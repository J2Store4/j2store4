<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerCpanels extends F0FController
{
	 public function execute($task) {
		if(!in_array($task, array('browse' ,'getEupdates', 'notifications','getSubscription'))) {
			$task = 'browse';
		}
		parent::execute($task);
	}

	protected function onBeforeBrowse() {
		$db = JFactory::getDbo();

		$config = J2Store::config();
		$installation_complete = $config->get('installation_complete', 0);
		if(!$installation_complete) {
			//installation not completed
			JFactory::getApplication()->redirect('index.php?option=com_j2store&view=postconfig');
		}
        $platform = J2Store::platform();
        //$platform->checkAdminMenuModule();
		//first check if the currency table has a default records at least.
		$rows = F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->enabled(1)->getList();
		if(count($rows) < 1) {
			//no records found. Dumb default data
			F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->create_currency_by_code('USD', 'USD');
		}
		//update schema
		$dbInstaller = new F0FDatabaseInstaller(array(
				'dbinstaller_directory'	=> JPATH_ADMINISTRATOR . '/components/com_j2store/sql/xml'
		));
		$dbInstaller->updateSchema();

		//update cart table
		$cols = $db->getTableColumns('#__j2store_carts');
		$cols_to_delete = array('product_id', 'vendor_id', 'variant_id', 'product_type', 'product_options', 'product_qty');
		foreach($cols_to_delete as $key) {
			if(array_key_exists($key, $cols)) {
				$db->setQuery('ALTER TABLE #__j2store_carts DROP COLUMN '.$key);
				try {
					$db->execute();
				}catch(Exception $e) {
					echo $e->getMessage();
				}
			}
		}
		$this->migrate_coupons();
		$this->migrate_order_coupons();
		$this->migrate_order_vouchers();
		$this->drop_indexes();
		$this->clear_outdated_cart_data();
        $this->disable_plugin_for_j4();

		return parent::onBeforeBrowse();
	}

    public function disable_plugin_for_j4(){
        if(version_compare(JVERSION,'3.99.99','ge')) {
            $db = JFactory::getDBO();
            $platform = J2Store::platform();
            $allowed_plugins = $platform->eventJ2Store4('onJ2StoreIsJ2Store4');
            $query = $db->getQuery(true)->select('*')->from('#__extensions')->where('enabled = 1')->where('folder = ' . $db->q('j2store'))->where('type = ' . $db->q('plugin'))->order('ordering ASC');
            $db->setQuery($query);
            $plugins = $db->loadObjectList();
            if (!empty($plugins  ) && !empty($allowed_plugins)) {
                foreach ($plugins as $plugin) {
                    if( !in_array($plugin->element, $allowed_plugins)) {
                        $query = $db->getQuery(true)->update('#__extensions')->set('enabled = 0')->where('folder = ' . $db->q('j2store'))->where('element=' . $db->q($plugin->element));
                        $db->setQuery($query);
                        try {
                            $db->execute();
                        } catch (Exception $e) {
                            //do nothing
                        }
                    }
                }
            }
        }
    }
	public function migrate_order_coupons() {
		$db = JFactory::getDbo ();

		$tables = $db->getTableList ();
		// get prefix
		$prefix = $db->getPrefix ();

		//correct the collation
       // if (in_array ( $prefix . 'j2store_orderdiscounts', $tables )) {
        //    $db->setQuery ( 'ALTER TABLE #_j2store_orderdiscounts CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci' );
        //}

		// let us back up the table first
		if (! in_array ( $prefix . 'j2store_backup_ordercoupons', $tables ) && in_array ( $prefix . 'j2store_ordercoupons', $tables )) {
			$db->setQuery ( 'CREATE TABLE #__j2store_backup_ordercoupons LIKE #__j2store_ordercoupons' );
			$db->execute ();
			$db->setQuery ( 'INSERT #__j2store_backup_ordercoupons SELECT * FROM #__j2store_ordercoupons' );
			$db->execute ();
		}

		if (in_array ( $prefix . 'j2store_ordercoupons', $tables )) {

			$query = $db->getQuery ( true )->select ( '*' )->from ( '#__j2store_ordercoupons' );
			$db->setQuery ( $query );
			$ordercoupons = $db->loadObjectList ();

			$migrated_coupons = array ();
			if (count ( $ordercoupons ) > 0) {
				foreach ( $ordercoupons as $coupon ) {
					unset ( $table );
					$table = F0FTable::getInstance ( 'Orderdiscount', 'J2StoreTable' )->getClone ();
					$table->load ( array (
							'order_id' => $coupon->order_id,
							'discount_type' => 'coupon'
					) );
					$table->order_id = $coupon->order_id;
					$table->discount_type = 'coupon';
					$table->discount_code = $coupon->coupon_code;
					$table->discount_title = $coupon->coupon_code;
					$table->discount_value = $coupon->value;
					$table->discount_value_type = $coupon->value_type;
					$table->discount_entity_id = $coupon->coupon_id;
					$table->discount_customer_email = $coupon->customer_email;
					$table->user_id = $coupon->customer_id;
					$table->discount_amount = $coupon->amount;
					if ($table->store ()) {
						$migrated_coupons [] = $coupon->j2store_ordercoupon_id;
					}
				}

				if (count ( $migrated_coupons )) {
					// now delete the records of successfully migrated order coupons
					$query = $db->getQuery ( true )->delete ( '#__j2store_ordercoupons' )->where ( 'j2store_ordercoupon_id IN (' . implode ( ',', $migrated_coupons ) . ')' );
					$db->setQuery ( $query );
					try {
						$db->execute ();
					} catch ( Exception $e ) {
						// was not able to delete. So remove one by one.
						$model = F0FModel::getTmpInstance ( 'Ordercoupons', 'J2StoreModel' );
						$model->setIds ( $migrated_coupons );
						$model->delete ();
					}
				}
			}
		}
	}

	public function migrate_order_vouchers() {
		$db = JFactory::getDbo ();

		$tables = $db->getTableList ();
		// get prefix
		$prefix = $db->getPrefix ();

		// let us back up the table first
		if (! in_array ( $prefix . 'j2store_backup_voucherhistories', $tables ) && in_array ( $prefix . 'j2store_voucherhistories', $tables )) {
			$db->setQuery ( 'CREATE TABLE #__j2store_backup_voucherhistories LIKE #__j2store_voucherhistories' );
			$db->execute ();
			$db->setQuery ( 'INSERT #__j2store_backup_voucherhistories SELECT * FROM #__j2store_voucherhistories' );
			$db->execute ();
		}

		if (in_array ( $prefix . 'j2store_voucherhistories', $tables )) {

			$query = $db->getQuery ( true )->select ( '*' )->from ( '#__j2store_voucherhistories' );
			$db->setQuery ( $query );
			$vouchers = $db->loadObjectList ();

			$migrated_vouchers = array ();
			if (count ( $vouchers ) > 0) {
				foreach ( $vouchers as $voucher ) {
					unset ( $table );
					$table = F0FTable::getInstance ( 'Orderdiscount', 'J2StoreTable' )->getClone ();
					$table->load ( array (
							'order_id' => $voucher->order_id,
							'discount_type' => 'voucher'
					) );
					$table->order_id = $voucher->order_id;
					$table->discount_type = 'voucher';
					$table->discount_code = $voucher->voucher_code;
					$table->discount_title = $voucher->voucher_code;
					$table->discount_entity_id = $voucher->voucher_id;
					$table->discount_customer_email = $voucher->voucher_to_email;
					$table->user_id = $voucher->created_by;
					$table->discount_amount = abs ( $voucher->amount );
					if ($table->store ()) {
						$migrated_vouchers [] = $voucher->j2store_voucherhistory_id;
					}
				}

				if (count ( $migrated_vouchers )) {
					// now delete the records of successfully migrated order coupons
					$query = $db->getQuery ( true )->delete ( '#__j2store_voucherhistories' )->where ( 'j2store_voucherhistory_id IN (' . implode ( ',', $migrated_vouchers ) . ')' );
					$db->setQuery ( $query );
					try {
						$db->execute ();
					} catch ( Exception $e ) {
						// was not able to delete. So remove one by one.
						$model = F0FModel::getTmpInstance ( 'Voucherhistories', 'J2StoreModel' );
						$model->setIds ( $migrated_vouchers );
						$model->delete ();
					}
				}
			}
		}
	}

	public function migrate_coupons() {
		$db = JFactory::getDbo ();
		if (! J2Store::isPro ()) return true;

		$total = 0;
		$query = $db->getQuery ( true )->select ( 'COUNT(*)' )->from ( '#__j2store_coupons' )->where ( '(value_type =' . $db->q ( 'P' ) . ' OR value_type =' . $db->q ( 'F' ) . ')' )->where ( 'enabled = 1' );
		$db->setQuery ( $query );
		try {
			$total = $db->loadResult ();
		} catch ( Exception $e ) {
			// do nothing
		}
		if ($total) {
			// disable fixed product type coupons
			$query = $db->getQuery ( true )->update ( '#__j2store_coupons' )->set ( 'enabled = 0' )->where ( 'value_type = ' . $db->q ( 'F' ) );
			$db->setQuery ( $query );
			try {
				$db->execute ();
			} catch ( Exception $e ) {
				// do nothing.
			}

			// if product category and products not empty, then coupon type is percentage product
			$query = $db->getQuery ( true )->update ( '#__j2store_coupons' )->set ( 'value_type = ' . $db->q ( 'percentage_product' ) )->where ( 'value_type = ' . $db->q ( 'P' ) )->where ( 'product_category <> ""' )->where ( 'products <> ""' );

			$db->setQuery ( $query );
			try {
				$db->execute ();
			} catch ( Exception $e ) {
				// do nothing.
			}

			// if product category and products is empty, then coupon type is percentage cart
			$query = $db->getQuery ( true )->update ( '#__j2store_coupons' )->set ( 'value_type = ' . $db->q ( 'percentage_cart' ) )->where ( 'value_type = ' . $db->q ( 'P' ) )->where ( '(product_category = "" OR product_category = NULL)' )->where ( '(products = "" OR products = NULL)' );

			$db->setQuery ( $query );
			try {
				$db->execute ();
			} catch ( Exception $e ) {
				// do nothing.
			}
		}
	}

	public function drop_indexes() {
		//This fix is required because multiple indexes were created in previous versions.

		$db = JFactory::getDbo();
		$query = "SHOW INDEX FROM #__j2store_orders WHERE `key_name` LIKE ".$db->q('%user_email_%');
		$db->setQuery($query);
		$indexes = $db->loadObjectList();

		$i = 2;
		foreach ($indexes as $index)
		{
			$name = 'user_email_'.$i;
			if ($index->Key_name == $name) {
				$query = 'DROP INDEX '.$name.' ON #__j2store_orders';
				$db->setQuery($query);
				try {
					$db->execute();
				}catch (Exception $e) {
					//do nothing
				}
			}
		$i++;
		}

		$db = JFactory::getDbo();
		$query = "SHOW INDEX FROM #__j2store_addresses WHERE `key_name` LIKE ".$db->q('%email_%');
		$db->setQuery($query);
		$indexes = $db->loadObjectList();

		$i = 2;
		foreach ($indexes as $index)
		{
			 $name = 'email_'.$i;
			if ($index->Key_name == $name) {
				 $query = 'DROP INDEX '.$name.' ON #__j2store_addresses';
				$db->setQuery($query);
				try {
					$db->execute();
				}catch (Exception $e) {

				}
			}
			$i++;
		}

	}

	/**
	 * Task to clear the old cart data
	 * */
	public function clear_outdated_cart_data(){
		$j2params = J2Store::config();
		$no_of_days_old = $j2params->get('clear_outdated_cart_data_term',90);

		$db = JFactory::getDbo();
		$query = "select count(j2store_cart_id) from #__j2store_carts c where c.cart_type='cart' AND datediff(now(), c.created_on) > ".$db->q($no_of_days_old).";";
		$db->setQuery($query);
		$old_cart_items_exists = $db->loadResult();

		if ( $old_cart_items_exists ) {
		
			$delete_cartitems_qry = "delete from #__j2store_cartitems where cart_id in "
									."(select j2store_cart_id from #__j2store_carts c where c.cart_type=".$db->q('cart')
										." AND datediff(now(), c.created_on) > ".$db->q($no_of_days_old)." );" ;
			$db->setQuery($delete_cartitems_qry);
			try {
				$db->execute();
			}catch (Exception $e) {	}			

			$delete_carts_qry = "delete from #__j2store_carts where #__j2store_carts.cart_type=".$db->q('cart')
								." AND datediff(now(), #__j2store_carts.created_on) > ".$db->q($no_of_days_old)." ;" ;
			$db->setQuery($delete_carts_qry);
			try {
				$db->execute();
			}catch (Exception $e) {	}
			
		}
		//delete from #__j2store_cartitems where cart_id in (select j2store_cart_id from #__j2store_carts c where c.cart_type='cart' AND datediff(now(), c.modified_on) > 120);
		//delete from #__j2store_carts where #__j2store_carts.cart_type='cart' AND datediff(now(), #__j2store_carts.modified_on) > 120;
	}

	public function getEupdates(){
		$app = JFactory::getApplication();
		$eupdate_model = F0FModel::getTmpInstance('Eupdates','J2StoreModel');
		$list = $eupdate_model->getUpdates();
		$total = count($list);
		$json =array();
		if($total > 0){
			$json['total'] = $total;
		}
		echo json_encode($json);
		$app->close();
	}

	//getSubscriptionDetails
	public function notifications() {
		$platform = J2Store::platform();
		JSession::checkToken( 'get' ) or die( 'Invalid Token' );
		if($platform->isClient('administrator')) {
			$app = JFactory::getApplication();
			$message_type= $app->input->getString('message_type');
			J2Store::config()->saveOne($message_type, 1);
		}
		$url = 'index.php?option=com_j2store&view=cpanels';
        $platform->redirect($url);
	}

}