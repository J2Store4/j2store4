<?php
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.formvalidator');

$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('formbehavior.chosen', '.chosenselect');
$row_class = 'row';
$col_class = 'col-md-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.JText::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $alert_html = '<div class="alert alert-error alert-danger">'.JText::_('J2STORE_INVALID_INPUT_FIELD').'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
}


?>
<script type="text/javascript">
    //Joomla.submitbutton = function(pressbutton) {
    //    var form = document.adminForm;
    //    if (pressbutton === 'cancel') {
    //        document.adminForm.task.value = pressbutton;
    //        form.submit();
    //    }else{
    //        if (document.formvalidator.isValid(form)) {
    //            document.adminForm.task.value = pressbutton;
    //            form.submit();
    //        } else {
    //            let msg = [];
    //            msg.push('<?php //echo $alert_html; ?>//');
    //            document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
    //        }
    //    }
    //}
    /**
     * when product title of the
     * Method
     */
    function jSelectProduct(product_id ,product_name ,field_id){
        var form = jQuery("#adminForm");
        var html ='';
        if(form.find('#'+field_id+ '  #product-row-'+product_id).length == 0){
            html +='<tr id="product-row-'+product_id +'"><td><input type="hidden" name="products['+product_id+']" value='+product_id+' />'+product_name +'</td><td><button class="btn btn-danger" onclick="jQuery(this).closest(\'tr\').remove();"><i class="icon icon-trash"></button></td></tr>';
            form.find("#"+field_id).append(html);
            alert('<?php echo JText::_('J2STORE_PRODUCT_ADDED');?>');
        }else{
            alert('<?php echo JText::_('J2STORE_PRODUCT_ADDED_ALREADY');?>');
        }
    }


</script>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">
        <div class="j2store_<?php echo $vars->view;?>_edit">
            <form id="adminForm" class="form-horizontal form-validate" action="<?php echo $vars->action_url?>" method="post" name="adminForm">
                <?php echo J2Html::hidden('option','com_j2store');?>
                <?php echo J2Html::hidden('view',$vars->view);?>
                <?php if(isset($vars->primary_key) && !empty($vars->primary_key)): ?>
                    <?php echo J2Html::hidden($vars->primary_key,$vars->id);?>
                <?php endif; ?>
                <?php echo J2Html::hidden('task', '', array('id'=>'task'));?>
                <?php echo JHTML::_( 'form.token' ); ?>
                <div class="<?php echo $row_class;?>">
                    <?php if(isset($vars->field_sets) && !empty($vars->field_sets)):?>
                        <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic_options')); ?>
                        <?php else: ?>
                            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic_options', 'recall' => true, 'breakpoint' => 768]); ?>
                        <?php endif; ?>
                        <?php foreach ($vars->field_sets as $field_set):?>
                            <?php if(isset($field_set['fields']) && !empty($field_set['fields'])):?>
                                <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                                    <?php echo JHtml::_('bootstrap.addTab', 'myTab', $field_set['id'], JText::_($field_set['label'])); ?>
                                <?php else: ?>
                                    <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'myTab', $field_set['id'], JText::_($field_set['label'])); ?>
                                <?php endif; ?>
                                <div <?php echo isset($field_set['id']) && $field_set['id'] ? 'id="'.$field_set['id'].'"': '';?>
                                    <?php echo isset($field_set['class']) && is_array($field_set['class']) ? 'class="'.implode(' ',$field_set['class']).'"': '';?>
                                >
                                    <?php /*if(isset($field_set['label']) && !empty($field_set['label'])):*/?><!--
                                        <h3><?php /*echo JText::_($field_set['label']);*/?></h3>
                                    --><?php /*endif; */?>
                                    <?php if(isset($field_set['is_pro']) && $field_set['is_pro'] && J2Store::isPro() != 1):?>
                                        <?php echo J2Html::pro();?>
                                    <?php else: ?>
                                        <?php foreach ($field_set['fields'] as $field_name => $field):?>
                                            <?php $is_required = isset($field['options']['required']) && !empty($field['options']['required']) ? true:false;?>
                                            <div class="control-group">
                                                <label class="control-label">
                                                    <?php if(isset($field['label']) && !empty($field['label'])):?>
                                                        <?php echo JText::_($field['label']);?><?php echo $is_required ? "<span>*</span>": '';?>
                                                    <?php endif; ?>
                                                </label>
                                                <div class="controls">
                                                    <?php if(isset($field['type']) && in_array($field['type'],array('number','text','email','password','textarea','file','radio','checkbox','button','submit','hidden'))):?>
                                                        <?php echo J2Html::input($field['type'],$field['name'],$field['value'],$field['options']);?>
                                                    <?php else:?>
                                                        <?php echo J2Html::custom($field['type'],$field['name'],$field['value'],$field['options']);?>
                                                    <?php endif; ?>
                                                    <?php if(isset($field['desc']) && !empty($field['desc'])):?>
                                                        <br/>
                                                        <small><?php echo JText::_($field['desc']);?></small>
                                                    <?php endif; ?>
                                                    <?php if($field_name == 'product_links'):?>
                                                        <br/>
                                                        <div class="<?php echo $row_class ?>">
                                                            <div class="<?php echo $col_class ?>6">
                                                                <div class="table-responsive">
                                                                    <table class="table table-striped table-condensed" id="jform_product_list">
                                                                        <tbody>
                                                                        <?php if(!empty($vars->item->products)):?>
                                                                        <tr>
                                                                            <td colspan="3">
                                                                                <a class="btn btn-danger" href="javascript:void(0);"
                                                                                   onclick="jQuery('.j2store-product-list-tr').remove();">
                                                                                    <?php echo JText::_('J2STORE_DELETE_ALL_PRODUCTS');?>
                                                                                    <i class="icon icon-trash"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        <?php $product_ids = explode(',',$vars->item->products);
                                                                        $i =1;
                                                                        ?>
                                                                        <?php foreach($product_ids as  $pid):?>
                                                                            <?php $product = F0FModel::getTmpInstance('Products','J2StoreModel')->getItem($pid);?>
                                                                            <tr class="j2store-product-list-tr" id="product-row-<?php echo $pid?>">
                                                                                <td><input type="hidden" name="products[<?php echo $pid;?>]" value='<?php echo $pid;?>' /><?php echo $product->product_name;?></td>
                                                                                <td><a class="btn btn-danger" href="javascript:void(0);" onclick="jQuery(this).closest('tr').remove();"><i class="icon icon-trash"></i></a></td>
                                                                            </tr>
                                                                            <?php
                                                                            $i++;
                                                                        endforeach;?>
                                                                        </tbody>
                                                                        <?php endif;?>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="<?php echo $col_class ?>6">
                                                                <div class="alert alert-success">
                                                                    <?php echo JText::_('J2STORE_COUPON_ADDING_PRODUCT_HELP');?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif;?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                                    <?php echo JHtml::_('bootstrap.endTab'); ?>
                                <?php else: ?>
                                    <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach;?>
                        <?php if (version_compare(JVERSION, '3.99.99', 'lt')):?>
                            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
                        <?php else: ?>
                            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTabSet'); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    //assign default value type
    var value_type = '<?php echo $vars->item->value_type?>';
    /** when coupon value type changed will toggle  **/
    jQuery('select[name=value_type]').on('change',function(){
        value_type = jQuery('select[name=value_type] option:selected').val();
        if(jQuery('#max_quantity').length > 0){
            jQuery('#max_quantity').closest('.control-group').hide();
            if(value_type == 'percentage_product' || value_type == 'fixed_product' ){
                jQuery('#max_quantity').closest('.control-group').show();
            }
        }
    });
    jQuery('select[name=value_type]').trigger('change');
</script>