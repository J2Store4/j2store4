<?php
/**
 * --------------------------------------------------------------------------------
 * App Plugin - Flexible Variable
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2018 J2Store . All rights reserved.
 * @license     GNU/GPL V3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$platform = J2Store::platform();
$platform->loadExtra('behavior.framework');
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('dropdown.init');
$platform->loadExtra('formbehavior.chosen', 'select');

JHtml::_('script', 'media/j2store/js/j2store.js', false, false);
?>

<script type="text/javascript">
    Joomla.submitbutton = function (pressbutton) {
        if (pressbutton === 'save' || pressbutton === 'apply') {
            document.adminForm.task = 'view';
            document.getElementById('appTask').value = pressbutton;
            Joomla.submitform('view');
        }
        self::submitButton(pressbutton);
    }
</script>

<div class="j2store-configuration">

    <form action="<?php echo $vars->action; ?>" method="post" name="adminForm" id="adminForm"
          class="form-horizontal form-validate">
        <?php echo J2Html::hidden('option', 'com_j2store'); ?>
        <?php echo J2Html::hidden('view', 'apps'); ?>
        <?php echo J2Html::hidden('app_id', $vars->id); ?>
        <?php echo J2Html::hidden('appTask', '', array('id' => 'appTask')); ?>
        <?php echo J2Html::hidden('task', 'view', array('id' => 'task')); ?>

        <?php echo JHtml::_('form.token'); ?>
        <?php
        $fieldsets = $vars->form->getFieldsets();
        $shortcode = $vars->form->getValue('text');
        $tab_count = 0;

        foreach ($fieldsets as $key => $attr) {

            if ($tab_count == 0) {
                echo JHtml::_('bootstrap.startTabSet', 'configuration', array('active' => $attr->name));
            }
            echo JHtml::_('bootstrap.addTab', 'configuration', $attr->name, JText::_($attr->label, true));
            ?>
            <?php if (J2Store::isPro() != 1 && isset($attr->ispro) && $attr->ispro == 1) : ?>
                <?php echo J2Html::pro(); ?>
            <?php else: ?>

                <div class="row-fluid">
                    <div class="span12">
                        <?php
                        $layout = '';
                        $style = '';
                        $fields = $vars->form->getFieldset($attr->name);
                        foreach ($fields as $key => $field) {
                            $pro = $field->getAttribute('pro');
                            ?>
                            <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
                                <div class="control-label"><?php echo $field->label; ?></div>
                                <?php if (J2Store::isPro() != 1 && $pro == 1): ?>
                                    <?php echo J2Html::pro(); ?>
                                <?php else: ?>
                                <div class="controls"><?php echo $field->input; ?>
                                    <br/>
                                    <small class="muted"><?php echo JText::_($field->description); ?></small>
                                    <?php endif; ?>

                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php
            echo JHtml::_('bootstrap.endTab');
            $tab_count++;

        }
        ?>
    </form>
</div>
