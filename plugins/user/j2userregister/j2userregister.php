<?php
/**
 * --------------------------------------------------------------------------------
 * User plugin - j2store address field
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Unauthorized Access');
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

if(!class_exists('J2Store')){
    require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
}


class plgUserJ2userregister extends JPlugin
{

    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        JFactory::getLanguage()->load('com_j2store',JPATH_ADMINISTRATOR);
    }

    public function onAjaxJ2userregister(){
        $app = JFactory::getApplication();
        $post = $app->input->get ( 'j2reg',array(),"ARRAY" );
        $session = JFactory::getSession ();
        $session->set ( 'j2userregister',$post,'j2store' );
        $selectableBase = J2Store::getSelectableBase();
        $json = $selectableBase->validate($post, 'billing', 'address');
        $disable_name = $this->params->get('disable_name',0);
        if($disable_name){
            if(isset($json['error']['first_name'])){
                unset($json['error']['first_name']);
            }
            if(isset($json['error']['last_name'])){
                unset($json['error']['last_name']);
            }
        }
        if(isset($json['error']['email'])){
            unset($json['error']['email']);
        }
        if(isset($json['error']) && empty($json['error'])){
            unset($json['error']);
        }
        if(!$json){
            $json['success'] = 1;
        }
        echo json_encode($json);
        $app->close();
    }

    public function onContentPrepareForm($form, $data)
    {

        if (!($form instanceof JForm))
        {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }

        // Check we are manipulating a valid form.
        $name = $form->getName();

        $show_address_fields = $this->params->get('show_address_fields', 1);

        if (in_array($name, array('com_users.registration', 'com_users.user')) && $show_address_fields)
        {

            //if this is administrator, we need to load a few files
            $app = JFactory::getApplication();
            if(J2Store::platform()->isClient('administrator')) {
                require_once (JPATH_SITE.'/administrator/components/com_j2store/helpers/strapper.php');
                J2StoreStrapper::addJS();
                J2StoreStrapper::addCSS();
            }
            // Add the registration fields to the form.
            JForm::addFormPath(dirname(__FILE__) . '/fields');
            $form->loadFile('j2storecustom', false);
        }
        $show_profile = $this->params->get ( 'show_myprofile',0 );
        if (in_array($name, array('com_users.profile')) && $show_profile)
        {
            $input = JFactory::getApplication()->input;
            $task = $input->get('task','');
            $option = $input->get('option','');
            $view = $input->get('view','');
            $layout = $input->get('layout','');
            if($task == "" &&  $option == "com_users" && $view == "profile" && $layout == ""){
                @ob_start();
                F0FDispatcher::getTmpInstance('com_j2store', 'myprofile', array('layout'=>'default', 'tmpl'=>'component', 'input' => $input))->dispatch();
                $html = ob_get_contents();
                ob_end_clean();
                echo $html;
            }
        }

        return true;
    }

    public function onUserAfterSave($data, $isNew, $result, $error)
    {
        $platform = J2Store::platform();
        $userId	= $platform->getValue( $data, 'id', 0, 'int' );
        //JArrayHelper::getValue($data, 'id', 0, 'int');
        $app = JFactory::getApplication();
        $show_address_fields = $this->params->get('show_address_fields', 1);
        $j2store_fields = $app->input->get ( 'j2reg',array(),"ARRAY" );
        $app = JFactory::getApplication();
        $disable_name = $this->params->get('disable_name',0);
        if($disable_name){
            $joom_fields = $app->input->get ( 'jform',array(),"ARRAY" );
            $name = isset($joom_fields['name']) && !empty($joom_fields['name']) ? $joom_fields['name']: (isset($joom_fields['username']) && !empty($joom_fields['username']) ? $joom_fields['username']: '');
            $joomla_name = explode(' ',$name);
            $first_name = '';
            $last_name = '';
            if(isset($joomla_name[0]) && !empty($joomla_name[0])){
                $first_name = $joomla_name[0];
            }
            if(isset($joomla_name[1]) && !empty($joomla_name[1])){
                $last_name = $joomla_name[1];
            }
            if(empty($first_name)){
                $first_name = $name;
            }
            if(empty($last_name)){
                $last_name = $name;
            }
            $j2store_fields['first_name'] = $first_name;
            $j2store_fields['last_name'] = $last_name;
        }


        if($platform->isClient('administrator') && $userId && $result && !empty($j2store_fields) && $show_address_fields) {
            return $this->saveAddress($j2store_fields, $userId, $result);
        }elseif($platform->isClient('site') && $userId && $result && $isNew && !empty( $j2store_fields ) && $show_address_fields )
        {
            return $this->saveAddress($j2store_fields, $userId, $result);
        }

        return true;
    }

    public function saveAddress($data, $userId, $result) {

        try
        {
            // save to j2store address table
            $address = F0FTable::getInstance('Address', 'J2StoreTable')->getClone();
            if(isset($data['j2store_address_id']) && $data['j2store_address_id'] > 0) {
                //attempt to load
                $address->load(intval($data['j2store_address_id']));
                if($address->user_id !== $userId) {
                    //re-set the address object
                    unset($address);
                    $address = F0FTable::getInstance('Address', 'J2StoreTable')->getClone();
                }
            }
            $address->bind($data);
            $address->user_id = $userId;
            $address->email = JFactory::getUser($userId)->email;

            if($address->store()){
                J2Store::plugin ()->event ( 'UserRegisterAfterSave', array($address,$data,$result) );
                $session = JFactory::getSession ();
                $session->clear ( 'j2userregister','j2store' );
            }

        }
        catch (Exception $e)
        {
            return false;
        }
        return true;
    }
}
