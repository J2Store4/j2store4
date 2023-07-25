<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
jimport('joomla.mail.helper');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerCustomers extends F0FController
{
    use list_view;

	public function __construct($config =array())
	{

		parent::__construct($config);
		$this->registerTask('confirmchangeEmail','changeEmail');
	}

    public function browse()
    {
        $app = JFactory::getApplication();
        $option = $app->input->getCmd('option', '');
        $msg = JText::_($option . '_CONFIRM_DELETE');
        JToolBarHelper::deleteList(strtoupper($msg));
        $this->exportButton('customers');
        $model = $this->getThisModel();
        $state = array();
        $state['customer_name'] = $app->input->getstring('customer_name','');
        $state['email'] = $app->input->getString('email','');
        $state['address_1'] = $app->input->getString('address_1','');
        $state['country_name']= $app->input->getstring('country_name','');
        $state['company']= $app->input->getstring('company','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_address_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $header = array(
            'j2store_address_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_CUSTOMER_ID'
            ),
            'customer_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=customer&amp;task=viewOrder&amp;email_id=[ITEM:ID]",
                'url_id' => 'email',
                'label' => 'J2STORE_CUSTOMER_NAME'
            ),
            'email' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_EMAIL'
            ),
            'address_1' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_LINE1'
            ),
            'address_2' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_LINE2'
            ),
            'country_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_COUNTRY'
            ),
            'zone_name' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ZONE'
            ),
            'company' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_EMAILTEMPLATE_TAG_BILLING_COMPANY'
            ),
            'zip' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_ZIP'
            ),
            'phone_1' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_TELEPHONE'
            ),
            'phone_2' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_MOBILE'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        $format = $app->input->get('format','html');
        if($format == 'csv'){
            $this->display();
        }else{
            echo $this->_getLayout('default',$vars);
        }
    }


	/**
	 *
	 * @return boolean
	 */
	function viewOrder(){
		$email  = $this->input->getString('email_id');
		$user_id = $this->input->getInt('user_id');
		$this->layout='view';
		$this->display();
		return true;
	}


	/**
	 * Delete selected item(s)
	 *
	 * @return  bool
	 */
	public function remove()
	{
		// Initialise the App variables
		$app=JFactory::getApplication();
		$cids = $app->input->get('cid',array(),'ARRAY');
		if(!empty( $cids ) && J2Store::platform()->isClient('administrator') ){
			foreach ($cids as $cid){
				// store the table in the variable
				$address = F0FTable::getInstance('Address', 'J2StoreTable')->getClone ();
				$address->load($cid);
				$addresses = F0FModel::getTmpInstance('Addresses','J2StoreModel')->email($address->email)->getList();

				foreach ($addresses as $e_address){
					$address = F0FTable::getInstance('Address', 'J2StoreTable')->getClone ();
					$address->load($e_address->j2store_address_id);
					$address->delete ();
				}
			}
		}
		$msg = JText::_('J2STORE_ITEMS_DELETED');
		$link = 'index.php?option=com_j2store&view=customers';
		$this->setRedirect($link, $msg);
	}

	/**
	 * Method to delete customer
	 */
	function delete()
	{
		// Initialise the App variables
		$app=JFactory::getApplication();
		// Assign the get Id to the Variable
		$id=$app->input->getInt('id');

		if($id && J2Store::platform()->isClient('administrator'))
		{	// store the table in the variable
			$address = F0FTable::getInstance('Address', 'J2StoreTable');
			$address->load($id);
			$email = $address->email;
			try {
				$address->delete();
				$msg = JText::_('J2STORE_ITEMS_DELETED');
			} catch (Exception $error) {
				$msg = $error->getMessage();
			}
		}

		$link = 'index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$email;
		$this->setRedirect($link, $msg);

	}

	function editAddress(){
		// Initialise the App variables
		$app = JFactory::getApplication();
		// Assign the get Id to the Variable
		$id = $app->input->getInt('id',0);
		if($id && J2Store::platform()->isClient('administrator')) {    // store the table in the variable
			$address = F0FTable::getAnInstance('Address','J2StoreTable');
			$address->load($id);
			$address_type = $address->type;
			if(empty( $address_type )){
				$address_type = 'billing';
			}
			$model = F0FModel::getTmpInstance('Customers','J2StoreModel');
			$view = $this->getThisView();
			$view->setModel($model, true);
			$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/customer/tmpl/');
			$view->set('address_type',$address_type);
			$fieldClass  = J2Store::getSelectableBase();
			$view->set('fieldClass' , $fieldClass);
			$view->set('address',$address);
			$view->set('item',$address);
			$view->setLayout('editaddress');
			$view->display();
			//$this->display();
			return true;

		}else{
			$this->redirect ('index.php?option=com_j2store&view=customers');
		}

	}

    function saveCustomer(){
        $app = JFactory::getApplication ();
        $data = $app->input->getArray($_POST);
        $address_id = $app->input->getInt('j2store_address_id');
        $address = F0FTable::getAnInstance('Address','J2StoreTable');
        $address->load($address_id);
        $data['id'] = $data['j2store_address_id'];
        unset( $data['j2store_address_id'] );
        $data['user_id'] = $address->user_id;
        $data['email'] = $address->email;
        $selectableBase = J2Store::getSelectableBase();
        if(!in_array($data['type'],array('billing','shipping'))){
            $data['type'] = 'billing';
        }
        $data['admin_display_error'] = true;
        $json = $selectableBase->validate($data, $data['type'], 'address');
        if(empty($json['error'])){
            $msg =JText::_('J2STORE_ADDRESS_SAVED_SUCCESSFULLY');
            $msgType='message';
            $address->bind($data);
            if($address->save($data)){
                $json['success']['url'] = "index.php?option=com_j2store&view=customer&task=editAddress&id=".$address->j2store_address_id."&tmpl=component";
                $json['success']['msg'] = JText::_('J2STORE_ADDRESS_SAVED_SUCCESSFULLY');
                $json['success']['address_id'] = $address->j2store_address_id;
                $json['success']['msgType']='success';
            }else{
                $json['error']['message'] = $address->getError ();
                $json['error']['msgType']='error';
            }
        }
        echo json_encode($json);
        $app->close();
    }

	function changeEmail(){
		// Initialise the App variables
		$app=JFactory::getApplication();
		if(J2Store::platform()->isClient('administrator')){
			$json = array();
			$model = $this->getThisModel();
			// Assign the get Id to the Variable
			$email_id=$app->input->getString('email');
			$new_email=$app->input->getString('new_email');

			if(empty($new_email) && !JMailHelper::isEmailAddress($new_email) ){
				$json = array('msg' => JText::_('Invalid Email Address'), 'msgType' => 'warning');
			}else{
				//incase an account already exists ?
				if($app->input->getString('task') == 'changeEmail'){

					$json = array('msg' => JText::_('J2STORE_EMAIL_UPDATE_NO_WARNING'), 'msgType' => 'message');
					$json = $this->validateEmailexists($new_email);

				}elseif($app->input->getString('task') == 'confirmchangeEmail'){

					$json = array( 'redirect' => JUri::base().'index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$new_email, 'msg' => JText::_('J2STORE_SUCCESS_SAVING_EMAIL'), 'msgType' => 'message');
					if(!$model->savenewEmail()){
						$json = array('msg' => JText::_('J2STORE_ERROR_SAVING_EMAIL'), 'msgType' => 'warning' );
					}
				}

			}
			echo json_encode($json);
			$app->close();
		}
	}

	function validateEmailexists($new_email){
		$json = array();
		$success = true;
		$model = $this->getThisModel();

		if(J2Store::user()->emailExists($new_email)){
			$success = false;
			$json = array('msg' => JText::_('J2STORE_EMAIL_UPDATE_ERROR_WARNING'), 'msgType' => 'warning');
		}

		if($success){
			$json = array( 'redirect' => JUri::base().'index.php?option=com_j2store&view=customer&task=viewOrder&email_id='.$new_email, 'msg' => JText::_('J2STORE_SUCCESS_SAVING_EMAIL'), 'msgType' => 'message');
			if(!$model->savenewEmail()){
				$json = array('msg' => JText::_('J2STORE_ERROR_SAVING_EMAIL'), 'msgType' => 'warning' );
			}
		}
		return $json;
	}
}