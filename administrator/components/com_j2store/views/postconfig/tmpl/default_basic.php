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
					<?php echo J2Html::label(JText::_('J2STORE_STORE_NAME'), 'store_name', array('class'=>'control-label'));?>
					<span class="required">*</span>
					<div class="controls">
					<?php echo J2Html::text('store_name', $this->params->get('store_name'), array('id'=>'store_name'));?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_ADDRESS_ZIP'), 'store_zip', array('class'=>'control-label'));?>
					<span class="required">*</span>
					<div class="controls">
					<?php echo J2Html::text('store_zip', $this->params->get('store_zip'), array('id'=>'store_zip'));?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_ADDRESS_COUNTRY'), 'country_id', array('class'=>'control-label'));?>
					<span class="required">*</span>
					<div class="controls">
					<?php echo J2Html::select()->clearState()
							->type('genericlist')
							->name('country_id')
							->idTag('country_id')
							->value($this->params->get('country_id'))
							->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
							->hasOne('Countries')
							->setRelations(
									array (
											'fields' => array (
													'key'=>'j2store_country_id',
													'name'=>'country_name'
											)
									)
							)->getHtml();
					?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_STORE_DEFAULT_CURRENCY'), 'config_currency', array('class'=>'control-label'));?>
					<span class="required">*</span>
					<div class="controls">
					<?php
					$currencies = J2Store::utilities()->world_currencies();
					echo J2Html::select()->clearState()
							->type('genericlist')
							->name('config_currency')
							->value($this->params->get('config_currency', 'USD'))
							->setPlaceHolders($currencies)
							->getHtml();
					?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_CURRENCY_SYMBOL_LABEL'), 'config_currency_symbol', array('class'=>'control-label'));?>
					<div class="controls">
					<?php echo J2Html::text('config_currency_symbol', '', array('id'=>'config_currency_symbol', 'placeholder'=>'$'));?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_STORE_CURRENCY_AUTO_UPDATE_CURRENCY'), 'config_currency_auto', array('class'=>'control-label'));?>
					<div class="controls">
					<?php
					echo J2Html::select()->clearState()
							->type('genericlist')
							->name('config_currency_auto')
							->value($this->params->get('config_currency_auto', 1))
							->setPlaceHolders(array(
												'0'=>JText::_('JNO'),
												'1'=>JText::_('JYES')
												))
							->getHtml();
					?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'config_weight_class_id', array('class'=>'control-label'));?>
					<div class="controls">
					<?php echo J2Html::select()->clearState()
							->type('genericlist')
							->name('config_weight_class_id')
							->value($this->params->get('config_weight_class_id', 1))
							->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
							->hasOne('Weights')
							->setRelations(
									array (
											'fields' => array (
													'key'=>'j2store_weight_id',
													'name'=>'weight_title'
											)
									)
							)->getHtml();
					?>
					</div>
				</div>

				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'config_length_class_id', array('class'=>'control-label'));?>
					<div class="controls">
					<?php echo J2Html::select()->clearState()
							->type('genericlist')
							->name('config_length_class_id')
							->value($this->params->get('config_length_class_id', 1))
							->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
							->hasOne('Lengths')
							->setRelations(
									array (
											'fields' => array (
													'key'=>'j2store_length_id',
													'name'=>'length_title'
											)
									)
							)->getHtml();
					?>
					</div>
				</div>