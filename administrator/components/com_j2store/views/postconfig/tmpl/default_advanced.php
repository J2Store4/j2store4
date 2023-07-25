<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_CONF_INCLUDING_TAX_LABEL'), 'config_currency_auto', array('class'=>'control-label'));?>
					<span class="required">*</span>
					<div class="controls">
					<?php
					echo J2Html::select()->clearState()
							->type('genericlist')
							->name('config_including_tax')
							->idTag('config_including_tax')
							->value($this->params->get('config_including_tax', 0))
							->setPlaceHolders(array(
												'0'=>JText::_('J2STORE_PRICES_EXCLUDING_TAXES'),
												'1'=>JText::_('J2STORE_PRICES_INCLUDING_TAXES')
												))
							->getHtml();
					?>
					<small><?php echo JText::_('J2STORE_CONF_INCLUDING_TAX_DESC')?></small>
					</div>
				</div>
				
				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_DEFAULT_TAX_RATE'), 'tax_rate', array('class'=>'control-label'));?>
					<div class="controls">
					<?php echo J2Html::text('tax_rate', '');?>
					<small><?php echo JText::_('J2STORE_DEFAULT_TAX_RATE_DESC'); ?></small>
					</div>
				</div>