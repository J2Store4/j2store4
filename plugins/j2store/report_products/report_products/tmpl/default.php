<?php
/**
 * --------------------------------------------------------------------------------
 * Report Plugin - Products
 * --------------------------------------------------------------------------------
 * @package     Joomla 3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2015 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die ('Restricted access');
$check_order_id = array();

$platform = J2Store::platform();
$platform->addIncludePath(JPATH_COMPONENT . '/helpers/html');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('formbehavior.chosen', 'select');
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<?php
unset ($listOrder);
$listOrder = $vars->state->get('filter_order', 'orderitem.j2store_orderitem_id');
$listDirn = $vars->state->get('filter_order_Dir');
$order_status = $vars->state->get('filter_orderstatus');
$currency = J2Store::currency();
?>
    <script type="text/javascript">
        Joomla.submitbutton = function(pressbutton) {
            if(pressbutton === 'cancel') {
                document.querySelectorAll('.csvdiv').forEach(function(element) {
                    element.innerHTML = '';
                });
            }
            Joomla.submitform(pressbutton);
            return true;
        }
    </script>
<?php $form = $vars->form; ?>
    <link rel="stylesheet"
          href="<?php echo JUri::root() . "plugins/j2store/report_products/report_products/css/material-charts.css" ?>">
    <script src="<?php echo JUri::root() . "plugins/j2store/report_products/report_products/js/material-charts.js" ?>"></script>
    <div class="j2store">

        <form class="form-horizontal" method="post"
              action="<?php echo $form['action']; ?>" name="adminForm" id="adminForm">
            <div class="<?php echo $col_class; ?> 6">
                <div class="<?php echo $col_class; ?> 12" style="margin-bottom: 10px; margin-left: 12px;">
			<span class="<?php echo $col_class; ?> 6"> <label> <strong><?php echo JText::_('J2STORE_FILTER_SEARCH'); ?></strong>
			</label> <input type="text" name="filter_search"
                            value="<?php echo htmlspecialchars($vars->state->get('filter_search')); ?>"
                            id="search"/>
				<button class="btn btn-inverse"
                        onclick="document.getElementById('search').value='';jQuery('.csvdiv').html('');this.form.submit();">
					<i class="icon icon-remove"></i>
				</button>

			</span>
                    <span class="<?php echo $col_class; ?> 6">
				<label> <strong><?php echo JText::_('J2STORE_FILTER_ORDERSTATUS'); ?></strong>
			</label>
				<?php
                $attribs = array(
                    'class' => 'input',
                    'multiple' => 'multiple'
                );
                echo JHtml::_('select.genericlist', $vars->orderStatus, 'filter_orderstatus[]', $attribs, 'value', 'text', $vars->state->get('filter_orderstatus'));
                ?>
			</span>
                </div>
                <div class="<?php echo $col_class; ?> 12" style="margin-bottom: 10px;">
			<span class="<?php echo $col_class; ?> 6"> <label> <strong><?php echo JText::_('J2STORE_FILTER_DURATION'); ?></strong>
			</label>
				<?php
                $attribs = array(
                    'class' => 'input',
                    'onchange' => "jQuery('.csvdiv').html('');this.form.submit();"
                );
                echo JHtml::_('select.genericlist', $vars->orderDateType, 'filter_datetype', $attribs, 'value', 'text', $vars->state->get('filter_datetype'));
                ?>
			</span>

                </div>
                <div class="<?php echo $col_class; ?> 12" style="margin-bottom: 10px;">
			<span class="<?php echo $col_class; ?> 6">
				<?php if ($vars->state->get('filter_datetype') == 'custom'): ?>
                    <label> <strong><?php echo JText::_('J2STORE_ORDERS_EXPORT_FROM_DATE'); ?></strong>
				</label>
					 	<?php echo JHtml::calendar($vars->state->get('filter_order_from_date'), 'filter_order_from_date', 'filter_order_from_date', '%Y-%m-%d', array('class' => 'input-mini')); ?>

					<label> <strong><?php echo JText::_('J2STORE_ORDERS_EXPORT_TO_DATE'); ?></strong>
				</label>
						<?php echo JHtml::calendar($vars->state->get('filter_order_to_date'), 'filter_order_to_date', 'filter_order_to_date', '%Y-%m-%d', array('class' => 'input-mini')); ?>

                    <button class="btn btn-inverse"
                            onclick="document.getElementById('filter_order_from_date').value='',document.getElementById('filter_order_to_date').value='';this.form.submit();">
					<i class="icon icon-remove"></i>
				</button>
                <?php endif; ?>
			</span>

                </div>
                <div align="right" style="margin-bottom: 20px;">
                    <button class="btn btn-warning btn-large"
                            onclick="jQuery('.csvdiv').html('<input type=\'hidden\' name=\'format\' value=\'csv\'>');this.form.submit();"
                            style="margin-right: 50px;"><?php echo JText::_('J2STORE_EXPORT'); ?>
                    </button>
                    <button class="btn btn-success btn-large"
                            onclick="jQuery('.csvdiv').html('');this.form.submit();"
                            style="margin-right: 50px;"><?php echo JText::_('J2STORE_FILTER'); ?>
                    </button>
                </div>
            </div>
            <div class="<?php echo $col_class; ?>6">

                <div class="example-chart">
                    <div id="bar-chart-example"></div>
                </div>

            </div>
            <div class="<?php echo $col_class; ?>12"><span class="pull-right"><?php echo $vars->pagination->getLimitBox(); ?></span></div>
            <div class="<?php echo $col_class; ?>12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>
                            <?php
                            echo JHtml::_('grid.sort', 'PLG_J2STORE_PRODUCT_NAME', 'orderitem.orderitem_name', $vars->state->get('filter_order_Dir'), $vars->state->get('filter_order')); ?>
                        </th>
                        <th>
                            <?php
                            echo JHtml::_('grid.sort', 'J2STORE_REPORT_TOTAL_QUANTITY', 'orderitem.orderitem_quantity', $vars->state->get('filter_order_Dir'), $vars->state->get('filter_order'));// echo JText::_('PLG_J2STORE_PRODUCT_NAME'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('J2STORE_REPORT_PRODUCT_DISCOUNT'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('J2STORE_REPORT_PRODUCT_TAX'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('J2STORE_REPORT_PRODUCT_WITHOUT_TAX'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('J2STORE_REPORT_PRODUCT_WITH_TAX'); ?>
                        </th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td colspan="8"><?php echo $vars->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                    </tfoot>
                    <?php if (count($vars->products)): ?>
                        <?php
                        $qty_total = 0;
                        $discount_total = 0;
                        $total_without_tax = 0;
                        $total_with_tax = 0;
                        $total_tax = 0;
                        ?>
                        <?php foreach ($vars->products as $product): ?>
                            <?php
                            $qty_total += $product->total_qty;
                            $discount_total += $product->total_item_discount + $product->total_item_discount_tax;
                            $total_without_tax += $product->total_final_price_without_tax;
                            $total_with_tax += $product->total_final_price_with_tax;
                            $total_tax += $product->total_item_tax;
                            ?>
                            <tbody>
                            <tr>
                                <td><?php echo $product->orderitem_name; ?>
                                    <br/>
                                    <?php echo JText::_('J2STORE_SKU'); ?>: <?php echo $product->orderitem_sku; ?></td>
                                <td><?php echo $product->total_qty; ?></td>
                                <td><?php echo $currency->format($product->total_item_discount + $product->total_item_discount_tax); ?></td>
                                <td><?php echo $currency->format($product->total_item_tax); ?></td>
                                <td><?php echo $currency->format($product->total_final_price_without_tax); ?></td>
                                <td><?php echo $currency->format($product->total_final_price_with_tax); ?></td>
                            </tr>
                            </tbody>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong><?php echo JText::_('J2STORE_TOTAL'); ?></strong></td>
                            <td><?php echo $qty_total; ?></td>
                            <td><?php echo $currency->format($discount_total); ?></td>
                            <td><?php echo $currency->format($total_tax); ?></td>
                            <td><?php echo $currency->format($total_without_tax); ?></td>
                            <td><?php echo $currency->format($total_with_tax); ?></td>
                        </tr>
                    <?php else: ?>
                        <tbody>
                        <tr>
                            <td colspan="5"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?></td>
                        </tr>
                        </tbody>
                    <?php endif; ?>
                </table>
            </div>
            <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
            <input type="hidden" name="reportTask" value=""/>
            <input type="hidden" name="task" value="view"/>
            <input type="hidden" name="report_id" value=" <?php echo $vars->id; ?>"/>
            <input type="hidden" name="boxchecked" value=""/>
            <input type="hidden" name="order_change" value="0"/>
            <div class="csvdiv">

            </div>
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
<?php if (!empty($vars->product_amount)): ?>
    <script>
        var exampleBarChartData = {
            "datasets": {
                "values": <?php echo json_encode($vars->product_amount);?>,//[5, 10, 30, 50, 20],
                "labels": <?php echo json_encode($vars->product_name);?>,
                "color": "blue"
            },
            "title": "Product Report",
            "height": "300px",
            "width": "500px",
            "background": "#FFFFFF",
            "shadowDepth": "1",

        };

        MaterialCharts.bar("#bar-chart-example", exampleBarChartData)
    </script>
<?php endif; ?>