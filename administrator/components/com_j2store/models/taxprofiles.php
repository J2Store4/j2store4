<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelTaxprofiles extends F0FModel {

	private $shipping_address;
	private $billing_address;
	private $store_address;

	public function __construct($config=array()) {
		parent::__construct($config);
		$this->initialize();

	}

	protected function onAfterGetItem(&$record) {

		$record->taxrates = F0FModel::getTmpInstance('Taxrates', 'J2StoreModel')->enabled(1)->getList();

		if($record->j2store_taxprofile_id){
		$record->taxrules = F0FModel::getTmpInstance('Taxrules', 'J2StoreModel')
								->taxprofile_id($record->j2store_taxprofile_id)
								->getList();
		}

	}
	
	protected function onProcessList(&$resultArray) {
		//allow plugins to modify the data
		J2Store::plugin()->event('AfterGetTaxprofiles', array(&$resultArray));
	}

	public function save($data) {
		if (parent::save ( $data )) {

			if ($this->otable->j2store_taxprofile_id) {
				if (isset ( $data ['tax-to-taxrule-row'] ) && count ( $data ['tax-to-taxrule-row'] )) {

					$status = true;
					foreach ( $data['tax-to-taxrule-row'] as $taxrate ) {
						$trTable = F0FTable::getInstance ( 'taxrules', 'J2StoreTable' )->getClone();
						$trTable->load($taxrate['j2store_taxrule_id']);
						$taxrate['taxprofile_id'] = $this->otable->j2store_taxprofile_id;
						try {
						    $trTable->save($taxrate);
					    }catch (Exception $e) {
						    $status = false;
					    }
					   if(!$status) break;
					}
				}
				else {return true;}
			}
		}else{return false;}
		return true;
	}

	protected function onAfterDelete($id) {
		$model = $this->getTmpInstance('Taxrules', 'J2StoreModel');
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('j2store_taxrule_id')
		->from('#__j2store_taxrules')
		->where('taxprofile_id='.$db->q($id));
		$db->setQuery($query);
		$idlist= $db->loadColumn();
		if(count($idlist)) {
			$model->setIds($idlist);
			$model->delete();
		}
		return true;
	}

	public function initialize() {		
		$config = J2Store::config();
		$session = JFactory::getSession();

		if ($session->has('shipping_country_id', 'j2store') || $session->has('shipping_zone_id', 'j2store') || $session->has('shipping_postcode', 'j2store') ) {
			$this->setShippingAddress($session->get('shipping_country_id', '', 'j2store'), $session->get('shipping_zone_id', '', 'j2store'), $session->get('shipping_postcode', '', 'j2store'));
		} elseif ($config->get('config_tax_default') == 'shipping' && $config->get('config_tax_default_address') =='store' ) {
			$this->setShippingAddress($config->get('country_id'), $config->get('zone_id'), $config->get('store_zip'));
		}

		if ($session->has('billing_country_id', 'j2store') || $session->has('billing_zone_id', 'j2store') || $session->has('billing_postcode', 'j2store')) {
			$this->setBillingAddress($session->get('billing_country_id', '', 'j2store'), $session->get('billing_zone_id', '', 'j2store'), $session->get('billing_postcode', '', 'j2store'));
		} elseif ($config->get('config_tax_default') == 'billing' && $config->get('config_tax_default_address') =='store') {
			$this->setBillingAddress($config->get('country_id'), $config->get('zone_id'), $config->get('store_zip'));
		}
	//	if ($config->get('config_tax_default') == 'store') {
			$this->setStoreAddress($config->get('country_id'), $config->get('zone_id'), $config->get('store_zip'));			
	//	}		
	}

	public function setShippingAddress($country_id, $zone_id, $postcode) {
		$this->shipping_address = array(
				'country_id' => $country_id,
				'zone_id'    => $zone_id,
				'postcode'    => $postcode
		);
	}

	public function setBillingAddress($country_id, $zone_id, $postcode) {
		$this->billing_address = array(
				'country_id' => $country_id,
				'zone_id'    => $zone_id,
				'postcode'    => $postcode
		);
	}

	public function setStoreAddress($country_id, $zone_id, $postcode) {
		$this->store_address = array(
				'country_id' => $country_id,
				'zone_id'    => $zone_id,
				'postcode'    => $postcode
		);
	}

	public function calculate($value, $taxprofile_id, $includes_tax = false) {

		$taxAmount = $this->getTax($value, $taxprofile_id, $includes_tax);

		$actual_value = $value;
		if($includes_tax) {
			//if the value already includes tax
			$actual_value = $value - $taxAmount;
		}

		$return = array();
		$return['value'] = $actual_value;
		$return['tax'] = $taxAmount;
		return (object) $return;

	}


	public function getTax($value, $taxprofile_id, $includes_tax = false) {
		$amount = 0;

		if(!$taxprofile_id) return $amount;

		//get the rates.
		$rates = $this->getRates($taxprofile_id);
		if($includes_tax) {
			$tax_total = 0;
			//first get rates
			foreach($rates as $rate) {
				if($rate['rate'] > 0) {
					$amount = $this->getInclusiveTaxAmount($value, $rates, $rate);
					$tax_total += $amount;
				}
			}
			$amount = $tax_total;
		} else {

			$tax_rates = $this->getTaxRates($value, $rates);
			foreach ($tax_rates as $tax_rate) {
				$amount += $tax_rate['amount'];
			}
		}
		return $amount;
	}

	public function getTaxwithRates($value, $taxprofile_id, $includes_tax = false,$type='line_item') {
		$return = array();

		if(!$taxprofile_id) return $return;
        $tax_profile_model = $this;
        J2Store::plugin()->event('BeforeGetTaxwithRates', array(&$tax_profile_model,$value, $taxprofile_id, $includes_tax,$type));
		//get the rates.
		$rates = $this->getRates($taxprofile_id);
		$taxtotal = 0;

		if($includes_tax) {
			$total = 0;
			//first get rates
			foreach($rates as $rate) {
                $amount = $this->getInclusiveTaxAmount($value, $rates, $rate);
                $total += $amount;
                $return[$rate['taxrate_id']]['name'] = $rate['name'];
                $return[$rate['taxrate_id']]['rate'] = $rate['rate'];
                $return[$rate['taxrate_id']]['amount'] = $amount;
			}
			$taxtotal = $total;
		} else {
			$total = 0;
			$tax_rates = $this->getTaxRates($value, $rates);			
			foreach ($tax_rates as $tax_rate) {
				$return[$tax_rate['taxrate_id']]['name'] = $tax_rate['name'];
				$return[$tax_rate['taxrate_id']]['rate'] = $tax_rate['rate'];
				$return[$tax_rate['taxrate_id']]['amount'] = $tax_rate['amount'];
				$total += $tax_rate['amount'];
			}
			$taxtotal = $total;
		}

		$item = new JObject();
		$item->taxes = $return;
		$item->taxtotal = $taxtotal;	
			
		//allow plugins to modify the data
		J2Store::plugin()->event('AfterGetTaxwithRates', array(&$item,$type));
		return $item;
		
	}

	public function getRates($taxprofile_id) {
		$tax_rates = array();

		if ($this->shipping_address) {
			$taxrates_items = $this->getTaxRateItems('shipping', $this->shipping_address['country_id'], $this->shipping_address['zone_id'], $this->shipping_address['postcode'], $taxprofile_id);
			if(isset($taxrates_items)){
				foreach ($taxrates_items as $trate) {
					$tax_rates[$trate->j2store_taxrate_id] = array(
							'taxrate_id' => $trate->j2store_taxrate_id,
							'name'        => $trate->name,
							'rate'        => $trate->rate
					);
				}
			}
		}

		if ($this->billing_address) {
			$taxrates_items = $this->getTaxRateItems('billing', $this->billing_address['country_id'], $this->billing_address['zone_id'], $this->billing_address['postcode'], $taxprofile_id);
			if(isset($taxrates_items)){
				foreach ($taxrates_items as $trate) {
					$tax_rates[$trate->j2store_taxrate_id] = array(
							'taxrate_id' => $trate->j2store_taxrate_id,
							'name'        => $trate->name,
							'rate'        => $trate->rate
					);
				}
			}
		}

	/* 	if ($this->store_address) {
			$taxrates_items = $this->getTaxRateItems('store', $this->store_address['country_id'], $this->store_address['zone_id'], $this->store_address['postcode'], $taxprofile_id);
			if(isset($taxrates_items)){

				foreach ($taxrates_items as $trate) {
					$tax_rates[$trate->j2store_taxrate_id] = array(
							'taxrate_id' => $trate->j2store_taxrate_id,
							'name'        => $trate->name,
							'rate'        => $trate->rate
					);
				}
			}
		} */
		//allow plugins to modify the data
		J2Store::plugin()->event('AfterGetTaxRates', array(&$tax_rates,$taxprofile_id));
		return $tax_rates;

	}

	public function getTaxRateItems($address_type, $country_id, $zone_id, $postcode, $taxprofile_id) {
		static $ratesets;
		if ( !is_array( $ratesets) )
		{
			$ratesets= array( );
		}
		if ( !isset( $ratesets[$address_type][$country_id][$zone_id][$postcode][$taxprofile_id]) )
		{
            $tax_profile = F0FTable::getAnInstance('Taxprofile' ,'J2StoreTable')->getClone();
            $tax_profile->load($taxprofile_id);
            $result = array();
            if(isset($tax_profile->j2store_taxprofile_id) && $tax_profile->j2store_taxprofile_id > 0 && isset($tax_profile->enabled) && $tax_profile->enabled > 0){
                $db = JFactory::getDbo();
                $query = "SELECT tr2.j2store_taxrate_id, tr2.taxrate_name AS name, tr2.tax_percent AS rate FROM "
                    . " #__j2store_taxrules tr1 LEFT JOIN "
                    . " #__j2store_taxrates tr2 ON (tr1.taxrate_id = tr2.j2store_taxrate_id) LEFT JOIN "
                    . " #__j2store_geozonerules z2gz ON (tr2.geozone_id = z2gz.geozone_id) LEFT JOIN "
                    . " #__j2store_geozones gz ON (tr2.geozone_id = gz.j2store_geozone_id AND gz.enabled=1) WHERE tr1.taxprofile_id = " . (int)$taxprofile_id
                    . " AND tr1.address = ".$db->q($address_type)
                    . " AND z2gz.country_id = " . (int)$country_id
                    . " AND gz.enabled = " . (int)1
                    . " AND tr2.enabled = " . (int)1
                    . " AND (z2gz.zone_id = 0 OR z2gz.zone_id = " . (int)$zone_id
                    . ") ORDER BY tr1.ordering ASC";
                $db->setQuery($query);
                $result = $db->loadObjectList();
            }
			$ratesets[$address_type][$country_id][$zone_id][$postcode][$taxprofile_id] = $result;
		}
		
		//allow plugins to modify the data
		J2Store::plugin()->event('AfterGetTaxRateItems', array(&$ratesets[$address_type][$country_id][$zone_id][$postcode][$taxprofile_id], $address_type, $country_id, $zone_id, $postcode, $taxprofile_id));
		
		return $ratesets[$address_type][$country_id][$zone_id][$postcode][$taxprofile_id];
	}

	public function getTaxRates($value, $tax_rates) {

		$tax_rate_data = array();

		foreach ($tax_rates as $tax_rate) {
			if (isset($tax_rate_data[$tax_rate['taxrate_id']])) {
				$amount = $tax_rate_data[$tax_rate['taxrate_id']]['amount'];
			} else {
				$amount = 0;
			}

			$amount += ($value / 100 * $tax_rate['rate']);

			$tax_rate_data[$tax_rate['taxrate_id']] = array(
					'taxrate_id' => $tax_rate['taxrate_id'],
					'name'        => $tax_rate['name'],
					'rate'        => $tax_rate['rate'],
					'amount'      => $amount
			);
		}
		return $tax_rate_data;
	}
	
	
	public function getBaseTaxRates($value, $taxprofile_id, $includes_tax = false) {
			$return = array();
		
			if(!$taxprofile_id) return $return;
			
			$rates = array();
			$config = J2Store::config();
			//$this->setStoreAddress($config->get('country_id'), $config->get('zone_id'));
			
			$taxrates_items = $this->getTaxRateItems($config->get('config_tax_default'), $config->get('country_id'), $config->get('zone_id'), $config->get('store_zip'), $taxprofile_id);
			//var_dump($taxrates_items);			
			if(isset($taxrates_items)){
			
				foreach ($taxrates_items as $trate) {
					$rates[$trate->j2store_taxrate_id] = array(
							'taxrate_id' => $trate->j2store_taxrate_id,
							'name'        => $trate->name,
							'rate'        => $trate->rate
					);
				}
			}
			
			//allow plugins to modify the data
			J2Store::plugin()->event('AfterGetTaxRates', array(&$rates,$taxprofile_id));
			
			$taxtotal = 0;
		
			if($includes_tax) {
				$total = 0;
				//first get rates
				foreach($rates as $rate) {
                    $amount = $this->getInclusiveTaxAmount($value, $rates, $rate);
                    $total += $amount;
                    $return[$rate['taxrate_id']]['name'] = $rate['name'];
                    $return[$rate['taxrate_id']]['rate'] = $rate['rate'];
                    $return[$rate['taxrate_id']]['amount'] = $amount;
				}
				$taxtotal = $total;
			} else {
				$total = 0;
				$tax_rates = $this->getTaxRates($value, $rates);
				foreach ($tax_rates as $tax_rate) {
					$return[$tax_rate['taxrate_id']]['name'] = $tax_rate['name'];
					$return[$tax_rate['taxrate_id']]['rate'] = $tax_rate['rate'];
					$return[$tax_rate['taxrate_id']]['amount'] = $tax_rate['amount'];
					$total += $tax_rate['amount'];
				}
				$taxtotal = $total;
			}
		
			$result = array();
			$result['taxes'] = $return;
			$result['taxtotal'] = $taxtotal;
		
			return (object) $result;
	}

	public function getInclusiveTaxAmount($value, $all_rates, $rate) {

		$regular_rates  = array();
		foreach ( $all_rates as $key => $single_rate ) {
			if($single_rate['rate'] > 0) {
				$regular_rates[ $key ] = $single_rate['rate'];
			}
		}
		
		$regular_tax_rate = 1 + ( array_sum( $regular_rates ) / 100 );

		$the_rate       = ( $rate['rate'] / 100 ) / $regular_tax_rate;
		$net_price      = $value - ( $the_rate * $value );
		$amount = 0;
		$amount = $value - $net_price;
		return $amount;
	}
}

