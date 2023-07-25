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
?>
<?php $row = $this->item;?>
    <!-- shipping plg name -->
    <h3><?php echo JText::_($row->name); ?></h3>
<?php
$app = JFactory::getApplication();

JPluginHelper::importPlugin('j2store');

$results = $app->triggerEvent( 'onJ2StoreGetShippingView',array( $row ));

for ($i=0; $i<count($results); $i++)
{
    $result = $results[$i];
    echo $result;
}