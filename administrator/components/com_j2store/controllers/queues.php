<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerQueues extends F0FController
{

	protected $cacheableTasks = array();

	function __construct() {
		$config['csrfProtection'] = 0;
		parent::__construct($config);
		$this->cacheableTasks = array();
	}

	function execute($task) {
		$this->processQueue();
	}

	public function processQueue(){

		$app = JFactory::getApplication ();
		J2Store::utilities()->nocache();
		$app->setHeader('X-Cache-Control', 'False', true);
		$queue_type = $app->input->get('queue_type','');
		$queue_limit = $app->input->get('queue_limit',10);
		$queue_key = $app->input->get('queue_key','');

		if (empty($queue_key))
		{
			header('HTTP/1.1 503 Service unavailable due to queue key invalid');
			$app->close ();
		}
		$store_queue_key = J2Store::config ()->get ( 'queue_key','' );
		if($queue_key != $store_queue_key){
			header('HTTP/1.1 503 Service unavailable due to queue key not match');
			$app->close ();
		}

		if(!empty( $queue_key ) && $queue_key == $store_queue_key){
			$tz = JFactory::getConfig()->get('offset');
			$now_date = JFactory::getDate('now', $tz);
			$last_trigger = array(
				'date' => $now_date->toSql (),
				'url' => isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI']: '',
				'ip' => $_SERVER['REMOTE_ADDR']
			);

			J2Store::config ()->saveOne('cron_last_trigger',json_encode ( $last_trigger ));
			$model =  F0FModel::getTmpInstance('Queues', 'J2StoreModel');
			if(!empty( $queue_type )){
				$model->setState('queue_type',$queue_type);
			}
			$model->setState('limit',$queue_limit);
			$queue_lists = $model->getList ();
			if(!empty( $queue_lists )){
				J2Store::plugin ()->event ( 'BeforeProcessQueue',array($queue_lists) );
				// process queue
				foreach ($queue_lists as $queue_list){
					J2Store::plugin ()->event ( 'ProcessQueue',array(&$queue_list) );
				}
				J2Store::plugin ()->event ( 'AfterProcessQueue',array($queue_lists) );
			}

		}else{
			$app->enqueueMessage ( JText::_ ( 'J2STORE_QUEUE_SYSTEM_SECURITY_KEY_NOT_FOUND' ) );
		}

	}
	
}