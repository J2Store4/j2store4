<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class J2StoreViewProduct extends F0FViewHtml
{
	public function preRender() {
		$view = $this->input->getCmd('view', 'cpanel');
		$task = $this->getModel()->getState('task', 'browse');

		$renderer = $this->getRenderer();
		$renderer->preRender($view, $task, $this->input, $this->config);

	}

	protected function onEdit($tpl = null) {
		return $this->onAdd($tpl);
	}


	protected function onAdd($tpl = null) {
		$platform = J2Store::platform();
        $platform->application()->set('hidemainmenu',true);
		$model = $this->getModel();
		$this->item = $model->runMyBehaviorFlag(true)->getItem();
		$this->currency = J2Store::currency();
		$this->form_prefix = $this->input->getString('form_prefix', '' );
		$this->product_source_view  = $this->input->getString('product_source_view', 'article' );
		$this->product_types = JHtml::_('select.genericlist', $model->getProductTypes(), $this->form_prefix.'[product_type]', array(), 'value', 'text', $this->item->product_type);

		if($this->item->j2store_product_id) {

			//manufacturers
			$this->manufacturers = J2Html::select()->clearState()
						->type('genericlist')
						->name($this->form_prefix.'[manufacturer_id]')
						->value($this->item->manufacturer_id)
						->setPlaceHolders(
								array(''=>JText::_('J2STORE_SELECT_OPTION'))
						)
						->hasOne('Manufacturers')
						->setRelations( array(
										'fields' => array (
												'key' => 'j2store_manufacturer_id',
												'name' => array('company')
										)
									)
						)->getHtml();


			//vendor
			$this->vendors = J2Html::select()->clearState()
												->type('genericlist')
												->name($this->form_prefix.'[vendor_id]')
												->value($this->item->vendor_id)
												->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
												->hasOne('Vendors')
												->setRelations(
																array (
																	'fields' => array
																		 		(
																					'key'=>'j2store_vendor_id',
																					'name'=>array('first_name','last_name')
																				)
																		)
													)->getHtml();

			//tax profiles
			$this->taxprofiles = J2Html::select()->clearState()
														->type('genericlist')
														->name($this->form_prefix.'[taxprofile_id]')
														->value($this->item->taxprofile_id)
														->setPlaceHolders(array(''=>JText::_('J2STORE_NOT_TAXABLE')))
														->hasOne('Taxprofiles')
														->setRelations(
																array (
																		'fields' => array (
																				'key'=>'j2store_taxprofile_id',
																				'name'=>'taxprofile_name'
																		)
																)
														)->getHtml();

		}
        $tags = new JHelperTags;
        $tags->getItemTags('com_content.article', $this->item->product_source_id);
        $tag_options = array();
        $tag_options[''] = JText::_('J2STORE_SELECT_TAG');
        if(count($tags->itemTags) > 0){
            foreach($tags->itemTags as $product_tag) {
                $tag_options[$product_tag->alias] =  JText::_($product_tag->title);
            }
        }

        $this->tag_lists = J2Html::select()->clearState()
            ->type('genericlist')
            ->name($this->form_prefix.'[main_tag]')
            ->attribs(array())
            ->value($this->item->main_tag)
            ->setPlaceHolders($tag_options)
            ->getHtml();
        $productfilter_model = F0FModel::getTmpInstance('ProductFilters', 'J2StoreModel');
        $productfilter_model->setState('limit',10);
        $this->filter_limit = 10;
		if($this->item->j2store_product_id > 0) {
            $productfilter_model->setState('product_id',$this->item->j2store_product_id);
            $productfilter_list = $productfilter_model->getList();
            $product_filters = array();
            foreach($productfilter_list as $row) {
                if(!isset($product_filters[$row->group_id])){
                    $product_filters[$row->group_id] = array();
                }
                $product_filters[$row->group_id]['group_name'] = $row->group_name;
                $product_filters[$row->group_id]['filters'][] = $row;
            }
			$this->product_filters = $product_filters;//F0FTable::getAnInstance('ProductFilter', 'J2StoreTable')->getFiltersByProduct($this->item->j2store_product_id);
		}else {
			$this->product_filters = array();
		}
        $this->item->productfilter_pagination = $productfilter_model->getPagination();
        $this->product_option_list =  $this->getProductOptionList($this->item->product_type);
		return true;
	}

	public function getProductOptionList($product_type){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('j2store_option_id, option_unique_name, option_name');
        $query->from('#__j2store_options');
        //based on the product type
        if(isset($product_type) && in_array($product_type,array('variable','flexivariable'))){
            $query->where("type IN ('select' , 'radio' ,'checkbox')");
        }
        $query->where('enabled=1');
        $db->setQuery($query);
        return $db->loadObjectList();
    }
}
