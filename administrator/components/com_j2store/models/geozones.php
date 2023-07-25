<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelGeozones extends F0FModel {

	public function onAfterGetItem(&$record){
		if(isset($record->j2store_geozone_id) && !empty($record->j2store_geozone_id)){
			$record->geoRuleList =$this->getGeoZoneRules($record->j2store_geozone_id);
		}

		$record->countryList =  F0FModel::getTmpInstance('Countries','J2StoreModel')->enabled(1)->getList();
	}

	public function save($data) {
		if (parent::save ( $data )) {
			if ($this->otable->j2store_geozone_id) {
				if (isset ( $data ['zone_to_geo_zone'] ) && count ( $data ['zone_to_geo_zone'] )) {

					$status = true;
					foreach ( $data['zone_to_geo_zone'] as $georule ) {
						$grtable = F0FTable::getInstance ( 'geozonerule', 'J2StoreTable' )->getClone();
						$grtable->load($georule['j2store_geozonerule_id']);
						$georule['geozone_id'] = $this->otable->j2store_geozone_id;
						try {
						    $grtable->save($georule);
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

	function getGeoZoneRules($geozone_id){
		$app = JFactory::getApplication();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('gr.j2store_geozonerule_id as j2store_geozonerule_id,gr.country_id,c.country_name as country, z.zone_name as zone,gr.zone_id');
		$query->from('#__j2store_geozonerules AS gr');
		$query->join('LEFT','#__j2store_countries AS c ON c.j2store_country_id=gr.country_id');
		$query->join('LEFT','#__j2store_zones AS z ON z.j2store_zone_id=gr.zone_id');

		$query->where('gr.geozone_id='.$db->q($geozone_id));

		//$query->order('gr.ordering');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getCountryList() {
		$app = JFactory::getApplication();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('a.j2store_country_id,a.country_name');
		$query->from('#__j2store_countries AS a');
		$query->where('enabled = 1');
		$db->setQuery($query);
		return $countries = $db->loadObjectList();
	}

	protected function onAfterDelete($id) {
		$model = $this->getTmpInstance('Geozonerules', 'J2StoreModel');
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('j2store_geozonerule_id')
		->from('#__j2store_geozonerules')
		->where('geozone_id='.$db->q($id));
		$db->setQuery($query);
		$idlist= $db->loadColumn();
		if(count($idlist)) {
			$model->setIds($idlist);
			$model->delete();
		}
		return true;
	}


}

