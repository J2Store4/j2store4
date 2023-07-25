<?php
/**
 * @package     J2Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c)2018 Ramesh Elamathi / J2Store.org
 * @license     GNU GPL v3 or later
 * */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// load tooltip behavior
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.framework');
//$platform->loadExtra('behavior.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('dropdown.init');
$platform->loadExtra('formbehavior.chosen', 'select');
$current_page = $this->state->get('current_page', 'popular');

$total = count($this->items);
$counter = 0;
$col = 3;
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=appstores'); ?>" method="post"
      name="adminForm"
      id="adminForm" xmlns="https://www.w3.org/1999/html">
    <input type="hidden" name="task" value="browse"/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="page" value="<?php echo $current_page; ?>"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>"/>
    <input type="hidden" id="token" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>
    <div class="<?php echo $row_class; ?>">
        <div id="j2-main-container">
            <div class="j2store apps">
                <div class="<?php echo $row_class; ?>">
                    <div class="<?php echo $col_class; ?>6 app_search">
                        <input type="text" name="search" id="search"
                               value="<?php echo $this->escape($this->state->search); ?>"
                               class="input-large" onchange="document.adminForm.submit();"
                               placeholder="<?php echo JText::_('J2STORE_PLUGIN_NAME'); ?>"
                        />
                        <nobr>
                            <button class="btn btn-success"
                                    type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                            <button class="btn btn-inverse" type="button"
                                    onclick="document.adminForm.search.value='';document.adminForm.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
                        </nobr>
                    </div>
                    <div class="<?php echo $col_class; ?>6">
                        <label for="plugin_type"
                               style="display: inline;"><strong><?php echo JText::_('J2STORE_PLUGIN_TYPES'); ?></strong></label>&nbsp;&nbsp;&nbsp;&nbsp;
                        <select name="plugin_type" id="j2_plugin_type" onchange="document.adminForm.submit();"
                                style="display: inline;">
                            <?php foreach ($this->plugin_types as $plugin_key => $plugin_value): ?>
                                <?php if ($plugin_key == $this->state->plugin_type): ?>
                                    <option value="<?php echo $plugin_key; ?>"
                                            selected="selected"><?php echo $plugin_value; ?></option>
                                <?php else: ?>
                                    <option value="<?php echo $plugin_key; ?>"><?php echo $plugin_value; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <h2 class="app-heading"><?php //echo JText::_('COM_J2STORE_TITLE_PLUGINS')?></h2>

                <?php $i = -1 ?>
                <?php

                foreach ($this->items as $i => $app): ?>
                    <?php
                    $i++;
                    $element = isset($app['element']) ? $app['element'] : '';
                    $image_url = isset($app['main_image']) ? trim($app['main_image']) : '';
                    if (empty($image_url)) {
                        $image_url = JUri::root(true) . '/media/j2store/images/app_placeholder.png';
                    }
                    $plugin_name = isset($app['plugin_name']) ? $app['plugin_name'] : '';
                    $short_desc = isset($app['short_desc']) ? $app['short_desc'] : '';
                    $author = isset($app['developer']) ? $app['developer'] : 'J2Store';
                    $app_version = isset($app['version']) ? $app['version'] : '1.0.0';
                    $buy_url = isset($app['site_url']) ? $app['site_url'] : '';
                    $document_url = isset($app['documentation-url']) ? $app['documentation-url'] : '';

                    //load the language files
                    //JFactory::getLanguage()->load('plg_j2store_'.$app['element'], JPATH_ADMINISTRATOR);
                    ?>
                    <?php $rowcount = ((int)$counter % (int)$col) + 1; ?>
                    <?php if ($rowcount == 1) : ?>
                        <?php $row = $counter / $col; ?>
                        <div class="j2store-apps-row <?php echo 'row-' . $row; ?> <?php echo $row_class; ?>">
                    <?php endif; ?>
                    <div class="<?php echo $col_class; ?><?php echo round((12 / $col)); ?>">
                        <div class="app-container">
                            <div class="panel panel-warning">
                                <div class="panel-body">
                                    <div class="app-image">
                                        <?php
                                        if (!empty($image_url)):?>
                                            <img src="<?php echo $image_url; ?>" style="max-width: 100%;"/>
                                        <?php endif; ?>
                                    </div>

                                    <div class="app-name">
                                        <h3 class="panel-title"><?php echo JText::_($plugin_name); ?></h3>
                                    </div>

                                    <div class="app-description">
                                        <?php
                                        echo substr(JText::_($short_desc), 0, 100) . '...';
                                        ?>
                                    </div>
                                    <div class="app-footer">
						<span class="author">
							<?php echo $author; ?>
						</span>

                                        <span class="version pull-right"><strong><?php echo JText::_('J2STORE_APP_VERSION'); ?> : <?php echo $app_version; ?></strong></span>
                                    </div>
                                </div>
                                <div class="panel-footer">
                                    <div class="app-action">
                                        <?php if (in_array($element, $this->installed_plugin)): ?>
                                            <?php
                                            $installed_version = isset($this->plugin_version[$element]) ? $this->plugin_version[$element] : '1.0.0';
                                            $is_need_update = false;
                                            $class = 'app-button j2-flat-button btn-success';
                                            $display_text = JText::_('J2STORE_INSTALLED');
                                            $url = 'javascript:void(0)';
                                            $target = '';
                                            if (version_compare($installed_version, $app_version, 'lt')) {
                                                $is_need_update = true;
                                                $class = 'app-button btn-primary j2-flat-button';
                                                $display_text = JText::_('J2STORE_UPDATE_PLUGIN');
                                                $url = 'https://www.j2store.org/my-account/my-downloads.html';
                                                $target = '_blank';
                                            }

                                            //$plugin_data = JPluginHelper::getPlugin('j2store', $element);
                                            ?>
                                            <a class="<?php echo $class; ?>" href="<?php echo $url; ?>"
                                               target="<?php echo $target; ?>"><?php echo $display_text; ?></a>
                                        <?php else: ?>
                                            <a class="app-button app-button-publish j2-flat-button"
                                               target="_blank"
                                               href="<?php echo $buy_url; ?>">
                                                <?php echo JText::_('J2STORE_BUY'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($document_url)): ?>
                                            <a class="app-button btn-info j2-flat-button" target="_blank"
                                               href="<?php echo $document_url; ?>">
                                                <?php echo JText::_('J2STORE_DOCUMENT'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (isset($app['price'])): ?>
                                            <strong class="pull-right"><?php echo JText::_('J2STORE_PRODUCT_PRICE'); ?>
                                                : <?php echo $app['price']; ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </div>


                            </div>
                        </div>

                    </div>

                    <?php $counter++; ?>
                    <?php if (($rowcount == $col) or ($counter == $total)) : ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php //  echo $this->pagination->getPagesLinks(); ?>
                <div class="pagination">
                    <?php echo $this->pagination->getListFooter(); ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</form>

