<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$base_path = rtrim(JUri::root(),'/').'/administrator';
?>

<div class="j2store-product-configuration-options">
	<div class="row-fluid">
		<div class="span10">
		<div class="form-group">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_OPTIONS'), 'option_name'); ?>
			<table id="attribute_options_table" class="adminlist table table-striped table-bordered j2store">
				<thead>
					<tr>
					<th colspan="2"><?php echo JText::_('J2STORE_OPTION_NAME');?></th>
					<th colspan="2"><?php echo JText::_('J2STORE_PARENT_OPTION');?></th>
					<th><?php echo JText::_('J2STORE_OPTION_REQUIRED');?></th>					
					<th><?php echo JText::_('J2STORE_OPTION_ORDERING');?></th>
					<th><?php echo JText::_('J2STORE_OPTION_REMOVE');?></th>
					</tr>
			</thead>
			<tbody>
				<?php if(isset($this->item->product_options ) && !empty($this->item->product_options)):
					$key = 0;
				?>
				<?php foreach($this->item->product_options as  $poption ):

				?>
				<tr id="pao_current_option_<?php echo $poption->j2store_productoption_id;?>">
						<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][j2store_productoption_id]', $poption->j2store_productoption_id);?>
						<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][option_id]', $poption->option_id);?>
					<td>
						<?php echo $this->escape($poption->option_name);?>
						<br/>
						<small>(<?php  echo $this->escape($poption->option_unique_name);?>)</small>
						<small><?php JText::_('J2STORE_OPTION_TYPE');?><?php echo JText::_('J2STORE_'.strtoupper($poption->type))?></small>
					</td>
					<td>
						<?php if(isset($poption->type) && ($poption->type =='select' || $poption->type =='radio' || $poption->type =='checkbox')):?>
						<?php
                            echo J2StorePopup::popup($base_path."/index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$this->item->j2store_product_id."&productoption_id=".$poption->j2store_productoption_id."&layout=productoptionvalues&tmpl=component", JText::_( "J2STORE_OPTION_SET_VALUES" ), array());?>
						<?php endif;?>

					</td>

					<td>
						<?php
						$parent_options  = J2StoreHelperSelect::getParentOption($poption->j2store_productoption_id,$poption->parent_id,$poption->option_id);
						echo J2Html::select()->clearState()
						->type('genericlist')
						->name($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][parent_id]')
						->value($poption->parent_id)
						->setPlaceHolders($parent_options)
						->attribs(array('class'=>'input-small'))
						->getHtml();

						?>
						</td>

						<td>
						<?php if(isset($poption->type) && !in_array($poption->type ,array('select','radio','checkbox')) ):?>
							<?php if(isset($poption->parent_id) && !empty($poption->parent_id)):?>
								<?php
                                echo J2StorePopup::popup($base_path."/index.php?option=com_j2store&view=products&task=setparentoptionvalues&productoption_id=".$poption->j2store_productoption_id."&layout=parentproductopvalues&tmpl=component", JText::_( "J2STORE_OPTION_PARENT_OPTION_VALUES" ), array());?>
							<?php endif;?>
							<?php endif;?>
						</td>

					<td>
					<?php echo J2Html::select()->clearState()
											   ->type('genericlist')
												->name($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][required]')
												->value($poption->required)
												->setPlaceHolders(array('0' => JText::_('J2STORE_NO') ,'1' => JText::_('J2STORE_YES')))
												->attribs(array('class'=>'input-small'))
												->getHtml();
					?>

					</td>
					<td><?php echo J2Html::text($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][ordering]',$poption->ordering,array('id'=>'ordering' ,'class'=>'input-small'));?></td>
					<td>
						<span class="optionRemove" onClick="removePAOption(<?php echo $poption->j2store_productoption_id;?>,'<?php echo $this->item->product_type;?>')">X</span>
					</td>
				</tr>
				<?php $key++;?>
				<?php endforeach;?>
				<?php endif;?>
				<tr class="j2store_a_options">
					<td colspan="7">
						<label class="attribute_option_label">
						</label>
                        <select name="option_select_id" id="option_select_id">
                            <?php foreach ($this->product_option_list as $option_list):?>
                                <option value="<?php echo $option_list->j2store_option_id?>"><?php echo $this->escape($option_list->option_name) .' ('.$this->escape($option_list->option_unique_name).')';?></option>
                            <?php endforeach; ?>
                        </select>
                        <a onclick="addOption()" class="btn btn-success"> <?php echo JText::_('J2STORE_ADD_OPTIONS')?></a>
					</td>
				</tr>
				</tbody>
					<tfoot>
						<tr>
							<td colspan="7">
								<?php echo J2StorePopup::popup($base_path."/index.php?option=com_j2store&view=products&task=setpaimport&product_type=".$this->item->product_type."&product_id=".$this->item->j2store_product_id."&layout=paimport&tmpl=component", JText::_('J2STORE_IMPORT_PRODUCT_OPTIONS'), array('class'=>'btn btn-success','width'=>800 , 'height'=>500));?>
							</td>
						</tr>
					</tfoot>
			</table>
		</div>
		</div>
		<div class="span2"></div>
		</div>
</div>
<script type="text/javascript">
function addOption() {
    (function ($) {
        var option_value = $('#option_select_id').val();
        var option_name = $('#option_select_id option[value='+option_value+']').html();

        var html='';
        html+='<span class="j2error"><?php echo JText::_('J2STORE_PARENT_OPTION_MESSAGE');?></span>';

        $('<tr><td class=\"addedOption\">' + option_name+ '</td><td></td>'
            +'<td>'
            + html
            +'</td><td></td><td>'
            +'<select class="input-small" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ option_value +'][required]\" ><option value=\"0\"><?php echo Jtext::_('J2STORE_NO');?></option>'
            +'<option value=\"1\"><?php echo JText::_('J2STORE_YES'); ?></option></select>'
            +'</td><td><input class=\"input-small\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ option_value +'][ordering]\" value=\"0\"></td>'
            +'<td><span class=\"optionRemove\" onclick=\"j2store.jQuery(this).parent().parent().remove();\">x</span>'
            +'<input type=\"hidden\" value=\"' + option_value + '\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ option_value +'][option_id]\" /><input type=\"hidden\" value="" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ option_value +'][j2store_productoption_id]\" /> </td></tr>').insertBefore('.j2store_a_options');
        key++;
    })(j2store.jQuery);

}
</script>