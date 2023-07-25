<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;
?>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post"
      action="<?php JRoute::_('index.php'); ?>">
    <?php echo J2Html::hidden('option', 'com_j2store'); ?>
    <?php echo J2Html::hidden('view', 'apps'); ?>
    <?php echo J2Html::hidden('task', 'view', array('id' => 'task')); ?>
    <?php echo JHTML::_('form.token'); ?>
</form>
