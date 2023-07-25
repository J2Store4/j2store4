<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="payment-content inline-content">
    <div class="<?php echo $row_class; ?>">
        <div class="<?php echo $col_class ?>6">

            <div class="hero-unit">
                <h2>Need help in setting up payment methods ?</h2>
                <p class="lead">
                    Check our comprehensive user guide
                </p>
                <a onclick="return ! window.open(this.href);" class="btn btn-large btn-warning" href="<?php echo J2Store::buildHelpLink('support/user-guide.html', 'gateways'); ?>">User guide</a>
                <a onclick="return ! window.open(this.href);" class="btn btn-large btn-info" href="<?php echo J2Store::buildHelpLink('support.html', 'gateways'); ?>">Support center</a>
            </div>

        </div>
        <div class="<?php echo $col_class ?>6">
            <div class="hero-unit">
                <h2>Looking for more payment options? Check our extensions directory</h2>
                <p class="lead">
                    J2Store is integrated with 65+ payment gateways across the world.
                    <br />
                    Find more at our extensions directory
                </p>
                <a onclick="return ! window.open(this.href);" class="btn btn-large btn-success" href="<?php echo J2Store::buildHelpLink('extensions/payment-plugins.html', 'gateways'); ?>">Get more payment plugins</a>
            </div>
        </div>

    </div>
</div>
