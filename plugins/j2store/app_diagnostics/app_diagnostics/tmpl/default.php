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
    <h4><?php echo JText::_('PLG_J2STORE_TOOL_DIAGONISTICS_INFORMATION') ?></h4>
    <div class="alert alert-info alert-block">
        <?php echo JText::_('J2STORE_DIAGNOSTICS_HELP_TEXT'); ?>
    </div>
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>
                <?php echo JText::_('PLG_J2STORE_SETTINGS'); ?>
            </th>
            <th>
                <?php echo JText::_('PLG_J2STORE_SETTINGS_VALUE'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_SERVER'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['server']; ?><?php echo php_uname(); ?>
            </td>
        </tr>

        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_PHP_VERSION'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['phpversion']; ?>
            </td>
        </tr>

        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_JOOMLA_VERSION'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['version']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_J2STORE_VERSION'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['j2store_version']; ?><?php echo ($vars->info['is_pro'] == 1) ? 'Professional' : 'Core'; ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_MEMORY_LIMIT'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['memory_limit']; ?>
                <?php if (intval($vars->info['memory_limit']) < 64): ?>
                    <div class="alert alert-danger">
                        <?php echo JText::_('PLG_J2STORE_MINIMUM_MEMORY_LIMIT_WARNING'); ?>
                        <a target="_blank"
                           href="http://magazine.joomla.org/issues/issue-dec-2010/item/295-Are-you-getting-your-fair-share-of-PHP-memory">
                            <strong>Refer this article for more information</strong>
                        </a>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_CURL_ENABLED'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['curl']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_JSON_ENABLED'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['json']; ?>
            </td>
        </tr>
        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_ERROR_REPORTING'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['error_reporting']; ?>
            </td>
        </tr>

        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_CACHING_ENABLED'); ?></strong>
            </td>
            <td>
                <?php echo $vars->info['caching']; ?>
            </td>
        </tr>


        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_CACHE_PLUGIN_ENABLED'); ?></strong>
            </td>
            <td>
                <?php if ($vars->info['plg_cache_enabled']): ?>
                    <?php echo JText::_('J2STORE_ENABLED') ?>
                    <div class="alert alert-danger">
                        <?php echo JText::_('PLG_J2STORE_SYSTEM_CACHE_WARNING'); ?>
                    </div>
                <?php else: ?>
                    <?php echo JText::_('J2STORE_DISABLED'); ?>
                <?php endif; ?>

            </td>
        </tr>

        <tr>
            <td>
                <strong><?php echo JText::_('PLG_J2STORE_DIAGNOSTICS_CLEAR_CART_CRON'); ?></strong>
            </td>
            <td>
                <?php
                $cron_key = J2Store::config()->get('queue_key', '');
                echo trim(JUri::root(), '/') . '/' . 'index.php?option=com_j2store&view=crons&task=cron&cron_secret=' . $cron_key . '&command=clear_cart&clear_time=1440' ?>
            </td>
        </tr>

        </tbody>
    </table>
</form>


