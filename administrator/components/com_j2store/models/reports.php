<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelReports extends F0FModel {

	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->getSelectQuery($query);
		$this->getWhereQuery($query);
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("report.extension_id,report.name,report.type,report.folder,report.element,report.params,report.enabled,report.ordering,report.manifest_cache")
		->from("#__extensions as report");
	}

	protected function getWhereQuery(&$query)
	{
        $app = J2Store::platform()->application();
        $db = JFactory::getDbo();
		$query->where("report.type='plugin'");
		$query->where("report.element LIKE 'report_%'");
		$query->where("report.folder='j2store'");
        $search = $app->input->getString('name','');
        if ($search){
            $query->where('report.name LIKE '.$db->q('%'.$search.'%'));
        }
	}

	protected function onProcessList(&$resultArray){

		foreach($resultArray as &$res){
		    $res->name = JText::_($res->name);
			$res->create_text = JText::_('J2STORE_CREATE');
			$res->view = JText::_('J2STORE_VIEW');
            $manifest_cache = json_decode($res->manifest_cache);
            $res->version = isset($manifest_cache->version) && !empty($manifest_cache->version) ? $manifest_cache->version: '1.0';
		}
	}

}
