<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';


class J2StoreControllerEmailtemplates extends F0FController {


    use list_view;

    public function execute($task) {
        if (in_array($task, array('edit', 'add'))) {
            $task = 'add';
        }
        return parent::execute($task);
    }

    function add()
    {

        $platform = J2Store::platform();
        $app = $platform->application();
        $vars = $this->getBaseVars();
        if(J2Store::isPro()) {
            $this->editToolBar();
            $bar = JToolBar::getInstance();
            $id = $app->input->getInt('id',0);
            $bar->appendButton('Link','mail' , JText::_('J2STORE_EMAILTEMPLATE_SEND_TEST_EMAIL_TO_YOURSELF'),'index.php?option=com_j2store&view=emailtemplate&task=sendtest&id='.$id);
        }else {
            $this->noToolbar();
        }
        $vars->primary_key = 'j2store_emailtemplate_id';
        $vars->id = $this->getPageId();
        $emailtemplate_table = F0FTable::getInstance('Emailtemplate', 'J2StoreTable')->getClone ();
        $emailtemplate_table->load($vars->id);
        $vars->item = $emailtemplate_table;
        $vars->field_sets = array();


        $order_status_model = F0FModel::getTmpInstance('Orderstatuses', 'J2StoreModel');
        $default_order_status_list = $order_status_model->enabled(1)->getList();
        $order_status = array();
        $order_status['*'] = JText::_('JALL');
        foreach ($default_order_status_list as $status) {
            $order_status[$status->j2store_orderstatus_id] = JText::_(strtoupper($status->orderstatus_name));
        }

        $payment_model = F0FModel::getTmpInstance('Payments', 'J2StoreModel');
        $default_payment_list = $payment_model->enabled(1)->getList();

        $payment_list = array();
        $payment_list['*'] = JText::_('JALL');
        $payment_list['free'] = JText::_('J2STORE_FREE_PAYMENT');
        foreach ($default_payment_list as $payment) {
            $payment_list[$payment->element] = JText::_(strtoupper($payment->element));
        }


        $groupList = JHtmlUser::groups ();
        $group_options = array();
        $group_options [] =  JText::_ ( 'JALL' ) ;
        foreach ( $groupList as $row ) {
            $group_options [  $row->value ] = JText::_ ( $row->text ) ;
        }


        $languages = JLanguageHelper::getLanguages ( );
        $language_list = array ();
        $language_list ['*'] = JText::_ ( 'JALL_LANGUAGE' ) ;
        foreach ( $languages as  $lang ) {
            $language_list [$lang->lang_code] =JText ::_ ( strtoupper( $lang->title_native));
        }


        $vars->field_sets[] = array(
            'id' => 'basic_options',
            'label'  => 'J2STORE_BASIC_OPTIONS',
            'fields' => array(
                'receiver_type' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_RECEIVER',
                    'type' => 'list',
                    'default' => 'both',
                    'name' => 'receiver_type',
                    'desc' => 'J2STORE_EMAILTEMPLATE_RECEIVER_DESC',
                    'value' => $emailtemplate_table->receiver_type,
                    'options' => array('options' => array( '*' => JText::_('J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_BOTH'), 'admin'=> JText::_('J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_ADMIN'),'customer' =>  JText::_('J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_CUSTOMER')))
                ),
                'language' => array(
                    'label' => 'JFIELD_LANGUAGE_LABEL',
                    'type' => 'list',
                    'default' => 'en-GB',
                    'name' => 'language',
                    'desc' => 'J2STORE_EMAILTEMPLATE_LANGUAGE_DESC',
                    'value' => isset($vars->item->language) && !is_null($vars->item->language) ? $vars->item->language : '*',
                    'options' => array( 'options' => $language_list )
                ),
                'orderstatus_id' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_ORDERSTATUS',
                    'type' => 'list',
                    'name' => 'orderstatus_id',
                    'value' => isset($vars->item->orderstatus_id) && !is_null($vars->item->orderstatus_id) ? $vars->item->orderstatus_id : '*',
                    'options' => array( 'translate' => false,'options' => $order_status  ),
                    'desc' => 'J2STORE_EMAILTEMPLATE_ORDERSTATUS_DESC'
                ),
                'group_id' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_GROUPS',
                    'type' => 'list',
                    'name' => 'group_id',
                    'default' => '*',
                    'value' => isset($vars->item->group_id) && !is_null($vars->item->group_id) ? $vars->item->group_id : '*',
                    'options' => array('options' => $group_options),
                    'desc' => 'J2STORE_EMAILTEMPLATE_GROUPS_DESC'
                ),
                'paymentmethod' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_PAYMENTMETHODS',
                    'type' => 'list',
                    'name' => 'paymentmethod',
                    'value' => isset($vars->item->paymentmethod) && !is_null($vars->item->paymentmethod) ? $vars->item->paymentmethod : '*',
                    'options' => array('options' => $payment_list),
                    'desc' => 'J2STORE_EMAILTEMPLATE_PAYMENTMETHODS_DESC'
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $emailtemplate_table->enabled,
                    'options' => array('class' => 'input-xlarge')
                ),
                'subject' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_SUBJECT_LABEL',
                    'type' => 'text',
                    'name' => 'subject',
                    'value' =>$emailtemplate_table->subject,
                    'options' => array('class' => 'input-xlarge','required' => true)
                ),
                'body_source' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_BODY_SOURCE',
                    'type' => 'list',
                    'default' => 'html',
                    'name' => 'body_source',
                    'value' => $emailtemplate_table->body_source,
                    'options' => array('options' => array( 'html' => JText::_('J2STORE_HTML_INLINE_EDITOR'), 'file'=> JText::_('J2STORE_EMAILTEMPLATE_FILE_ADVANCED')))
                ),
            ),
        );
       $body_source = isset($emailtemplate_table->body_source) && !empty($emailtemplate_table->body_source) ? $emailtemplate_table->body_source: 'html';
        $source_hide = '';
        $body_source_file = '';
        $body_hide = '';
        if($body_source == 'html'){
            $source_hide = 'display:none;';
            $body_source_file = 'display:none;';
        }elseif ($body_source == 'file'){
            if(empty($emailtemplate_table-> body_source_file)){
                $source_hide = 'display:none;';
            }
            $body_hide = 'display:none;';
        }
        $vars->field_sets[] = array(
            'id' => 'advanced_information',
            'label' => 'J2STORE_ADVANCED_SETTINGS',
            'fields' => array(
               'body_source_file' => array(
                   'label' => 'J2STRE_EMAILTEMPLATE_BODY_SOURCE_FILE',
                   'type' => 'filelist',
                   'name' => 'body_source_file',
                   'value' => $emailtemplate_table-> body_source_file,
                   'style' => $body_source_file,
                   'options' => array(
                       'directory' => "administrator/components/com_j2store/views/emailtemplate/tpls",
                       'filter' => "(.*?)\.(php)",
                   )
               ),
                'source' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_FIELD_SOURCE_LABEL',
                    'type' => 'editor',
                    'name' => 'source',
                    'value' => $emailtemplate_table-> body_source_file,
                    'desc' => 'J2STORE_EMAILTEMPLATE_FIELD_SOURCE_DESC',
                    'style' => $source_hide,
                    'options' => array(
                        'editor' => 'codemirror',
                        'content' => 'from_file',
                        'syntax' => 'php',
                        'buttons' => false,
                        'height' => '500px',
                        'rows' => '20',
                        'cols' => '80',
                        'filter' => 'raw')

                ),
                'body' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_BODY_LABEL',
                    'type' => 'editor',
                    'name' => 'body',
                    'value' => $emailtemplate_table->body,
                    'style' => $body_hide,
                    'options' =>array('class' => 'input-xlarge','buttons' => true)
                ),
            )
        );
        echo $this->_getLayout('email_tab', $vars , 'edit');

    }
    public function browse()
    {
        $app = JFactory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['paymentmethod'] = $app->input->getString('paymentmethod','');
        $state['subject'] = $app->input->getString('subject','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_emailtemplate_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->edit_view = 'emailtemplates';
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_emailtemplate_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_EMAILTEMPLATE_ID'
            ),
            'receiver_type' => array(
                'type' => 'receivertypes',
                'sortable' => 'true',
                'label' => 'J2STORE_EMAILTEMPLATE_RECEIVER'
            ),
            'language' => array(
                'type' => 'text',
                'sortable' => 'true',
                'label' => 'JFIELD_LANGUAGE_LABEL'
            ),
            'orderstatus_id' => array(
                'type' => 'orderstatuslist',
                'sortable' => 'true',
                'label' => 'J2STORE_ORDERSTATUS_NAME'
            ),

            'group_id' => array(
                'type' => 'fieldsql',
                'query' => 'SELECT * FROM #__usergroups',
                'key_field' => 'id',
                'value_field' => 'title',
                'sortable' => 'true',
                'translate' => 'false',
                'label' => 'J2STORE_EMAILTEMPLATE_GROUPS'
            ),

            'paymentmethod' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_EMAILTEMPLATE_PAYMENTMETHODS'
            ),
            'subject' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=emailtemplates&amp;task=edit&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_emailtemplate_id',
                'label' => 'J2STORE_EMAILTEMPLATE_SUBJECT_LABEL'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

	/**
	 * ACL check before allowing someone to browse
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeBrowse() {

		if(parent::onBeforeBrowse()) {

			jimport('joomla.filesystem.file');
			//make sure we have a default.php template
			$filename = 'default.php';
			$tplpath = JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tpls';
			$defaultphp = $tplpath.'/default.php';
			$defaulttpl = $tplpath.'/default.tpl';

			if(!JFile::exists(JPath::clean($defaultphp))) {
				//file does not exist. so we need to rename
                if(JFile::exists(JPath::clean($defaulttpl)) ) {
                    JFile::copy($defaulttpl, $defaultphp);
                }
			}

			return true;
		}

		return false;

	}


	function sendtest() {
        $platform = J2Store::platform();
        $app = $platform->application();
		$template_id = $app->input->getInt ( 'id', 0 );
		$msgType = 'warning';
		if ($template_id) {
            $model = $this->getModel ( 'Emailtemplates' );
			try {
				$email = $model->sendTestEmail ( $template_id );
				if ($email == false) {
					$msg = JText::sprintf ( 'J2STORE_EMAILTEMPLATE_TEST_EMAIL_ERROR' );
				} else {
					$msg = JText::sprintf ( 'J2STORE_EMAILTEMPLATE_TEST_EMAIL_SENT', $email );
					$msgType = 'message';
				}
			} catch ( Exception $e ) {
				$msg = $e->getMessage ();
			}
            $url = 'index.php?option=com_j2store&view=emailtemplate&id=' . $template_id;
		} else {
			$msg = JText::_ ( 'J2STORE_EMAILTEMPLATE_NO_EMAIL_TEMPLATE_FOUND' );
			$url = 'index.php?option=com_j2store&view=emailtemplates';
		}
		$platform->redirect ( $url, $msg, $msgType );
	}
}