<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;

class J2StoreTableQueue extends F0FTable
{
	public function __construct($table, $key, &$db)
	{
		$table = '#__j2store_queues';
		$key = 'j2store_queue_id';
		parent::__construct($table, $key, $db);
	}

	public function check(){
		$app = JFactory::getApplication ();
		$data = $app->input->getArray ($_POST);
		if( empty( $data ) ){
			$message = JText::_ ( 'J2STORE_ADD_QUEUE_DATA_EMPTY' );
			$app->enqueueMessage ( $message, 'error' );
			return false;
		}

		if(isset( $data['queue_type'] ) && empty( $data['queue_type'] )){
			$message = JText::_ ( 'J2STORE_ADD_QUEUE_TYPE_EMPTY' );
			$app->enqueueMessage ( $message, 'error' );
			return false;
		}
		
		return true;
	}
}