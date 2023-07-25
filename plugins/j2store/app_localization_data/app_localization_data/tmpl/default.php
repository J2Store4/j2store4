<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;
?>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
    <?php echo J2Html::hidden('option', 'com_j2store'); ?>
    <?php echo J2Html::hidden('view', 'apps'); ?>
    <?php echo J2Html::hidden('task', 'view', array('id' => 'task')); ?>
    <?php echo J2Html::hidden('appTask', '', array('id' => 'appTask')); ?>
    <?php echo J2Html::hidden('table', '', array('id' => 'table')); ?>
    <?php echo J2Html::hidden('id', $vars->id, array('id' => 'table')); ?>
    <?php echo JHTML::_('form.token'); ?>

    <div class="j2store-tool-localization-data">
        <div class="row-fluid">

            <div class="span2">
                <h4><?php echo JText::_('J2STORE_COUNTRIES') ?></h4>
                <div class="tool-child tool-country">
                    <div id="toolbar-icon icon-download" class="btn-wrapper">
                        <button class="btn btn-small" onclick="myToolFunction('countries');">
                            <span class="icon-icon icon-download"></span><?php echo JText::_('J2STORE_INSTALL'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="span2">
                <h5><?php echo JText::_('J2STORE_ZONES'); ?></h5>
                <div class="tool-child tool-zone">

                    <div id="toolbar-icon icon-download" class="btn-wrapper">
                        <button class="btn btn-small" onclick="myToolFunction('zones');">
                            <span class="icon-icon icon-download"></span><?php echo JText::_('J2STORE_INSTALL'); ?>
                        </button>
                    </div>

                </div>

            </div>
            <div class="span2">
                <h5><?php echo JText::_('J2STORE_METRICS'); ?></h5>
                <div class="tool-child tool-metrics">

                    <div id="toolbar-icon icon-download" class="btn-wrapper">
                        <button class="btn btn-small" onclick="myToolFunction('metrics');">
                            <span class="icon-icon icon-download"></span><?php echo JText::_('J2STORE_INSTALL'); ?>
                        </button>
                    </div>

                </div>
            </div>
            <div class="span6">
                <h4><?php echo JText::_('J2STORE_NOTE'); ?></h4>
                <div class="alert alert-warning">
                    <p><?php echo JText::_('J2STORE_APP_LOCALIZATION_DATA_HELP_TEXT'); ?></p>
                </div>
            </div>
        </div>
</form>

<script type="text/javascript">

    function myToolFunction(table) {
        (function ($) {
            $("#tool-btn-" + table).attr('disabled', true);
            var txt;
            var r = confirm("<?php echo JText::_('J2STORE_TABLE_WILL_BE_RESET')?>");
            if (r == true) {
                $("#appTask").attr('value', 'insertTableValues');
                $("#table").attr('value', table);
            } else {
                return;
            }
        })(j2store.jQuery);
    }
</script>

