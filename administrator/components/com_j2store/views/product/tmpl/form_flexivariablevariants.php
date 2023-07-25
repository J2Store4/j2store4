<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;
$global_config = JFactory::getConfig();
//get the config
$limit = $global_config->get('list_limit',20);
?>
<div id="variant_add_block">
    <input type="hidden" name="flexi_product_id" value="<?php echo $this->item->j2store_product_id;?>"/>
    <?php foreach ($this->item->product_options as $product_option): ?>
        <select name="varient_combin[<?php echo $product_option->j2store_productoption_id;?>]">
            <option value="0"><?php echo substr(JText::_('J2STORE_ANY').' '.$this->escape($product_option->option_name),0,10).'...';?></option>
            <?php foreach ($product_option->option_values as $option_value): ?>
                <option value="<?php echo $option_value->j2store_optionvalue_id;?>"><?php echo $this->escape($option_value->optionvalue_name);?></option>
            <?php endforeach; ?>
        </select>
    <?php endforeach; ?>
    <a onclick="addFlexiVariant()" class="btn btn-info"><?php echo JText::_('J2STORE_ADD_VARIANT');?></a>
    <a onclick="remvoeFlexiAllVariant()" class="btn btn-danger"><?php echo JText::_('J2STORE_REMOVE_ALL_VARIANT');?></a>
</div>
<div id="variant_display_block">
    <!-- Advanced variable starts here  -->
    <div class="bs-example j2store-advancedvariants-settings">
        <div class="panel-group" id="accordion">
            <?php
            /* to get ajax advanced variable list need to
             *  assign these variables
             */
            $this->variant_list = $this->item->variants;
            $this->variant_pagination =$this->item->variant_pagination;
            $this->weights = $this->item->weights;
            $this->lengths = $this->item->lengths;

            ?>
            <?php  echo $this->loadTemplate('ajax_flexivariableoptions');?>
        </div>
    </div>
</div>

<script type="text/javascript">
    var currentPage = <?php echo $this->item->variant_pagination->pagesCurrent;?>;
    var total_flexivariants =<?php echo $this->item->variant_pagination->total;?>;
    var flexi_limit  = <?php echo $limit;?>;
    var product_id = <?php echo $this->item->j2store_product_id;?>;

    (function($) {
        /**  on load will create footer list **/
        $(document).ready(function(){
            $('#accordion').after('<div id="nav" class="pagination pagination-toolbar"><ul class="pagination-list"></ul></div>');
            var numPages = total_flexivariants / flexi_limit;
            // now convert the numPages to int
            numPages = Math.ceil(numPages);
            if(numPages > 1 ){
                createFooterList(numPages);
                $('#nav .pagination-list a').bind('click', function(){
                    $('#nav .pagination-list li').removeClass('active');
                    $(this).parent('li').addClass('active');
                });
            }
        });

    })(j2store.jQuery);


    /***
     *  This method will append pagination li to parent Ul
     */
    function createFooterList(numPages){
        (function($) {
            var limitstart = 0;
            for(i = 0;i < numPages;i++) {
                var pageNum = i + 1;
                limitstart = i * flexi_limit;
                $('#nav .pagination-list').append('<li><a data-get_limitstart="'+limitstart +'" data-get_page="'+i+'"  onclick="getVariantList(this);" href="javascript:void(0);" rel="'+i+'">'+pageNum+'</a></li> ');
            }
            $('#nav .pagination-list li:first').addClass('active');
        })(j2store.jQuery);
    }

    /**
     * Method to run ajax request to get the list of variants based on the page requested
     */
    function getVariantList(element){
        (function($) {
            //var limit = $('#variant-limit').val();
            var getPage = $(element).data('get_page');
            var limitstart = $(element).data('get_limitstart');
            var ajOptions = {
                type : 'post',
                url :  'index.php',
                cache: false,
                dataType : 'json',
                data:{
                    'option':'com_j2store',
                    'view' :'products',
                    'task' :'getVariantListAjax',
                    'limitstart':limitstart,
                    'product_id' : product_id,
                    'limit' : limit,
                    'form_prefix' : '<?php echo $this->form_prefix;?>'
                }
            }
            $.ajax(ajOptions)
                .done( function(json) {
                    if(json['html']){
                        $('#accordion').html(json['html']);
                    }
                })
        })(j2store.jQuery);

    }

    function addFlexiVariant() {
        (function ($) {
            var data = $('#variant_add_block select,#variant_add_block input').serializeArray();
            console.log(data);

            var j2Ajax = $.ajax({
                url: 'index.php?option=com_j2store&view=apps&task=view&id=<?php echo $this->item->app_detail->extension_id;?>&appTask=addFlexiVariant&form_prefix=<?php echo $this->form_prefix;?>',
                type: 'post',
                data: data,
                dataType: 'json'

            });
            j2Ajax.done(function(json) {
                if(json['html']){
                    window.location.reload();
                    //$('#variant_display_block').html(json['html']);
                }

            }).fail(function( jqXHR, textStatus, errorThrown) {
                console.log(textStatus + errorThrown);
            }).always(function(jqXHR, textStatus, errorThrown) {
                    //form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-always'));
            });
        })(j2store.jQuery);
    }

    function remvoeFlexiAllVariant() {
        (function ($) {
            var delete_var_data = {
                option: 'com_j2store',
                view: 'apps',
                task: 'view',
                id: '<?php echo $this->item->app_detail->extension_id;?>',
                appTask: 'deleteAllVariant',
                product_id: '<?php echo $this->item->j2store_product_id;?>'
            };
            $.ajax({
                url: '<?php echo JRoute::_('index.php');?>',
                data: delete_var_data,
                beforeSend: function () {

                },
                success: function (json) {
                    if (json) {
                        window.location.reload();
                    }
                }
            });
        })(j2store.jQuery);
    }
</script>
