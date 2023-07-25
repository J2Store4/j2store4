<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class JFormFieldCouponproducts extends  JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Couponproducts';

	protected function getInput(){
		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';
		$html =J2StorePopup::popup("index.php?option=com_j2store&view=coupons&task=setProducts&layout=products&tmpl=component&function=jSelectProduct&field=".$fieldId, JText::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400 ,'class'=>'btn btn-success'));
		return $html ;
	}

	/* protected function getInput(){

		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';
		$products = F0FModel::getTmpInstance('Products' ,'J2StoreModel')->enabled(1)->getList();
		$productarray =array();
		$value =array();
		if(isset($this->value) && $this->value){
			$value = explode(',' ,$this->value);
		}
		foreach($products as $product){
			$product = J2Product::getInstance()
				->setId($product->j2store_product_id)
				->getProduct();
			 if(in_array($product->j2store_product_id ,$value)){
				$productarray[$product->j2store_product_id] =$product->product_name;
			 }
		}
		$html .=JHTML::_('behavior.modal', 'a.modal');
		$html .= J2Html::select()->clearState()
				->type('genericlist')
				->idTag($fieldId)
				->attribs(array('multiple'=>true))
				->name('products[]')
				->setPlaceholders($productarray)
				->value($value)
				->getHtml();
		$html .=J2StorePopup::popup("index.php?option=com_j2store&view=coupons&task=setProducts&layout=products&tmpl=component&function=jSelectProduct&field=".$fieldId, JText::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400));
		return $html ;
	} */

}
