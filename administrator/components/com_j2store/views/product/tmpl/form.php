<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/input.php');
$platform = J2Store::platform();
$platform->addStyle('j2store-font-awesome-css','/media/j2store/css/font-awesome.min.css');
$platform->addStyle('j2store-admin-css','/media/j2store/css/j2store_admin.css');
//$doc = JFactory::getDocument();
//$doc->addStyleSheet(JURI::root(true).'/media/j2store/css/font-awesome.min.css');
//$doc->addStyleSheet(JURI::root(true).'/media/j2store/css/j2store_admin.css');
$app = JFactory::getApplication();
$option = $app->input->getString('option');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row_class = 'row';
$col_class = 'col-md-';
$product_type_class = 'badge bg-success';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.JText::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $product_type_class = 'label label-success';
    $alert_html = '<div class="alert alert-error alert-danger">'.JText::_('J2STORE_INVALID_INPUT_FIELD').'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
    $platform->addInlineScript("jQuery(function($) {
	SqueezeBox.initialize({});
	SqueezeBox.assign($('a.modal').get(), {
		parse: 'rel'
	});
});");
}
?>
<script  type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if(pressbutton == 'article.cancel') {
            document.adminForm.task.value = pressbutton;
            form.submit();
        }else if(pressbutton == 'article.apply') {
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;
                if(document.getElementById('submit_button') != null) {
                    document.getElementById('submit_button').onclick = function () {
                        this.disabled = true;
                    }
                }
                form.submit();
            }
            else {
                let msg = [];
                msg.push('<?php echo $alert_html; ?>');
                document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
            }
        }else{
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;
                form.submit();
            }
            else {
                let msg = [];
                msg.push('<?php echo $alert_html; ?>');
                document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
            }
        }
    }
</script>


<div class="j2store">
    <div class="j2store-product-edit-form">

        <div class="<?php echo $row_class;?>">
            <div class="<?php echo $col_class;?><?php echo ($this->item->j2store_product_id) ?'4':'12'; ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo JText::_('J2STORE_PRODUCT_INFORMATION'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="control-group form-group form-inline" id="j2store-product-enable">
                            <?php echo J2Html::label(JText::_('J2STORE_TREAT_AS_PRODUCT'), 'enabled',array('class'=>'control-label'));?>
                            <?php echo J2Html::radio($this->form_prefix.'[enabled]', $this->item->enabled, array('id'=>'j2store-product-enabled-radio-group', 'class'=>'radio'));?>
                        </div>
                        <div class="control-group form-group" id="j2store-product-type">
                            <?php if(!empty($this->item->product_type)): ?>
                                <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_TYPE'), 'product_type',array('class'=>'control-label')); ?>
                                <span class="<?php echo $product_type_class;?>"><?php echo JText::_('J2STORE_PRODUCT_TYPE_'.strtoupper($this->item->product_type)) ?></span></label>
                                <?php echo J2Html::hidden($this->form_prefix.'[product_type]', $this->item->product_type); ?>
                            <?php else: ?>
                                <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_TYPE'), 'product_type',array('class'=>'control-label')); ?>
                                <div class="controls"><?php echo $this->product_types; ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if(!$this->item->enabled): ?>
                            <!-- Show this only when this was not a product -->
                            <?php if($option == 'com_content' && J2Store::platform()->isClient('administrator')): ?>
                                <div class="control-group form-group">
                                    <input type="button" id="submit_button" onclick="Joomla.submitbutton('article.apply')" class="btn btn-large btn-success" value="<?php echo JText::_('J2STORE_SAVE_AND_CONTINUE'); ?>" />
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
                            <div class="j2store-confirm-cont">
                                <a data-fancybox data-src="#j2storeConfirmChange" type="button" class="btn btn-warning" ><?php echo  JText::_('J2STORE_CHANGE_PRODUCT_TYPE');?></a>
                                <!-- here load the confim modal -->
                                <?php echo $this->loadTemplate('confirm_change'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
                <div class="<?php echo $col_class;?>7">
                    <div class="panel panel-solid-success">
                        <div class="panel-body">
                            <p class="lead">
                                <?php echo JText::_('J2STORE_PRODUCT_ID'); ?> : <strong><?php echo $this->item->j2store_product_id; ?></strong>
                            </p>
                            <h3><?php echo JText::_('J2STORE_PLUGIN_SHORTCODE')?></h3>
                            <p class="shortcode">
                                {j2store}<?php echo $this->item->j2store_product_id; ?>|cart{/j2store}
                            </p>
                            <small>
                                <?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_HELP_TEXT');?>
                            </small>
                            <br />

                            <span class="pull-right">
					<button type="button" class="btn btn-small btn-warning"
                            href="javascript:void(0);"
                            onclick="jQuery('#hide-icon-<?php echo $this->item->j2store_product_id;?>').toggle('click');jQuery('#show-icon-<?php echo $this->item->j2store_product_id;?>').toggle('click');jQuery('.additional-short-code').toggle('click');jQuery('.panel-solid-success .panel-footer').toggle('click');">
						<?php echo JText::_('J2STORE_EXPAND_CLOSE'); ?>
						<i id="show-icon-<?php echo $this->item->j2store_product_id;?>"
                           class="icon icon-plus"></i> <i
                                id="hide-icon-<?php echo $this->item->j2store_product_id;?>"
                                class="icon icon-minus" style="display: none;"></i>
					</button>
				</span>
                            <br/>


                            <div class="additional-short-code" style="display: none;">
                                <h4><?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_ADDITIONAL')?></h4>
                                <p>
                                    <?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_HELP_TEXT_ADDITIONAL');?> <strong style="color:black;"> {j2store}<?php echo $this->item->j2store_product_id; ?>|upsells|crosssells{/j2store}</strong>
                                </p>
                                <p class="shortcode">price|thumbnail|mainimage|mainadditional|upsells|crosssells</p>
                            </div>

                        </div>
                        <div class="panel-footer" style="display: none;">
                            <strong>
                                <?php echo JText::_('J2STORE_PLUGIN_SHORTCODE_FOOTER_WARNING');?>
                            </strong>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
        <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_product_id]'?>" value="<?php echo $this->item->j2store_product_id; ?>" />

        <!-- @TODO should fix with css -->
        <hr>
        <?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php echo $this->loadTemplate($this->item->product_type); ?>
                    <input type="hidden" name="<?php echo $this->form_prefix.'[product_type]'?>" value="<?php echo $this->item->product_type; ?>" />
                </div>
            </div>
        <?php endif; ?>
    </div> <!--  end of J2Store Product Form -->
</div>
<?php if($this->item->j2store_product_id && $this->item->enabled && $this->item->product_type): ?>
    <script type="text/javascript">

        (function($) {
            $(document).on("click","#j2storeConfirmChange #changeTypeBtn", function(e) {
                $.ajax({
                    url :'index.php?option=com_j2store&view=products&task=changeProductType',
                    type: 'post',
                    data:{'product_id' :<?php echo $this->item->j2store_product_id; ?> ,'product_type' : '<?php echo $this->item->product_type; ?>' },
                    dataType: 'json',
                    beforeSend:function(){
                        $('#changeTypeBtn').html('<i class="icon-spin icon-refresh glyphicon glyphicon-refresh glyphicon-spin"></i> Changing type...');
                    },
                    success: function(json) {
                        if(json['success']){
                            location.reload();
                        }
                    }
                });

            });
            $(document).on("click","#j2storeConfirmChange #closeTypeBtn", function(e) {
                $.fancybox.close();
            });
        })(jQuery);
    </script>
<?php endif;?>
<script type="text/javascript">
    (function($) {
        $(document).ready(function() {
            $("div.j2store-tab-menu>div.list-group>a").click(function(e) {
                e.preventDefault();
                $(this).siblings('a.active').removeClass("active");
                $(this).addClass("active");
                var index = $(this).index();
                $("div.j2store-tab>div.j2store-tab-content").removeClass("active");
                $("div.j2store-tab>div.j2store-tab-content").eq(index).addClass("active");
            });
        });
    })(jQuery);

    (function($) {
        $('#j2store-product-enable').bind('change', function() {
            var enabled = $('#j2store-product-enable input[type=radio]:checked').val();
            if(enabled == 1) {
                $("#j2store-product-type").show();
            }else {
                $("#j2store-product-type").hide();
            }
        });

        $('#j2store-product-enable').trigger('change');

    })(jQuery);


</script>