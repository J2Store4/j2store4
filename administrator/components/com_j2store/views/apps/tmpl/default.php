<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
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
$sortFields = array(
    'id' => JText::_('JGRID_HEADING_ID'),
    'name' => JText::_('COM_ATS_TICKETS_HEADING_TITLE'),
    'state' => JText::_('JSTATUS'),
);
$sidebar = JHtmlSidebar::render();
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
<script type="text/javascript">
    Joomla.orderTable = function () {
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '$order') {
            dirn = 'asc';
        } else {
            dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn);
    }
</script>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=apps'); ?>" method="post" name="adminForm"
      id="adminForm" xmlns="https://www.w3.org/1999/html">
    <input type="hidden" name="task" value="browse"/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists->order; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists->order_Dir; ?>"/>
    <input type="hidden" id="token" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>
    <div class="<?php echo $row_class; ?>">
        <?php if (!empty($sidebar)): ?>
        <div id="j-sidebar-container" class="<?php echo $col_class; ?>2">
            <?php echo $sidebar; ?>
        </div>
        <div id="j-main-container" class="<?php echo $col_class; ?>10">
            <?php else : ?>
            <div id="j-main-container">
                <?php endif; ?>
                <div class="j2store apps">
                    <div class="app-search">
                        <input type="text" name="search" id="search"
                               value="<?php echo $this->escape($this->getModel()->getState('search', '')); ?>"
                               class="input-large" onchange="document.adminForm.submit();"
                               placeholder="<?php echo JText::_('J2STORE_APP_NAME'); ?>"
                        />
                        <nobr>
                            <button class="btn btn-success"
                                    type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                            <button class="btn btn-inverse" type="button"
                                    onclick="document.id('search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
                        </nobr>

                    </div>
                    <h2 class="app-heading"><?php echo JText::_('COM_J2STORE_TITLE_APPS') ?></h2>
                    <div class="alert alert-info"> <?php echo JText::_('COM_J2STORE_EXTENSIONS_ALERT') ?></div>
                    <?php $i = -1 ?>
                    <?php foreach ($this->items as $i => $app): ?>
                        <?php
                        $i++;
                        $app->published = $app->enabled;
                        //load the language files
                        JFactory::getLanguage()->load('plg_j2store_' . $app->element, JPATH_ADMINISTRATOR);
                        //var_dump($app->manifest_cache);
                        $params = $platform->getRegistry($app->manifest_cache);
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
                                            <?php if (JFile::exists(JPATH_SITE . '/plugins/j2store/' . $app->element . '/images/' . $app->element . '.png')): ?>
                                                <img src="<?php echo JUri::root(true) . '/plugins/j2store/' . $app->element . '/images/' . $app->element . '.png'; ?>"/>
                                            <?php elseif (JFile::exists(JPATH_SITE . '/media/j2store/images/' . $app->element . '.png')): ?>
                                                <img src="<?php echo JUri::root(true) . '/media/j2store/images/' . $app->element . '.png'; ?>"/>
                                            <?php else: ?>
                                                <img src="<?php echo JUri::root(true) . '/media/j2store/images/app_placeholder.png'; ?>"/>
                                            <?php endif; ?>
                                        </div>

                                        <div class="app-name">
                                            <h3 class="panel-title"><?php echo JText::_($app->name); ?></h3>
                                        </div>

                                        <div class="app-description">
                                            <?php
                                            $desc = $params->get('description');
                                            echo substr(JText::_($desc), 0, 100) . '...';
                                            ?>
                                        </div>
                                        <div class="app-footer">
						<span class="author">
							<?php echo $params->get('author'); ?>
						</span>

                                            <span class="version pull-right"><strong><?php echo JText::_('J2STORE_APP_VERSION'); ?> : <?php echo $params->get('version'); ?></strong></span>
                                        </div>
                                    </div>
                                    <div class="panel-footer">
                                        <div class="app-action">
                                            <?php if ($app->enabled): ?>
                                                <a
                                                        class="app-button app-button-open j2-flat-button"
                                                        href="<?php echo 'index.php?option=com_j2store&view=apps&task=view&layout=view&id=' . $app->extension_id ?>">
                                                    <?php echo JText::_('J2STORE_OPEN'); ?>
                                                    <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($app->enabled): ?>
                                                <a
                                                        class="app-button app-button-unpublish j2-flat-button"
                                                        href="<?php echo 'index.php?option=com_j2store&view=apps&task=unpublish&id=' . $app->extension_id . '&' . JFactory::getSession()->getFormToken() . '=1'; ?>">
                                                    <?php echo JText::_('J2STORE_DISABLE'); ?>
                                                </a>
                                            <?php else: ?>
                                                <a
                                                        class="app-button app-button-publish j2-flat-button"
                                                        href="<?php echo 'index.php?option=com_j2store&view=apps&task=publish&id=' . $app->extension_id . '&' . JFactory::getSession()->getFormToken() . '=1'; ?>">
                                                    <?php echo JText::_('J2STORE_ENABLE'); ?>
                                                </a>
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
                    <div class="payment-content inline-content">
                        <div class="<?php echo $row_class;?>">
                            <div class="<?php echo $col_class;?>12">
                                <div class="hero-unit">
                                    <h2>Get more apps for your store. Increase sales and customer engagement</h2>
                                    <p class="lead">
                                        Choose from 25 + apps that will help you boost your sales, improve customer engagement and do more
                                        with J2Store.
                                    </p>
                                    <a target="_blank" class="app-button app-button-open j2-flat-button"
                                       href="<?php echo J2Store::buildHelpLink('extensions/apps.html', 'apps'); ?>"><?php echo JText::_('J2STORE_GET_MORE_APPS'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (!empty($sidebar)): ?>
            </div>
                <?php else:?>
        </div>
                <?php endif; ?>

    </div>
</form>