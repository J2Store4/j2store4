<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelQueues extends F0FModel {

	public function buildQuery($overrideLimits=false) {
		$query = parent::buildQuery($overrideLimits);
		$this->_buildQueryWhere ( $query );
		return $query;
	}

	protected function _buildQueryWhere(&$query)
	{
		$db = JFactory::getDbo ();
		$state = $this->getQueueState();
		if(isset( $state->queue_type ) && !empty( $state->queue_type )){
			$query->where ( 'queue_type ='.$db->q($state->queue_type) );
		}

		if(isset( $state->search ) && !empty( $state->search )){
			$query->where('(relation_id LIKE '.$db->q ( '%'.$state->search.'%' ).' OR status LIKE '.$db->q('%'.$state->search.'%').')');
		}

		$repeat_count = J2Store::config()->get('queue_repeat_count',10);
		if(!empty( $repeat_count )){
			$query->where ( 'repeat_count <= '.$db->q($repeat_count) );
		}
	}

	function getQueueState(){
		$state = array(
			'queue_type' => $this->getState ('queue_type',''),
			'search' => $this->getState('search','')
		);
		return $state;
	}

}