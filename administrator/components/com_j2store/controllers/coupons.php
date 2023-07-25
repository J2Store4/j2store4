<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerCoupons extends F0FController
{

    use list_view;

    public function execute($task) {
        if (in_array($task, array('edit', 'add'))) {
            $task = 'add';
        }
        return parent::execute($task);
    }

    public function onBeforeApplySave(&$data){
        if(is_array($data)){
            $data['valid_from'] = isset($data['valid_from']) && !empty($data['valid_from']) ? $data['valid_from']:'0000-00-00 00:00:00';
            $data['valid_to'] = isset($data['valid_to']) && !empty($data['valid_to']) ? $data['valid_to']:'0000-00-00 00:00:00';
            $data['value'] = isset($data['value']) && !empty($data['value']) && $data['value'] > 0  ? $data['value'] : 0 ;
        }
        return true;
    }

    function add()
    {
        $platform = J2Store::platform();
        $platform->loadExtra('formbehavior.chosen', '.chosenselect');
        $platform->loadExtra('behavior.multiselect');
        $app = $platform->application();
        $task = $app->input->getstring('task','');
        $vars = $this->getBaseVars();
        if(J2Store::isPro()) {
            $this->editToolBar();
            JToolBarHelper::divider();
            if($task == 'edit') {
                // Hide the content
                JToolbarHelper::save2copy('copy');
                $bar = JToolBar::getInstance('toolbar');
                $bar->appendButton('Link', 'list', JText::_('J2STORE_COUPON_HISTORY'), 'index.php?option=com_j2store&view=coupon&task=history&coupon_id=' . $app->input->getInt('id'));
            }
        }else {
            $this->noToolbar();
        }
        $vars->primary_key = 'j2store_coupon_id';
        $vars->id = $this->getPageId();
        $coupon_table = F0FTable::getInstance('Coupon', 'J2StoreTable')->getClone ();
        $coupon_table->load($vars->id);
        $vars->item = $coupon_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $col_class = 'span';
        }

        $vars->field_sets[] = array(
            'id' => 'basic_options',
            'label' => 'J2STORE_BASIC_SETTINGS',
            'fields' => array(
                'coupon_name' => array(
                    'label' => 'J2STORE_COUPON_NAME',
                    'type' => 'text',
                    'desc' => 'J2STORE_CURRENCY_TITLE_DESC',
                    'name' => 'coupon_name',
                    'value' => $coupon_table->coupon_name,
                    'options' => array('required' => 'true','class' => 'inputbox','filter' => 'intval')
                ),
                'coupon_code' => array(
                    'label' => 'J2STORE_COUPON_CODE',
                    'type' => 'text',
                    'name' => 'coupon_code',
                    'value' => $coupon_table->coupon_code,
                    'options' => array('required' => 'true','filter' => 'intval','class' => 'inputbox')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $coupon_table->enabled,
                    'options' => array('class' => 'input-xlarge')
                ),
                'free_shipping' => array(
                    'label' => 'J2STORE_FREE_SHIPPING',
                    'type' => 'radio',
                    'name' => 'free_shipping',
                    'desc' => 'J2STORE_COUPON_FREE_SHIPPING_HELP_TEXT',
                    'value' => $coupon_table->free_shipping,
                    'default' => '0',
                    'options' => array('option' => array( 0 => JText::_('J2STORE_NO'), 1 => JText::_('J2STORE_YES')))
                ),
                'value' => array(
                    'label' => 'J2STORE_COUPON_VALUE',
                    'type' => 'text',
                    'name' => 'value',
                    'value' => $coupon_table->value,
                    'options' => array('class' => 'inputbox','filter' => 'intval' )
                ),
                'value_type' => array(
                    'label' => 'J2STORE_COUPON_VALUE_TYPE',
                    'type' => 'coupondiscounttypes',
                    'name' => 'value_type',
                    'source_class'=> 'J2StoreModelCoupons',
                    'source_file'=>'admin://components/com_j2store/models/coupons.php',
                    'value' => $coupon_table->value_type,
                    'options' => array('id' => 'disount_type','class' => 'input-xlarge')
                ),
                'valid_from' => array(
                    'label' => 'J2STORE_COUPON_VALID_FROM',
                    'type' => 'calendar',
                    'name' => 'valid_from',
                    'value' => $coupon_table->valid_from,
                    'singleheader' => 'true',
                    'options' => array('class' => 'input-xlarge',
                        'showtime' => 'true',
                        'timeformat' => '24',
                        'todaybutton' => true,
                        'translateformat' => 'true',
                        'format' => '%Y-%m-%d %H:%M:%S')
                 ),

                'valid_to' => array(
                    'label' => 'J2STORE_COUPON_VALID_TO',
                    'type' => 'calendar',
                    'name' => 'valid_to',
                    'value' => $coupon_table->valid_to,
                    'options' => array('class' => 'input-xlarge',
                        'showtime' => 'true',
                        'timeformat' => '24',
                        'todaybutton' => true,
                        'translateformat' => 'true',
                        'format' => '%Y-%m-%d %H:%M:%S')
                ),
            ),
        );
        $groupList = JHtmlUser::groups ();
        $group_options = array();
        $group_options [] =  JText::_ ( 'JALL' ) ;
        foreach ( $groupList as $row ) {
            $group_options[$row->value] =JText ::_ ( strtoupper( $row->text));
        }
        $items =  F0FModel::getTmpInstance('Manufacturers','J2StoreModel')->getItemList();
        $new_options  = array();
        $new_options[] = JText::_('J2STORE_ALL');
        foreach($items as $brand){
            $new_options[$brand->j2store_manufacturer_id] = $brand->company;
        }
        $vars->field_sets[] = array(
            'id' => 'advanced_information',
            'label' => 'J2STORE_ADVANCED_SETTINGS',
            'fields' => array(
                'product_category' => array(
                    'label' => 'J2STORE_COUPON_PRODUCT_CATEGORY',
                     'name' => 'product_category',
                    'type' => 'duallistbox',
                    'desc' => 'J2STORE_CURRENCY_NUM_DECIMALS_DESC',
                    'value' => $coupon_table->product_category,
                    'options' => array('id'=> 'product-category','class' => 'inputbox hideMe','multiple' => true,
                        'data_value'=> 'id',
                        'data_text'=> 'title',
                        'data_title'=> 'JCATEGORIES',
                        'data_maxAllBtn' => '500',
                        'source_file'=> "admin://components/com_j2store/helpers/select.php",
                        'source_class'=> 'J2StoreHelperSelect',
                        'source_method' => 'getContentCategories',
                        'labelclass' => 'j2store-label'
                    )
                ),
                'product_links' => array(
                    'label' => 'J2STORE_COUPON_PRODUCTS',
                    'type' => 'couponproducts',
                    'name' => 'product_links',
                    'value' => $coupon_table->products,
                    'options' => array('class' => 'hideMe')
                ),
                'brand_ids' => array(
                    'label' => 'J2STORE_PRODUCT_MANUFACTURER',
                    'type' => 'list',
                    'name' => 'brand_ids[]',
                    'value' => isset($vars->item->brand_ids) && !is_null($vars->item->brand_ids) ? explode(',',$vars->item->brand_ids)  : '*',
                        'options' => array('class' => 'chosenselect','multiple' => true,'options' => $new_options ),
                ),
                'logged' => array(
                    'label' => 'J2STORE_COUPON_LOGGED',
                    'type' => 'radio',
                    'desc' => 'J2STORE_COUPON_LOGGED_HELP_TEXT',
                    'name' => 'logged',
                    'value' =>$coupon_table->logged,
                    'options' => array('class' => 'btn-group','options' => array(0 => JText::_('J2STORE_NO'), 1 => JText::_('J2STORE_YES')))
                ),
                'user_group' => array(
                    'label' => 'J2STORE_CUSTOMER_GROUPS',
                    'type' => 'list',
                    'name' => 'user_group[]',
                    'default' => '*',
                    'value' => isset($vars->item->user_group) && !is_null($vars->item->user_group) ? explode(',',$vars->item->user_group) : '*',
                    'options' => array('class' => 'chosenselect','multiple' => true,'options' => $group_options ),
                    'desc' => 'J2STORE_COUPON_CUSTOMER_GROUPS_HELP_TEXT'
                ),
                'users' => array(
                    'label' => 'J2STORE_USERS',
                    'type' => 'text',
                    'desc' => 'J2STORE_COUPON_USERS_HELP_TEXT',
                    'name' => 'users',
                    'value' =>$coupon_table->users,
                    'options' => array('class' => 'inputbox')
                ),
                'min_subtotal' => array(
                    'label' => 'J2STORE_COUPON_MINIMUM_SUBTOTAL',
                    'type' => 'text',
                    'desc' => 'J2STORE_COUPON_MINIMUM_SUBTOTAL_HELP_TEXT',
                    'name' => 'min_subtotal',
                    'value' =>$coupon_table->min_subtotal,
                    'options' => array('class' => 'inputbox')
                ),
            )
        );
        $vars->field_sets[] = array(
            'id' => 'usage_information',
            'label' => 'J2STORE_COUPON_USAGE_LIMIT_SETTINGS',
            'fields' => array(
                'max_uses' => array(
                    'label' => 'J2STORE_COUPON_MAXIMUM_USES',
                    'type' => 'text',
                    'desc' => 'J2STORE_COUPON_MAXIMUM_USES_HELP_TEXT',
                    'name' => 'max_uses',
                    'value' => $coupon_table->max_uses ,
                    'options' => array('class' => 'inputbox' )
                ),
                'max_quantity' => array(
                    'label' => 'J2STORE_COUPON_MAXIMUM_ITEM_LIMIT',
                    'type' => 'number',
                    'name' => 'max_quantity',
                    'desc' => 'J2STORE_COUPON_MAXIMUM_ITEM_LIMIT_DESC',
                    'min' => 0,
                    'step' => 1,
                    'value' => $coupon_table->max_quantity,
                    'options' => array('id' => 'max_quantity','class' => 'inputbox')
                ),
                'max_customer_uses' => array(
                    'label' => 'J2STORE_COUPON_MAXIMUM_CUSTOMER_USES',
                    'type' => 'text',
                    'desc' => 'J2STORE_COUPON_MAXIMUM_CUSTOMER_USES_HELP_TEXT',
                    'name' => 'max_customer_uses',
                    'value' => $coupon_table->max_customer_uses ,
                    'options' => array('class' => 'inputbox' )
                ),
            ),
        );
        echo $this->_getLayout('coupon_tab', $vars,'edit');
    }

    public function browse()
    {
        $app = JFactory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['coupon_name'] = $app->input->getString('coupon_name','');
        $state['coupon_code'] = $app->input->getString('coupon_code','');
        $state['value'] = $app->input->getString('value','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_coupon_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->edit_view = 'coupons';
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_coupon_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_COUPON_ID'
            ),
            'coupon_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=coupons&amp;task=edit&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_coupon_id',
                'label' => 'J2STORE_COUPON_NAME'
            ),
            'coupon_code' => array(
                'sortable' => 'true',
                'type' => 'fieldsearchable',
                'label' => 'J2STORE_COUPON_CODE'
            ),
            'value' => array(
                'sortable' => 'true',
                'type' => 'fieldsearchable',
                'label' => 'J2STORE_COUPON_VALUE'
            ),
            'valid_from' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_COUPON_VALID_FROM'
            ),
            'valid_to' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_COUPON_VALID_TO'
            ),
            'expire_date' => array(
                'sortable' => 'true',
                'type' => 'couponexpiretext',
                'label' => 'J2STORE_COUPON_EXPIRY'
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
     * Method to set view to add products
     *
     */

function setProducts(){
		//get variant id
		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$limit = $this->input->getInt('limit',20);
		$limitstart = $this->input->getInt('limitstart',0);

		//sku search
		$search = $this->input->getString('search','');
		$model->setState('search',$search);
		$model->setState('limit',$limit);
		$model->setState('limitstart',$limitstart);
		$model->setState('enabled',1);
		$items = $model->getProductList();
		$layout = $this->input->getString('layout');
		$view = $this->getThisView('Coupons');
		$view->setModel($model, true);
		$view->set('state',$model->getState());
		$view->set('pagination',$model->getPagination());
		$view->set('total',$model->getTotal());
		$view->set('productitems',$items);
		$view->setLayout($layout);
		$view->display();
	}

	public function history() {
		$app = JFactory::getApplication();
		$coupon_id = $app->input->getInt('coupon_id');
		$view = $this->getThisView();
		if($coupon_id > 0) {
			if ($model = $this->getThisModel())
			{
				// Push the model into the view (as default)
				$view->setModel($model, true);
			}
			$coupon = F0FTable::getAnInstance('Coupon', 'J2StoreTable');
			$coupon->load($coupon_id);
			$view->assign('coupon', $coupon);
			$coupon_history = $model->getCouponHistory();
			$view->assign('coupon_history', $coupon_history);
			$view->assign('params', J2Store::config());
			$view->assign('currency', J2Store::currency());

		}
		$view->setLayout('history');
		$view->display();
	}
}