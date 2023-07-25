<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

class JFormFieldOrderstatusform extends F0FFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Orderstatusform';

	public function getRepeatable()
	{

		$cont_saving =JText::_('J2STORE_SAVING_CHANGES').'...';
		$html ='';
		$html .='<div class="j2store-order-status-form">';
		$html .=JText::_("J2STORE_CHANGE_ORDER_STATUS");
		$html .='<script type="text/javascript">
				function submitOrderState(id){
					(function($) {
							var order_state = $("#order_state_id_"+id).attr("value");
					var notify_customer = 0;
					if($("#notify_customer_"+id).is(":checked")) {
						notify_customer = 1;
					}
					$.ajax({
						url: "index.php?option=com_j2store&view=orders&task=orderstatesave",
						type: "post",
						data: {"id":id,"return":"orders","notify_customer":notify_customer,"order_state_id":order_state},
						dataType: "json",
						beforeSend: function() {
							$("#order-list-save_"+id).attr("disabled", true);
							$("#order-list-save_"+id).val("$cont_saving");
						},
						success: function(json) {
							if(json["success"]){
								if(json["success"]["link"]){
									window.location =json["success"]["link"];
								}
							}
						}
					});
					})(j2store.jQuery);
			};
		</script>';
		$html .=J2Html::select()->clearState()
												->type('genericlist')
												->name('order_state_id')
												->value($this->item->order_state_id)
												->idTag("order_state_id_".$this->item->j2store_order_id)
												->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
												->hasOne('Orderstatuses')
												->setRelations(
																array (
																	'fields' => array
																		 		(
																					'key'=>'j2store_orderstatus_id',
																					'name'=>'orderstatus_name'
																				)
																		)
													)->getHtml();

		$html .= "<label><input type='checkbox' name='notify_customer' id='notify_customer_.$this->item->j2store_order_id.' value='1' />";
		$html .=JText::_('J2STORE_NOTIFY_CUSTOMER');
		$html .='</label>';
		//$html .='<input type="hidden" name="j2store_order_id" value="'.$this->item->j2store_order_id.'" />';
		$html .='<input type="hidden" name="return" value="orders" />';
		$html .='<input class="btn btn-primary btn-small" id="order-list-save_'.$this->item->j2store_order_id .' type="button" onclick="submitOrderState('.$this->item->j2store_order_id.')"	value="'.JText::_('J2STORE_ORDER_STATUS_SAVE').'" />';
		$html .='</div>';
		return $html;
	}
}
