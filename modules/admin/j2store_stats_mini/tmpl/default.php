<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
$currency = J2Store::currency();
$order_status = $params->get('order_status', array('*'));
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="j2store_stat-mini">
    <div class="<?php echo $row_class; ?>">

        <div class="<?php echo $col_class; ?>3">
            <div class="j2store-stats-mini-badge j2store-stats-mini-today">
                <?php
                $tz = JFactory::getConfig()->get('offset');
                $today = JFactory::getDate('now', $tz)->format('Y-m-d');
                $tomorrow = JFactory::getDate('now +1 days', $tz)->format('Y-m-d');
                ?>
                <span class="j2store-mini-price">
			<?php
            echo $currency->format(
                F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                    ->since($today)
                    ->until($tomorrow)
                    ->orderstatus($order_status)
                    ->nozero(1)
                    ->moneysum(1)
                    ->getOrdersTotal()
            );
            ?>
			</span>
                <h3><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_TODAY'); ?></h3>
            </div>
        </div>

        <div class="<?php echo $col_class; ?>3">
            <div class="j2store-stats-mini-badge j2store-stats-mini-yesterday">
                <?php
                $yesterday = JFactory::getDate('now -1 days', $tz)->format('Y-m-d');
                ?>
                <span class="j2store-mini-price">
			<?php
            echo $currency->format(
                F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                    ->since($yesterday)
                    ->until($yesterday . ' 23:59:59')
                    ->orderstatus($order_status)
                    ->nozero(1)
                    ->moneysum(1)
                    ->getOrdersTotal()
            );
            ?>
			</span>
                <h3><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_YESTERDAY'); ?></h3>
            </div>
        </div>

        <div class="<?php echo $col_class; ?>3">
            <div class="j2store-stats-mini-badge j2store-stats-mini-this-month">
                <?php
                switch (gmdate('m')) {
                    case 1:
                    case 3:
                    case 5:
                    case 7:
                    case 8:
                    case 10:
                    case 12:
                        $lmday = 31;
                        break;
                    case 4:
                    case 6:
                    case 9:
                    case 11:
                        $lmday = 30;
                        break;
                    case 2:
                        $y = gmdate('Y');
                        if (!($y % 4) && ($y % 400)) {
                            $lmday = 29;
                        } else {
                            $lmday = 28;
                        }
                }
                if ($lmday < 1) $lmday = 28;
                ?>
                <?php
                /* 	echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                                        ->since(gmdate('Y').'-'.gmdate('m').'-01')
                                        ->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
                                        ->orderstatus($order_status)
                                        ->nozero(1)
                                        ->getOrdersTotal(); */
                ?>
                <span class="j2store-mini-price">
			<?php
            echo $currency->format(
                F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
                    ->since(gmdate('Y') . '-' . gmdate('m') . '-01')
                    ->until(gmdate('Y') . '-' . gmdate('m') . '-' . $lmday . ' 23:59:59')
                    ->orderstatus($order_status)
                    ->nozero(1)
                    ->moneysum(1)
                    ->getOrdersTotal()
            );
            ?>
			</span>
                <h3><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_THIS_MONTH'); ?></h3>
            </div>
        </div>

        <div class="<?php echo $col_class; ?>3">
            <div class="j2store-stats-mini-badge j2store-stats-mini-orders">
				<span class="j2store-mini-price">
					<?php echo $currency->format(F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()->orderstatus($order_status)->nozero(1)->moneysum(1)->getOrdersTotal()); ?>
				</span>
                <h3><?php echo JText::_('J2STORE_ALL_TIME'); ?></h3>
            </div>
        </div>

    </div>
</div>