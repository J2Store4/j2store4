<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$document =JFactory::getDocument();
$platform->addScript('j2store-fancybox-script','/media/j2store/js/jquery.fancybox.min.js');
$platform->addStyle('j2store-fancybox-css','/media/j2store/css/jquery.fancybox.min.css');
//$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery.fancybox.min.css' );
require_once JPATH_ADMINISTRATOR."/components/com_j2store/library/popup.php";
class JFormFieldCouponproductadd extends  JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Couponproductadd';

	protected function getInput(){
		$html ='';
		$fieldId = isset($this->element['id']) ? $this->element['id'] : 'jform_product_list';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration(
			"function jSelectProduct(product_id ,product_name ,field_id){
				var form = jQuery(\"#module-form\");
				var html ='';
				if(form.find('#'+field_id+ '  #product-row-'+product_id).length == 0){
					html +='<tr id=\"product-row-'+product_id +'\"><td><input type=\"hidden\" name=\"".$this->name."[]\" value='+product_id+' />'+product_name +'</td><td><button class=\"btn btn-danger\" onclick=\"jQuery(this).closest(\'tr\').remove();\"><i class=\"icon icon-trash\"></button></td></tr>';
					form.find(\"#\"+field_id).append(html);
					alert('Product added');
				}else{
					alert('Product already exists');
				}
			}"
		);

		$popupurl = "index.php?option=com_j2store&view=products&task=setCouponProducts&layout=couponproducts&tmpl=component&function=jSelectProduct&field=".$fieldId;
		$html = J2StorePopup::popup($popupurl, JText::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400 ,'class'=>'btn btn-success'));
		$html .= "<table class=\"table table-striped table-condensed\" id=\"jform_product_list\">";
		$html .= "	<tbody>";
		if(!empty($this->value)){
			$html .= "<tr>
			            	<td colspan=\"3\">
			            		<a class=\"btn btn-danger\" href=\"javascript:void(0);\"
			            		     onclick=\"jQuery('.j2store-product-list-tr').remove();\">
			            		       <?php echo JText::_('J2STORE_DELETE_ALL_PRODUCTS');?>
			            		       <i class=\"icon icon-trash\"></i></a>
	                		</td>
				  	</tr>";
			$i =1;
			if(is_string ( $this->value )){
				$this->value = explode ( ',', $this->value );
			}

			foreach($this->value as  $pid){
				$product = F0FModel::getTmpInstance('Products','J2StoreModel')->getItem($pid);
				if($product->j2store_product_id){
					$html .= "<tr class=\"j2store-product-list-tr\" id=\"product-row-$pid\">
						<td><input type=\"hidden\" name=\"$this->name[]\" value='$pid' />$product->product_name</td>
						<td><a class=\"btn btn-danger\" href=\"javascript:void(0);\" onclick=\"jQuery(this).closest('tr').remove();\"><i class=\"icon icon-trash\"></i></a></td>
						</tr>";
				}
				$i++;
			}

		}
		$html .= "	</tbody>";
		$html .= "</table>";
		$html .= "<script>
					(function($) {
						$(\"#jform_product_list\").bind(\"DOMSubtreeModified\", function() {
    						$(\"#jform_product_list input\").each(function(i) {
  								$(this).attr('name', \"$this->name[]\");
							});
						});
  
					})(jQuery);

					</script>";
		return $html ;
	}

}
