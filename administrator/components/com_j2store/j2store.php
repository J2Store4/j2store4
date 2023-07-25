<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;
// Load FOF
// Include F0F
if(!defined('F0F_INCLUDED')) {
	require_once JPATH_LIBRARIES.'/f0f/include.php';
}
if(!defined('F0F_INCLUDED')) {
?>
   <h2>Incomplete installation detected</h2>
<?php
}
F0FDispatcher::getTmpInstance('com_j2store')->dispatch();
?>
