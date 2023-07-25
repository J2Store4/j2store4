<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class;?>">
    <div class="<?php echo $col_class;?>12">

        <div class="alert alert-block alert-info">
            <h4><?php echo JText::_('J2STORE_QUICK_HELP'); ?></h4>
            <?php echo JText::_('J2STORE_VARIANT_PRODUCT_HELP_TEXT'); ?>
        </div>
        <?php if (version_compare(JVERSION, '3.99.99', 'lt')) : ?>
            <div class="tabbable tabs-left">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#generalTab" data-toggle="tab"><i class="fa fa-home"></i>
                            <?php echo JText::_('J2STORE_PRODUCT_TAB_GENERAL'); ?>
                        </a>
                    </li>
                    <li><a href="#imagesTab" data-toggle="tab"><i class="fa fa-file-image-o"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_IMAGES'); ?></a></li>
                    <li><a href="#variantsTab" data-toggle="tab"><i class="fa fa-sitemap"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_VARIANTS'); ?></a></li>
                    <li><a href="#filterTab" data-toggle="tab"><i class="fa fa-filter"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_FILTER'); ?></a></li>
                    <li><a href="#relationsTab" data-toggle="tab"><i class="fa fa-group"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_RELATIONS'); ?></a></li>
                    <li><a href="#appsTab" data-toggle="tab"><i class="fa fa-group"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_APPS'); ?></a></li>

                </ul>
                <!-- / Tab content starts -->
                <div class="tab-content">
                    <div class="tab-pane active" id="generalTab">
                        <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo $this->item->variant->j2store_variant_id; ?>" />
                        <?php echo $this->loadTemplate('variable_general');?>
                    </div>
                    <div class="tab-pane" id="imagesTab">
                        <?php echo $this->loadTemplate('images');?>
                    </div>

                    <div class="tab-pane" id="variantsTab">
                        <?php echo $this->loadTemplate('variable_options');?>
                        <?php echo $this->loadTemplate('variants');?>
                    </div>
                    <div class="tab-pane" id="filterTab">
                        <?php echo $this->loadTemplate('filters');?>
                    </div>
                    <div class="tab-pane" id="relationsTab">
                        <?php echo $this->loadTemplate('relations');?>
                    </div>
                    <div class="tab-pane" id="appsTab">
                        <?php echo $this->loadTemplate('apps');?>
                    </div>
                </div>
                <!-- / Tab content Ends -->
            </div> <!-- /tabbable -->
        <?php else: ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.startTabSet', 'j2storetab', ['active' => 'generalTab', 'recall' => true, 'breakpoint' => 768]); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'generalTab', JText::_('J2STORE_PRODUCT_TAB_GENERAL')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo isset($this->variant->j2store_variant_id) && !empty($this->variant->j2store_variant_id) ? $this->variant->j2store_variant_id: 0; ?>" />
                    <?php echo $this->loadTemplate('variable_general');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'imagesTab', JText::_('J2STORE_PRODUCT_TAB_IMAGES')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('images');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'variantsTab', JText::_('J2STORE_PRODUCT_TAB_VARIANTS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('variable_options');?>
                    <?php  echo $this->loadTemplate('variants');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'filterTab', JText::_('J2STORE_PRODUCT_TAB_FILTER')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('filters');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'relationsTab', JText::_('J2STORE_PRODUCT_TAB_RELATIONS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('relations');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.addTab', 'j2storetab', 'appsTab', JText::_('J2STORE_PRODUCT_TAB_APPS')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <?php  echo $this->loadTemplate('apps');?>
                </div>
            </div>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTab'); ?>
            <?php echo \Joomla\CMS\HTML\HTMLHelper::_('uitab.endTabSet'); ?>
        <?php endif; ?>
    </div>
</div>
