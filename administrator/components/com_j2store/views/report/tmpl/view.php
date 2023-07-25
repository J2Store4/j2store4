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
$app = $platform->application();
?>
<?php $row = $this->item; ?>
    <!-- shipping plg name -->

<?php
JFactory::getLanguage()->load('plg_j2store_' . $row->element, JPATH_ADMINISTRATOR, null, true);
?>

    <h3><?php echo JText::_('J2STORE_' . strtoupper($row->element)); ?></h3>
<?php
JPluginHelper::importPlugin('j2store');

$results = array();
$results = $app->triggerEvent('onJ2StoreGetReportView', array($row));
$html = '';
foreach ($results as $result) {
    $html .= $result;
}
echo $html;
?>