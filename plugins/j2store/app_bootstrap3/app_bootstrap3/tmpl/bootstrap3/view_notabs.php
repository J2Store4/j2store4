<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Bootstrap 2 layout of product detail
 */
// No direct access
defined('_JEXEC') or die;
?>
	<div class="row">
		<div class="col-sm-12">
				<?php if($this->params->get('item_show_sdesc') || $this->params->get('item_show_ldesc') ):?>
				<div class="product-description">
					<?php echo $this->loadTemplate('sdesc'); ?>
					<?php echo $this->loadTemplate('ldesc'); ?>
				</div>
				<?php endif;?>

				<?php if($this->params->get('item_show_product_specification')):?>
					<div class="product-specs">
						<?php echo $this->loadTemplate('specs'); ?>
					</div>
				<?php endif;?>		
		</div>
	</div>