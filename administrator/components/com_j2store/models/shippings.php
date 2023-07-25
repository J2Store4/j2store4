<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelShippings extends F0FModel {

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
        $this->buildOrderbyQuery($query);
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("shipping.extension_id,shipping.name,shipping.type,shipping.folder,shipping.element,shipping.params,shipping.enabled,shipping.ordering,shipping.manifest_cache")
		->from("#__extensions as shipping");
	}

	protected function getWhereQuery(&$query)
	{
		$query->where("shipping.type='plugin'");
		$query->where("shipping.element LIKE 'shipping_%'");
	}

    public function buildOrderbyQuery(&$query){
        $state = $this->getState();
        $app = JFactory::getApplication();
        $filter_order_Dir = $app->input->getString('filter_order_Dir','asc');
        if(!in_array(strtolower($filter_order_Dir),array('asc','desc'))){
            $filter_order_Dir = 'desc';
        }
        $filter_order = $app->input->getString('filter_order','extension_id');
        if($filter_order != 'version' &&$filter_order != 'id' && !empty($filter_order)){
            if(in_array ( $filter_order, array('name','version','enabled') )){
                $query->order('shipping.'.$filter_order.' '.$filter_order_Dir);
            }
        }
    }


	protected function onProcessList(&$resultArray){
		foreach($resultArray as &$res){

			//links added aren't reflecting in the form.default.xml
			$res->link_edit = 'index.php?option=com_j2store&view=shipping&task=view&id='.$res->extension_id;
			$res->plugin_link_edit="index.php?option=com_plugins&task=plugin.edit&extension_id={$res->extension_id}";
			$res->view = JText::_('J2STORE_CREATE_EDIT_LINK');
            $manifest_cache = json_decode($res->manifest_cache);
            $res->version = isset($manifest_cache->version) && !empty($manifest_cache->version) ? $manifest_cache->version: '1.0';
		}
	}


	public function getShippingRates(&$order)
	{
		static $rates;
		
		if (empty($rates) || !is_array($rates))
		{
			$rates = array();
		}

		if (!empty($rates))
		{
            if($order->order_type != 'subscription'){
                return $rates;
            }
		}

		$app = JFactory::getApplication();
		JPluginHelper::importPlugin ('j2store');

		$plugins = $this->enabled(1)->getList();
		$rates = array();

		if ($plugins)
		{
			foreach ($plugins as $plugin)
			{
				$shippingOptions = $app->triggerEvent( "onJ2StoreGetShippingOptions", array( $plugin->element, $order  ) );
				if (in_array(true, $shippingOptions, true))
				{
					$results = $app->triggerEvent( "onJ2StoreGetShippingRates", array( $plugin->element, $order  ) );					
					foreach ($results as $result)
					{
						if(is_array($result))
						{
							foreach( $result as $r )
							{
								$extra = 0;
								// here is where a global handling rate would be added
								//	if ($global_handling = $this->defines->get( 'global_handling' ))
								//	{
								//		$extra = $global_handling;
								//	}
								J2Store::plugin()->event('GetGlobalHandling', array($order, &$r, &$extra));
								$r['extra'] += $extra;
								$r['total'] += $extra;
								$rates[] = $r;
							}
						}
					}
				}
			}
		}

		//order by the cheapest method
		if(function_exists('usort') && count($rates)) {
			usort($rates, function($a, $b) {
				return $a['total'] - $b['total'];
			});
		}
        J2Store::plugin()->event('AfterGetShippingRate',array($order,&$rates));
		return $rates;
	}
}
