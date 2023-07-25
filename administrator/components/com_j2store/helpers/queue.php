<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * J2Html class provides Form Inputs
 */

class J2Queue extends JObject
{

	public static $instance;

	public function __construct ( $properties = null )
	{
		parent::__construct ( $properties );

	}

	public static function getInstance ( $properties = null )
	{
		if ( !self::$instance ) {
			self::$instance = new self( $properties );
		}

		return self::$instance;
	}

	public function deleteQueue($list){
		if(isset($list->j2store_queue_id) && !empty($list->j2store_queue_id)) {
			$queue_table = F0FTable::getInstance ( 'Queue', 'J2StoreTable' )->getClone ();
			$queue_table->load ( $list->j2store_queue_id );
			$queue_table->delete ();
		}
	}


	function resetQueue($list,$day = 7){
		if(isset($list->j2store_queue_id) && !empty($list->j2store_queue_id)){
			$queue_table = F0FTable::getInstance('Queue', 'J2StoreTable')->getClone();
			$queue_table->load($list->j2store_queue_id);
			$new_table = clone $queue_table;

			//delete the current queue
			$queue_table->delete();

			$new_table->j2store_queue_id = '';
			$tz = JFactory::getConfig()->get('offset');
			$current_date = JFactory::getDate('now', $tz)->toSql(true);
			$date_string = 'now +'.$day.' day';
			$date = JFactory::getDate($date_string, $tz)->toSql(true);
			$new_table->status = 'Requeue';
			$new_table->expired = $date;
			$new_table->repeat_count += 1;
			$new_table->modified_on = $current_date;
			$new_table->store();
		}
	}

}