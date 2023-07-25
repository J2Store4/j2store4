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
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class ?>">
    <?php if (!empty($sidebar)): ?>
    <div id="j-sidebar-container" class="<?php echo $col_class; ?>2">
        <?php echo $sidebar; ?>
    </div>
    <div id="j-main-container" class="<?php echo $col_class; ?>10">
        <?php else : ?>
        <div class="j2store">
            <?php endif; ?>
            <form action="index.php?option=com_j2store&view=orders" method="post" name="adminForm" id="adminForm">
                <?php echo J2Html::hidden('option', 'com_j2store'); ?>
                <?php echo J2Html::hidden('view', 'orders'); ?>
                <?php echo J2Html::hidden('task', 'browse', array('id' => 'task')); ?>
                <?php echo J2Html::hidden('boxchecked', '0'); ?>
                <?php echo J2Html::hidden('filter_order', ''); ?>
                <?php echo J2Html::hidden('filter_order_Dir', ''); ?>
                <?php echo JHTML::_('form.token'); ?>
                <div class="j2store-order-filters">
                    <div class="j2store-alert-box" style="display:none;"></div>
                    <!-- general Filters -->
                    <?php echo $this->loadTemplate('filters'); ?>

                    <!-- advanced filters -->
                    <?php echo $this->loadTemplate('advancedfilters'); ?>
                </div>
                <div class="j2store-order-list">
                    <!-- Orders items -->
                    <?php echo $this->loadTemplate('items'); ?>
                </div>
            </form>
            <?php if (!empty($sidebar)): ?>
        </div>
        <?php else : ?>
    </div>
<?php endif; ?>
</div>
<script type="text/javascript">

    /**
     * method to reset only advanced filters values
     */
    function resetAdvancedFilters() {
        jQuery("#advanced-search-controls .j2store-order-filters").each(function () {
            if (jQuery(this).attr('name') == 'reset' || jQuery(this).attr('name') == 'go' || jQuery(this).attr('id') == 'hideBtnAdvancedControl' || jQuery(this).attr('id') == 'showBtnAdvancedControl' || jQuery(this).attr('name') == 'advanced_search' || jQuery(this).attr('name') == 'reset_advanced_filters') {

            } else {
                jQuery(this).val('');
            }
        });
        jQuery('#j2store_paykey').attr('value', '');
        jQuery("#adminForm").submit();
    }

    /**
     * Method to reset All filters values
     */
    jQuery("#reset-filter").on('click', function () {
        jQuery('#j2store_orderstate').attr('value', '');
        jQuery('#j2store_paykey').attr('value', '');
        jQuery(".j2store-order-filters input").each(function () {
            if (jQuery(this).attr('name') == 'reset' || jQuery(this).attr('name') == 'go' || jQuery(this).attr('id') == 'showBtnAdvancedControl' || jQuery(this).attr('name') == 'advanced_search' || jQuery(this).attr('name') == 'reset_advanced_filters') {

            } else {
                jQuery(this).val('');
            }
        });
        this.form.submit();
    })

    jQuery("#reset-filter-search").on('click', function () {
        jQuery("#search").val('');
        this.form.submit();
    });

    function submitOrderState(id, order_id) {
        (function ($) {
            //var order_state = $("#order_state_id_"+id).attr('value');
            var order_state = $("#order_state_id_" + id).val();
            var notify_customer = 0;
            if ($("#notify_customer_" + id).is(':checked')) {
                notify_customer = 1;
            }
            $.ajax({
                url: 'index.php?option=com_j2store&view=orders&task=saveOrderstatus',
                type: 'post',
                data: {
                    'id': id,
                    'order_id': order_id,
                    'return': 'orders',
                    'notify_customer': notify_customer,
                    'order_state_id': order_state
                },
                dataType: 'json',
                beforeSend: function () {
                    $('#order-list-save_' + id).attr('disabled', true);
                    $('#order-list-save_' + id).val('<?php echo JText::_('J2STORE_SAVING_CHANGES');?>...');
                },
                success: function (json) {
                    if (json['success']) {
                        if (json['success']['link']) {
                            window.location = json['success']['link'];
                        }
                    }

                    if (json['error']) {
                        $('.j2store-alert-box').show();
                        var html = '';
                        html += '<p class="alert alert-warning">' + json['error']['msg'] + '</p>';
                        $('.j2store-alert-box').html(html);

                    }
                }
            });
        })(j2store.jQuery);
    }


    function jSelectUser_jform_user_id(id, title) {


        /*(function($){

            var old_id =$("#"+id).attr('value');

                $("#"+id).attr('value',id);
                $("#"+id+'_name').attr('value',title);

            })(j2store.jQuery);*/
        var old_id = document.getElementById('jform_user_id').value;
        /*if (old_id != id) { */
        document.getElementById('jform_user_id').value = id;
        document.getElementById('jform_user_id_name').value = title;
        document.getElementById('jform_user_id').className = document.getElementById('jform_user_id').className.replace();
        //j2storeGetAddress();
        /*} */
        SqueezeBox.close();
    };

</script>