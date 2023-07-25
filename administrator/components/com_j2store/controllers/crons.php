<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerCrons extends F0FController
{

	protected $cacheableTasks = array();

	function __construct() {
		$config['csrfProtection'] = 0;
		parent::__construct($config);
		$this->cacheableTasks = array();
	}

	function execute($task) {
		$this->cron();
	}

	public function cron(){
		// Makes sure SiteGround's SuperCache doesn't cache the CRON view
		$app = JFactory::getApplication();
		$app->setHeader('X-Cache-Control', 'False', true);
		$cron_key = J2Store::config ()->get ( 'queue_key','' );

		if (empty($cron_key))
		{
			header('HTTP/1.1 503 Service unavailable due to configuration');
			$app->close (503);
		}
		$secret = $app->input->get('cron_secret', null, 'raw');
		if ($secret != $cron_key)
		{
			header('HTTP/1.1 403 Forbidden');
			$app->close (403);
		}
		$command = $app->input->get('command', null, 'raw');
		$command = trim(strtolower($command));
		if (empty($command))
		{
			header('HTTP/1.1 501 Not implemented');
			$app->close (501);
		}
        $tz = JFactory::getConfig()->get('offset');
        $now_date = JFactory::getDate('now', $tz);
        $last_trigger = array(
            'date' => $now_date->toSql (),
            'url' => isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI']: '',
            'ip' => $_SERVER['REMOTE_ADDR']
        );
        J2Store::config ()->saveOne('cron_last_trigger',json_encode ( $last_trigger ));

		J2Store::plugin ()->event ( 'ProcessCron',array($command) );
		echo "$command OK";
		$app->close ();
	}

}