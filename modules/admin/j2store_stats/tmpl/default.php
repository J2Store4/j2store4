<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
$currency = J2Store::currency();
$order_status = $params->get('order_status',array('*'));
?>
<div class="j2store_statistics">
   <h3><i class="fa fa-line-chart"></i><?php echo JText::_('J2STORE_ORDER_STATISTICS');?></h3>
	<table class="adminlist table table-bordered table-striped">
	<thead>
		<th></th>
		<th><?php echo JText::_('J2STORE_TOTAL'); ?></th>
		<th><?php echo JText::_('J2STORE_AMOUNT'); ?></th>
	</thead>
	<tbody>
		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_ORDERS'); ?></td>
			<td>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()->orderstatus($order_status)->nozero(1)->getOrdersTotal();

			?>
			</td>
			<td>
			 <?php
			/* 	echo $currency->format(F0FModel::getTmpInstance('Orders', 'J2StoreModel')
												->clearState()
												->orderstatus($order_status)
												->nozero(1)
												->moneysum(1)
												->getOrdersTotal()); */
			?>

			<?php
				echo $currency->format(F0FModel::getTmpInstance('Orders', 'J2StoreModel')
												->clearState()
												->orderstatus($order_status)
												->nozero(1)
												->moneysum(1)
												->getOrdersTotal());
			?>
			</td>
		</tr>

		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_LAST_YEAR'); ?></td>
			<td>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since((gmdate('Y')-1).'-01-01 00:00:00')
									->until((gmdate('Y')-1).'-12-31 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</td>
			<td>
			<?php
		echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since((gmdate('Y')-1).'-01-01 00:00:00')
									->until((gmdate('Y')-1).'-12-31 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</td>
		</tr>

		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_THIS_YEAR'); ?></td>
			<td>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since(gmdate('Y').'-01-01')
									->until(gmdate('Y').'-12-31 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</td>
			<td>
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since(gmdate('Y').'-01-01')
									->until(gmdate('Y').'-12-31 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</td>
		</tr>

		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_LAST_MONTH'); ?></td>
			<td>
			<?php
							$y = gmdate('Y');
							$m = gmdate('m');
							if($m == 1) {
								$m = 12; $y -= 1;
							} else {
								$m -= 1;
							}
							switch($m) {
								case 1: case 3: case 5: case 7: case 8: case 10: case 12:
									$lmday = 31; break;
								case 4: case 6: case 9: case 11:
									$lmday = 30; break;
								case 2:
									if( !($y % 4) && ($y % 400) ) {
										$lmday = 29;
									} else {
										$lmday = 28;
									}
							}
							if($y < 2011) $y = 2011;
							if($m < 1) $m = 1;
							if($lmday < 1) $lmday = 1;
			?>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since($y.'-'.$m.'-01')
									->until($y.'-'.$m.'-'.$lmday.' 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</td>
			<td>
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since($y.'-'.$m.'-01')
									->until($y.'-'.$m.'-'.$lmday.' 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</td>
		</tr>

		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_THIS_MONTH'); ?></td>
			<td>
			<?php
							switch(gmdate('m')) {
								case 1: case 3: case 5: case 7: case 8: case 10: case 12:
									$lmday = 31; break;
								case 4: case 6: case 9: case 11:
									$lmday = 30; break;
								case 2:
									$y = gmdate('Y');
									if( !($y % 4) && ($y % 400) ) {
										$lmday = 29;
									} else {
										$lmday = 28;
									}
							}
							if($lmday < 1) $lmday = 28;
						?>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since(gmdate('Y').'-'.gmdate('m').'-01')
									->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</td>
			<td>
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since(gmdate('Y').'-'.gmdate('m').'-01')
									->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</td>
		</tr>
		<?php
		$tz = JFactory::getConfig()->get('offset');
		$previous = JFactory::getDate ('now -7 days',$tz)->format ( 'Y-m-d' );
		$today = JFactory::getDate ('now',$tz)->format ( 'Y-m-d' );
		?>
		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_LAST7DAYS'); ?></td>
			<td>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $previous )
									->until( $today.' 23:59:59' )
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</td>
			<td>
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $previous )
									->until( $today.' 23:59:59' )
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</td>
		</tr>


		<tr>
			<td><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_YESTERDAY'); ?></td>
			<td>
			<?php
			$yesterday = JFactory::getDate ('now -1 days',$tz)->format ( 'Y-m-d' );
			?>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $yesterday )
									->until( $yesterday.' 23:59:59' )
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</td>
			<td>
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $yesterday )
									->until( $yesterday.' 23:59:59' )
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</td>
		</tr>


		<tr>
			<td><strong><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_TODAY'); ?></strong></td>
			<td><strong>
			<?php
				$tomorrow = JFactory::getDate ('now +1 days',$tz)->format ( 'Y-m-d' );
			?>
			<?php
				echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $today )
									->until( $today.' 23:59:59' )
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal();
			?>
			</strong>
			</td>
			<td>
			<strong>
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $today )
									->until( $today.' 23:59:59' )
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</strong>
			</td>
		</tr>

		<tr>
			<td><strong><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_AVERAGE'); ?></strong></td>

			<?php
						switch(gmdate('m')) {
							case 1: case 3: case 5: case 7: case 8: case 10: case 12:
								$lmday = 31; break;
							case 4: case 6: case 9: case 11:
								$lmday = 30; break;
							case 2:
								$y = gmdate('Y');
								if( !($y % 4) && ($y % 400) ) {
									$lmday = 29;
								} else {
									$lmday = 28;
								}
						}
						if($lmday < 1) $lmday = 28;
						if($y < 2011) $y = 2011;
						$daysin = gmdate('d');
						$numsubs = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
							->since(gmdate('Y').'-'.gmdate('m').'-01')
							->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
							->nozero(1)
							->orderstatus($order_status)
							->getOrdersTotal();
						$summoney = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
							->since(gmdate('Y').'-'.gmdate('m').'-01')
							->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
							->moneysum(1)
							->orderstatus($order_status)
							->getOrdersTotal();
					?>

			<td>
				<strong><?php echo sprintf('%01.1f', $numsubs/$daysin)?><strong>
			</td>
			<td>
			<strong>
			<?php
			echo $currency->format(
					sprintf('%01.2f', $summoney/$daysin)
			);
			?>
			</strong>
			</td>
		</tr>



	</tbody>



	</table>


</div>
