<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');
class JFormFieldJ2storeitem extends  JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'J2storeitem';

	protected function getInput(){

		//print_r($this);
        $platform = J2Store::platform();
		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';

		$products = F0FModel::getTmpInstance('Products' ,'J2StoreModel')->enabled(1)->getList();
		$productarray =array();
		$value =array();
		$link = 'index.php?option=com_j2store&amp;view=products&amp;task=setProducts&amp;tmpl=component&amp;object='.$this->name;
		$selected_value =array();
		if(isset($this->value) && !empty($this->value)){
			$selected_value = (isset($this->value['ids']) && !empty($this->value['ids'])) ? $this->value['ids'] : array();
		}

		if(is_array($selected_value) && !empty($selected_value)){
			foreach($products as $product){
				$product = J2Product::getInstance()
					->setId($product->j2store_product_id)
					->getProduct();
				 if(in_array($product->j2store_product_id ,$selected_value)){
					$productarray[$product->j2store_product_id] =$product->product_name;
				 }
			}
		}

		$js = "
		function jSelectItem(id, title, object) {
		var exists = jQuery('#j2store-product-li-'+id).html();
			if(!exists){
				var container = jQuery('<li/>' ,{'id' :'j2store-product-li-'+id,'class':'j2store-product-list-menu'}).appendTo(jQuery('#j2store-product-item-list'));
				var span = jQuery('<label/>',{'class':'label label-info'}).html(title).appendTo(container);
				var input =jQuery('<input/>',{value:id, type:'hidden', name:'jform[request][j2store_item][ids][]'}).appendTo(container);
				var remove = jQuery('<a/>',{'class':'btn btn-danger btn-mini' ,'onclick':'jQuery(this).closest(\"li\").remove();' }).html('<i class=\"icon icon-remove\"></i>').appendTo(container);
				}else{
					alert('". JText::_('J2STORE_PRODUCT_ALREADY_EXISTS')."');
				}
		if(typeof(window.parent.SqueezeBox.close=='function')){
			window.parent.SqueezeBox.close();
		}
		else {
			document.getElementById('sbox-window').close();
			}
		}
		function removeProductList(id){
			jQuery('#j2store-product-li-'+id).remove();
		}
		";
		$css ='#j2store-product-item-list{
					list-style:none;
					margin:5px;
				}'
				;

        $platform->addInlineScript($js);
        $platform->addStyle($js);
//		JFactory::getDocument()->addScriptDeclaration($js);
//		JFactory::getDocument()->addStyleDeclaration($css);
		$html .=JHTML::_('behavior.modal', 'a.modal');
		$html .='<div id="'.$fieldId.'">';
		$html .='<label class="control-label"></label>';
		$html .= '<span class="input-append">';
		$html .='<input type="text" id="'.$this->name.'" name=""  disabled="disabled"/>';
		$html .='<a class="modal btn btn-primary" title="'.JText::_('J2STORE_SELECT_AN_ITEM').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 700, y: 450}}"><i class="icon-list"></i>  '.JText::_('J2STORE_SELECT_PRODUCT').'</a>';
		$html .=J2StorePopup::popup("index.php?option=com_j2store&view=coupons&task=setProducts&layout=products&tmpl=component&function=jSelectProduct&field=".$fieldId, JText::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400));
		$html .'</span>';
		$html .='<ul id="j2store-product-item-list" >';
		foreach($productarray as $key => $value){
			$html .='<li class="j2store-product-list-menu" id="j2store-product-li-'.$key.'">';
			$html .='<label class="label label-info">';
			$html .=$value;
			$html .='<input type="hidden" value="'.$key.'" name="jform[request][j2store_item][ids][]">';
			$html .='</label>';
			$html .='<a class="btn btn-danger btn-mini" onclick="removeProductList('.$key.');">';
			$html .='<i class="icon icon-remove"></i>';
			$html .='</a>';
			$html .='</li>';
		}
		$html .='</ul>';

		$html .='</div>';
		return $html ;
	}

}
