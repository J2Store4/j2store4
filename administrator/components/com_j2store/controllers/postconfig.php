<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
class J2StoreControllerPostconfig extends F0FController {

	public function execute($task)
	{
		if ($task != 'saveConfig')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	public function browse() {
		if(parent::browse()) {
			$config = J2Store::config();
			$complete = $config->get('installation_complete', 0);
			$platform = J2Store::platform();
			if($complete) {
                $platform->redirect('index.php?option=com_j2store&view=cpanel', JText::_('J2STORE_POSTCONFIG_STORE_SETUP_DONE_ALREADY'));
			}
			return true;
		}

		return false;
	}

	public function saveConfig() {

		//first CSRF check
		JSession::checkToken() or die( 'Invalid Token' );

		$app = J2Store::platform()->application();
		$json = array();

		$values = $app->input->getArray($_POST);

		//NOT a PRO version ? check if the mandatory terms are accepted.
		if(J2Store::isPro() != 1) {
			if(!isset($values['acceptlicense'])) {
				$json['error']['acceptlicense'] = JText::_('J2STORE_POSTCONFIG_ERR_ACCEPTLICENSE');
			}

			if(!isset($values['acceptsupport'])) {
				$json['error']['acceptsupport'] = JText::_('J2STORE_POSTCONFIG_ERR_ACCEPTSUPPORT');
			}
		}

		//now we need a store name
		if(!$this->validate_field('store_name', $values)) {
			$json['error']['store_name'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$this->validate_field('store_zip', $values)) {
			$json['error']['store_zip'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$this->validate_field('country_id', $values)) {
			$json['error']['country_id'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(!$this->validate_field('config_currency', $values)) {
			$json['error']['config_currency'] = JText::_('J2STORE_FIELD_REQUIRED');
		}

		if(strlen($values['config_currency']) != 3) {
			$json['error']['config_currency'] = JText::_('J2STORE_CURRENCY_CODE_ERROR');
		}

		$currency_code = $values['config_currency'];
		$currency_symbol = isset($values['config_currency_symbol']) ? $values['config_currency_symbol'] : $currency_code;
		unset($values['config_currency_symbol']);

		if(!$json) {
			$db = JFactory::getDbo();
			$query = 'REPLACE INTO #__j2store_configurations (config_meta_key,config_meta_value) VALUES ';

			jimport('joomla.filter.filterinput');
			$filter = JFilterInput::getInstance(array(), array(), 1, 1);
			$conditions = array();
			foreach ($values as $metakey=>$value) {
				//now clean up the value

				if($metakey == 'tax_rate') {

					if(!empty($value)) {
						$rate = floatval($value);
						if($rate > 0) {
							try {
							$this->set_default_taxrate($rate, $values);
							} catch(Exception $e) {
								//do nothing. User can always set tax later
							}
						}
					}

					continue;
				}

				$clean_value = $filter->clean($value, 'string');
				$conditions[] = '('.$db->q(strip_tags($metakey)).','.$db->q($clean_value).')';
			}
			//add the admin email
			$conditions[] = '('.$db->q('admin_email').','.$db->q(JFactory::getUser()->email).')';

			//set installation complete
			$conditions[] = '('.$db->q('installation_complete').','.$db->q('1').')';

			$query .= implode(',',$conditions);
			try {
				$db->setQuery($query);
				$db->execute();

				F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->create_currency_by_code($currency_code, $currency_symbol);
				$msg = JText::_('J2STORE_CHANGES_SAVED');
				$json['redirect'] = 'index.php?option=com_j2store&view=cpanel';
			}catch (Exception $e) {
                $json['error']['config_currency_symbol'] = $e->getMessage();
			}

		}

		echo json_encode($json);
		$app->close();
	}

	protected function validate_field($field, $values) {
		if(!isset($values[$field]) || empty($values[$field])) {
			return false;
		}
		return true;
	}

	protected function set_default_taxrate($rate, $values) {
		// get the country id
		$country_id = $values ['country_id'];

		//first check if taxrates were already set up. So that we can ignore

		$list = F0FModel::getTmpInstance('Taxrates', 'J2StoreModel')->getList();
		if(count($list) > 0) return false;

		// first create a geozone.
		$geozone = F0FTable::getInstance ( 'Geozone', 'J2StoreTable' )->getClone ();
		$geozone->geozone_name = 'Default Geozone';
		$geozone->enabled = 1;

		try {
			$geozone->store ();
		} catch ( Exception $e ) {
			return false;
		}

		// create geozone rules
		if ($geozone->j2store_geozone_id) {
			$geozonerule = F0FTable::getInstance ( 'Geozonerule', 'J2StoreTable' )->getClone ();
			$geozonerule->geozone_id = $geozone->j2store_geozone_id;
			$geozonerule->country_id = $country_id;
			$geozonerule->zone_id = 0;

			try {
				$geozonerule->store ();
			} catch ( Exception $e ) {
				return false;
			}

			// now create a tax rate
			$taxrate = F0FTable::getInstance ( 'Taxrate', 'J2StoreTable' )->getClone ();
			$taxrate->geozone_id = $geozone->j2store_geozone_id;
			$taxrate->taxrate_name = 'VAT';
			$taxrate->tax_percent = $rate;
			$taxrate->enabled = 1;
			$taxrate->store ();

			// now create a tax profile
			$taxprofile = F0FTable::getInstance ( 'Taxprofile', 'J2StoreTable' )->getClone ();
			$taxprofile->taxprofile_name = 'Default tax profile';
			$taxprofile->enabled = 1;
			$taxprofile->store ();

			// now create the tax rule
			if ($taxrate->j2store_taxrate_id && $taxprofile->j2store_taxprofile_id) {
				$taxrule = F0FTable::getInstance ( 'Taxrule', 'J2StoreTable' )->getClone ();
				$taxrule->taxprofile_id = $taxprofile->j2store_taxprofile_id;
				$taxrule->taxrate_id = $taxrate->j2store_taxrate_id;
				$taxrule->address = 'billing';
				$taxrule->store ();
			}
		}
	}
}
