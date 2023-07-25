<?php
/**
 * @package J2Store
 * @author Alagesan
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
class J2StorePlatform {

    /**
     * instance variable
     * @var null
     */
    public static $instance = null;

    /**
     * J2StorePlatform constructor.
     * @param null $properties
     */
    public function __construct($properties=null) {}

    /**
     * class instance
     * @return J2StorePlatform|null
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return \Joomla\CMS\Application\CMSApplication|null
     */
    public function application(){
        $app = null;
        try{
            if(version_compare(JVERSION,'3.99.99','lt') && class_exists('\JFactory')){
                $app = JFactory::getApplication();
            }elseif(version_compare(JVERSION,'3.99.99','ge') && class_exists('\Joomla\CMS\Factory')){
                $app = \Joomla\CMS\Factory::getApplication();
            }
        }catch (\Exception $e){
            $app = null;
        }
        return $app;
    }

    public function redirect($url,$message = '',$notice = 'info'){
        $app = $this->application();
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            if(!empty($message)){
                $app->enqueueMessage(JText::_($message),$notice);
            }
            $app->redirect($url);
        } else if(version_compare(JVERSION, '3.99.99','lt')){
            $app->redirect($url, JText::_($message));
        }
    }

    public function isClient($identifier = 'site'){
        try{
            $status = $this->application()->isClient($identifier);
        }catch (\Exception $e){
            $status = false;
        }
        return $status;
    }

    public function toInteger($input,$default = null){
        $output = array();
        if (!empty($input) && version_compare(JVERSION, '3.99.99', 'ge') && class_exists(' \Joomla\Utilities\ArrayHelper')) {
            $output = \Joomla\Utilities\ArrayHelper::toInteger($input,$default);
        } else if(!empty($input) && version_compare(JVERSION, '3.99.99','lt') && class_exists('\JArrayHelper')){
            \JArrayHelper::toInteger($input,$default);
            $output = $input;
        }else{
            if (\is_array($input))
            {
                return array_map('intval', $input);
            }
            if ($default !== null)
            {
                $output = $default;
            }
        }
        return $output;
    }

    public function fromObject($source, $recurse = true, $regex = null){
        $output = array();
        if (version_compare(JVERSION, '3.99.99', 'ge') && class_exists('\Joomla\Utilities\ArrayHelper')) {
            $output = \Joomla\Utilities\ArrayHelper::fromObject($source, $recurse, $regex);
        } else if (version_compare(JVERSION, '3.99.99', 'lt') && class_exists('\JArrayHelper')) {
            $output = \JArrayHelper::fromObject($source, $recurse, $regex);
        }
        return $output;
    }

    public function toObject(array $array, $class = 'stdClass', $recursive = true){
        $output = new stdClass();
        if (version_compare(JVERSION, '3.99.99', 'ge') && class_exists('\Joomla\Utilities\ArrayHelper')) {
            $output = \Joomla\Utilities\ArrayHelper::toObject($array, $class, $recursive);
        } else if (version_compare(JVERSION, '3.99.99', 'lt') && class_exists('\JArrayHelper')) {
            $output = JArrayHelper::toObject($array, $class, $recursive);
        }
        return $output;
    }

    public function toString(array $array, $innerGlue = '=', $outerGlue = ' ', $keepOuterKey = false){
        $output = '';
        if (version_compare(JVERSION, '3.99.99', 'ge') && class_exists('\Joomla\Utilities\ArrayHelper')) {
            $output = \Joomla\Utilities\ArrayHelper::toString($array, $innerGlue, $outerGlue, $keepOuterKey);
        } else if (version_compare(JVERSION, '3.99.99', 'lt') && class_exists('\JArrayHelper')) {
            $output = JArrayHelper::toString($array, $innerGlue, $outerGlue, $keepOuterKey);
        }
        return $output;
    }

    public function getValue($array, $name, $default = null, $type = ''){
        $output = $default;
        if (version_compare(JVERSION, '3.99.99', 'ge') && class_exists('\Joomla\Utilities\ArrayHelper')) {
            $output = \Joomla\Utilities\ArrayHelper::getValue($array, $name, $default, $type);
        } else if (version_compare(JVERSION, '3.99.99', 'lt') && class_exists('\JArrayHelper')) {
            $output = JArrayHelper::getValue($array, $name, $default, $type);
        }
        return $output;
    }

    public function loadExtra($behaviour,...$methodArgs){
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            if(!in_array($behaviour,array('behavior.framework','behavior.modal','bootstrap.tooltip','behavior.tooltip'))){
                \Joomla\CMS\HTML\HTMLHelper::_($behaviour,implode(',',$methodArgs));
            }elseif($behaviour == 'behavior.modal'){
                \Joomla\CMS\HTML\HTMLHelper::_('script', 'system/fields/modal-fields.min.js', array('version' => 'auto', 'relative' => true));
            }
        }else if (version_compare(JVERSION, '3.99.99', 'lt') && class_exists('\JHtml')){
            if(!in_array($behaviour,array('draggablelist.draggable'))){
                JHtml::_($behaviour,implode(',',$methodArgs));
            }
        }
    }
    public function addIncludePath($path){
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            \Joomla\CMS\HTML\HTMLHelper::addIncludePath($path);
        }else if (version_compare(JVERSION, '3.99.99', 'lt') && class_exists('\JHtml')){
             JHtml::addIncludePath($path);
        }
    }
    public function checkAdminMenuModule(){
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $db = JFactory::getDbo();
            $sql = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__modules')
                ->where($db->qn('module') . ' = ' . $db->q('mod_j2store_menu'));
            $db->setQuery($sql);
            try
            {
                $count = $db->loadResult();

                if($count > 0){
                    $sql = $db->getQuery(true)
                        ->update($db->qn('#__modules'))
                        ->set($db->qn('published') . ' = ' . $db->q(0))
                        ->where($db->qn('module') . ' = ' . $db->q('mod_j2store_menu'));
                    $db->setQuery($sql);
                    $db->execute();
                }
            }
            catch (Exception $exc)
            {

            }
        }
    }
    public function addScript($asset, $uri ,$options = [], $attributes = [], $dependencies = []){
        $url = trim(JURI::root(),'/').$uri;
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $wa = $this->application()->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript($asset,$url,$options,$attributes,$dependencies);
        }elseif (version_compare(JVERSION, '3.99.99', 'lt')){
            $document = JFactory::getDocument();
            $document->addScript($url,$options,$attributes);
        }
    }
    public function addStyle($asset, $uri ,$options = [], $attributes = [], $dependencies = []){
        $url = trim(JURI::root(),'/').$uri;
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $wa = $this->application()->getDocument()->getWebAssetManager();
            $wa->registerAndUseStyle($asset,$url,$options,$attributes,$dependencies);
        }elseif (version_compare(JVERSION, '3.99.99', 'lt')){
            $document = JFactory::getDocument();
            $document->addStyleSheet($url ,$options,$dependencies);
        }
    }

    public function addInlineScript( $content, $options = [], $attributes = [], $dependencies = []){
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $wa = $this->application()->getDocument()->getWebAssetManager();
            $wa->addInlineScript($content,$options,$attributes,$dependencies);
        }elseif (version_compare(JVERSION, '3.99.99', 'lt')){
            $document = JFactory::getDocument();
            $document->addScriptDeclaration($content);
        }
    }

    public function addInlineStyle( $content, $options = [], $attributes = [], $dependencies = []){
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $wa = $this->application()->getDocument()->getWebAssetManager();
            $wa->addInlineStyle($content,$options,$attributes,$dependencies);
        }elseif (version_compare(JVERSION, '3.99.99', 'lt')){
            $document = JFactory::getDocument();
            $document->addStyleDeclaration($content);
        }
    }
    public function raiseError($code, $message)
    {
        throw new Exception($message, $code);
    }

    public function getMyprofileUrl($params = array(),$is_xml = false,$no_sef = false){
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'myprofile'
        );
        $active = J2StoreRouterHelper::findMenuMyprofile ( $qoptions );

        $url = 'index.php?option=com_j2store&view=myprofile';
        if(!empty($params)){
            $url .= "&".http_build_query($params);
        }
        if(isset($active) && is_object($active) && $active->id){
            $url .= '&Itemid='.$active->id;
        }
        if(!$no_sef){
            $url = JRoute::link('site',$url,$is_xml);
        }
        return $url;
    }

    public function getCheckoutUrl($params = array()){
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'checkout'
        );
        $active = J2StoreRouterHelper::findCheckoutMenu( $qoptions );
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }
        return JRoute::link('site','index.php?option=com_j2store&view=checkout&'.http_build_query($params).$item_id,false);
    }

    function getThankyouPageUrl($params = array()){
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'checkout',
            'layout' => 'postpayment',
            'task' => 'confirmPayment'
        );
        $active = J2StoreRouterHelper::findThankyouPageMenu( $qoptions );
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }else{
            unset($qoptions['layout']);
            unset($qoptions['task']);
            $active = J2StoreRouterHelper::findCheckoutMenu( $qoptions );
            if(isset($active) && is_object($active) && $active->id){
                $item_id = '&Itemid='.$active->id;
            }
        }
        return JRoute::link('site','index.php?option=com_j2store&view=checkout&layout=postpayment&task=confirmPayment&'.http_build_query($params).$item_id,false);
    }

    public function getCartUrl($params = array()){
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
            'view' => 'carts'
        );
        $active = J2StoreRouterHelper::findMenuCarts($qoptions );
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }
        return JRoute::link('site','index.php?option=com_j2store&view=carts&'.http_build_query($params).$item_id,false);
    }

    function getProductUrl($params = array(),$is_tag_view = false){
        require_once 'router.php';
        $qoptions = array(
            'option' => 'com_j2store',
        );
        $view = $this->application()->input->get('view','');
        if($view == 'producttags'){
            $qoptions['view'] = 'producttags';
        }elseif($is_tag_view){
            $qoptions['view'] = 'producttags';
        }else{
            $qoptions['view'] = 'products';
        }
        $qoptions = array_merge($qoptions,$params);

        if($qoptions['view'] == 'producttags'){
            $active = J2StoreRouterHelper::findProductTagsMenu( $qoptions );
        }else{
            $active = J2StoreRouterHelper::findProductMenu( $qoptions );
        }
        $item_id = '';
        if(isset($active) && is_object($active) && $active->id){
            $item_id = '&Itemid='.$active->id;
        }
        return JRoute::link('site','index.php?option=com_j2store&view='.$qoptions['view'].'&'.http_build_query($params).$item_id,false);
    }

    function getRootUrl(){
        $rootURL = rtrim(JURI::base(),'/');
        $subpathURL = JURI::base(true);
        if(!empty($subpathURL) && ($subpathURL != '/')) {
            $rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
        }
        return $rootURL;
    }

    public function getRegistry($json,$is_array = false)
    {
        if (!$json instanceof JRegistry || !$json instanceof \Joomla\Registry\Registry) {
            $params = new JRegistry();
            try {
                if($is_array){
                    $params->loadArray($json);
                }else{
                    $params->loadString($json);
                }
            } catch (\Exception $e) {
                $params = new JRegistry('{}');
            }
        } else {
            $params = $json;
        }
        return $params;
    }
    public function getImagePath($path)
    {
        $status = false;
        if(empty($path)){
            return $status;
        }
        $file_path = parse_url($path);
        if(isset($file_path['path']) && !empty($file_path['path']) && JFile::exists(JPATH_SITE.'/'.urldecode($file_path['path']))){
            $status = JUri::root().$file_path['path'];
        }
        return $status;
    }
    public function getLabel($label_info = ''){
        $label_class = 'badge bg-';
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $label_class = 'label label-';
        }
        return $label_class.$label_info;
    }
    public function getMenuLinks(){
        if(version_compare(JVERSION,'3.99.99','lt')){
            JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
            $items = MenusHelper::getMenuLinks();
        }else{
            $items = \Joomla\Component\Menus\Administrator\Helper\MenusHelper::getMenuLinks();
        }
        return $items;
    }

    public function eventTrigger($event_name,$args){
        if(version_compare(JVERSION,'3.99.99','lt')){
            JPluginHelper::importPlugin('j2store');
            $allowed_plugins = $this->eventJ2Store4('onJ2StoreIsJ2Store4');
            $event_dispactor = \JEventDispatcher::getInstance();
            $observers = $event_dispactor->get('_observers');
            $need_to_remove = array();
            $test = array();
            foreach ($observers as $handle_id => $observer) {
                if (is_object($observer) && $handle_id > 0) {
                    $type = $observer->get('_type');
                    $element = $observer->get('_name');
                    // $test[$handle_id] = $element;
                    if (isset($element) && !in_array($element, $allowed_plugins) && $type == 'j2store') {
                        $need_to_remove[] = $handle_id;
                        //$test[$handle_id] = $element;
                    }
                }
            }
            $methods = $event_dispactor->get('_methods');
            if (!empty($need_to_remove) && array_key_exists(strtolower($event_name), $methods)) {
                foreach ($need_to_remove as $remove_id) {
                    if (in_array($remove_id, $need_to_remove) && in_array($remove_id, $methods[strtolower($event_name)])) {
                        $key = array_search($remove_id, $methods[strtolower($event_name)]);
                        unset($methods[strtolower($event_name)][$key]);
                        // echo "<pre>".$key.'<br>'.$remove_id.'<br>'.$event_name.'<br>'.$test[$remove_id].'<br></pre>';
                    }
                }
            }
            $event_dispactor->set('_methods', $methods);
            $results = $event_dispactor->trigger($event_name, $args);
        } else {
            $results = $this->application()->triggerEvent($event_name, $args);
        }

        return $results;
    }

    public function eventJ2Store4($eventName){
        $plugin_helper = J2Store::plugin();
        $return = array();
        $db = JFactory::getDBO();
        $order_query = " ORDER BY ordering ASC ";
        $query = "SELECT * FROM #__extensions WHERE  enabled = '1' AND folder=".$db->q('j2store')." AND type='plugin' {$order_query}";
        $db->setQuery( $query );
        $plugins = $db->loadObjectList();
        if (!empty($plugins))
        {
            foreach ($plugins as $plugin)
            {
                if ($plugin_helper->hasEvent( $plugin, $eventName ))
                {
                    $return[] = $plugin->element;
                }
            }
        }
        JPluginHelper::importPlugin('j2store');
        $app = JFactory::getApplication();
        $app->triggerEvent('onJ2StoreAfterGetPluginsWithEvent', array(&$return));
        return $return;
    }
}