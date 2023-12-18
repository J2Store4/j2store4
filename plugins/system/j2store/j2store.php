<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport('joomla.html.parameter');

// Make sure FOF is loaded, otherwise do not run
if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

if (!defined('F0F_INCLUDED') || !class_exists('F0FLess', true))
{
	return;
}

// Do not run if Akeeba Subscriptions is not enabled
JLoader::import('joomla.application.component.helper');

if (!JComponentHelper::isEnabled('com_j2store', true))
{
	return;
}
if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
    require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
}else {
    return;
}

class plgSystemJ2Store extends JPlugin {

	function __construct( &$subject, $config ){
		parent::__construct( $subject, $config );

		// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
		if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
		{
			if (function_exists('error_reporting'))
			{
				$oldLevel = error_reporting(0);
			}
			$serverTimezone	 = @date_default_timezone_get();
			if (empty($serverTimezone) || !is_string($serverTimezone))
				$serverTimezone	 = 'UTC';
			if (function_exists('error_reporting'))
			{
				error_reporting($oldLevel);
			}
			@date_default_timezone_set($serverTimezone);
		}
		//load language
		$this->loadLanguage('com_j2store', JPATH_SITE);
		//if($this->_mainframe->isAdmin())return;


	}

	/**
     * J2store event for content plugin event
	*/
	function onContentPrepare($extension,&$article,&$params){
        if (!class_exists('J2Store')) {
            jimport('joomla.filesystem.file');
            if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
                require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
            }else {
                return;
            }
        }
	    J2Store::plugin()->event('ContentPrepare',array($extension,&$article,&$params));
    }

	function onAfterRoute() {

        $app = JFactory::getApplication();
		$document =JFactory::getDocument();
		$baseURL = JURI::root();
		$script = "
		var j2storeURL = '{$baseURL}';
		";
		$document->addScriptDeclaration($script);

		if(J2Store::platform()->isClient('site')) {
			//$this->_addCartJS();
            $coupon = $app->input->getString('coupon','');
            if(!empty($coupon)){
                F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' )->set_coupon($coupon);
            }
		}
        $option = $app->input->getString('option','');
        $is_change_filter_input = $this->params->get('is_change_filter_input',1);
        if($is_change_filter_input && $app->isClient('administrator') && !empty($option) && $option == 'com_j2store'){
            $script = "
            jQuery(document).on('ready',function(){
                jQuery('#adminForm input[name=\"filter_order\"').attr('name','sort_order');
                jQuery('#adminForm input[name=\"filter_order_Dir\"').attr('name','sort_order_Dir');
            });
           ";
            $document->addScriptDeclaration($script);
        }
	}

	public function onUserLogin($user, $options = array())
	{
		return $this->doLoginUser($user, $options);
	}

	private function doLoginUser($user, $options=array()) {

		$app = JFactory::getApplication();
		if(J2Store::platform()->isClient('administrator')) return true;

		$session = JFactory::getSession();
		$old_sessionid = $session->get( 'old_sessionid', '', 'j2store' );
		jimport('joomla.user.helper');
		$user['id'] = intval(JUserHelper::getUserId($user['username']));
		if (!class_exists('J2Store')) {
			jimport('joomla.filesystem.file');
			if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
				require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
			}else {
				return;
			}
		}
		//cart
		$helper = J2Store::cart();
		if (!empty($old_sessionid))
		{
			$helper->resetCart( $old_sessionid, $user['id'] );
			//TODO do the same for wish lists
		}
		else
		{
			$helper->updateSession( $user['id'], $session->getId() );
		}

		return true;
	}


	/**
	 * Called when Joomla! is booting up and checks for inventory. Thanks Nicholas (Based on Akeeba Subscriptions)
	 */
	public function onAfterInitialise()
	{
        $app = JFactory::getApplication();
        $option = $app->input->getString('option','');
        $is_change_filter_input = $this->params->get('is_change_filter_input',1);
        if($is_change_filter_input && $app->isClient('administrator') && !empty($option) && $option == 'com_j2store'){
            $sort_key = $app->input->get('sort_order','');
            $sort_order_dir = $app->input->get('sort_order_Dir','');
            if(!empty($sort_key)){
                $new_key = str_replace('sort','filter','sort_order');
                $app->input->set($new_key,$sort_key);
            }
            if(!empty($sort_order_dir)){
                $new_key = str_replace('sort','filter','sort_order_Dir');
                $app->input->set($new_key,$sort_order_dir);
            }
        }

		// Check if we need to run
	 	if (!$this->doIHaveToRun())
		{
			return;
		}
		$this->onJ2StoreCronTask('inventorycontrol');
	}


	public function onJ2StoreCronTask($task, $options = array())
	{
		if ($task != 'inventorycontrol')
		{
			return;
		}

		//check if inventory is enabled
		if (!class_exists('J2Store')) {
			jimport('joomla.filesystem.file');
			if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
				require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
			}else {
				return;
			}
		}
		$config = J2Store::config();
		if($config->get('enable_inventory', 0) != 1 || $config->get('cancel_order', 0) != 1) {
			//inventory not enabled. return.
			return;
		}

		// Get today's date
		JLoader::import('joomla.utilities.date');
		$jNow	 = new JDate();
		$now	 = $jNow->toUnix();

		F0FModel::getTmpInstance('Orders', 'J2StoreModel')->cancel_unpaid_orders();

		// Update the last run info and quit
		$this->setLastRunTimestamp();
	}

	/**
	 * Fetches the com_j2store component's parameters as a JRegistry instance
	 *
	 * @return JRegistry The component parameters
	 */
	private function getComponentParameters()
	{
		$component = JComponentHelper::getComponent('com_j2store');
        return $component->getParams();
	}

	/**
	 * "Do I have to run?" - the age old question. Let it be answered by checking the
	 * last execution timestamp, stored in the component's configuration.
	 */
	private function doIHaveToRun()
	{
		$params		 = $this->getComponentParameters();
		$lastRunUnix = $params->get('plg_j2store_inventory_control_timestamp', 0);
		$dateInfo	 = getdate($lastRunUnix);
		$nextRunUnix = mktime(0, 0, 0, $dateInfo['mon'], $dateInfo['mday'], $dateInfo['year']);
		$nextRunUnix += 24 * 3600;
		$now		 = time();

		return ($now >= $nextRunUnix);
	}

	/**
	 * Saves the timestamp of this plugin's last run
	 */
	private function setLastRunTimestamp()
	{
		$lastRun = time();
		$params	 = $this->getComponentParameters();
		$params->set('plg_j2store_inventory_control_timestamp', $lastRun);

		$db		 = JFactory::getDBO();
		$data	 = $params->toString();

		$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('params') . ' = ' . $db->q($data))
		->where($db->qn('element') . ' = ' . $db->q('com_j2store'))
		->where($db->qn('type') . ' = ' . $db->q('component'));
		$db->setQuery($query);
		$db->execute();
	}

	public function onJ2StoreAfterUpdateCart($cart_id, $data) {

		$plugin = JPluginHelper::getPlugin('system', 'cache');
		$params = J2Store::platform()->getRegistry($plugin->params);
		$options = array(
				'defaultgroup'	=> 'page',
				'browsercache'	=> $params->get('browsercache', false),
				'caching'		=> false,
		);
		$cache		= JCache::getInstance('page', $options);
		$cache->clean();
	}

	public function onJ2StoreBeforeGetPrice($pricing,$model,$calculator){
		$app = JFactory::getApplication ();
		$user_id = $app->input->getInt('user_id',0);
		$view = $app->input->get('view','');
		$task = $app->input->get('task','');

		if(!empty( $user_id ) && in_array ( $task, array('displayAdminProduct') ) && in_array ( $view, array('products') )){
			$user = JFactory::getUser ($user_id);
			$group_id = implode(',', JAccess::getGroupsByUser($user->id));
			$calculator->set('group_id',$group_id);
		}

	}

	/**
	 * add setup fee
	 * */
	function onJ2StoreCalculateFees($order) {
		$app = JFactory::getApplication ();
		$option = $app->input->get('option','');
		$view = $app->input->get('view','');
		
		if(J2Store::platform()->isClient('administrator') && in_array ( $order->order_type, array('normal') ) && $option == 'com_j2store' && in_array ( $view, array('orders','order') ) ){

			$db = JFactory::getDbo ();
			$query = $db->getQuery (true);
			$query->select('*')->from ( '#__j2store_orderfees' )->where ( 'order_id='.$db->q ( $order->order_id ) );
			$db->setQuery ( $query );
			$lists = $db->loadObjectList ();
			foreach ($lists as $list){
				$order->add_fee($list->name, $list->amount, $list->taxable, $list->tax_class_id);
			}
		}

	}

}
