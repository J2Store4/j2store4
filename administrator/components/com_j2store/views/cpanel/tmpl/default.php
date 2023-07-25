<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('bootstrap.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('formbehavior.chosen', 'select');
$sidebar = JHtmlSidebar::render();
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<style type="text/css">
	input[disabled] {
		background-color: #46a546 !important;
	}
</style>
    <form
            action="<?php echo JRoute::_('index.php?option=com_j2store&view=cpanel'); ?>"
            method="post" name="adminForm" id="adminForm">
<div class="<?php echo $row_class;?>">
        <?php if(!empty( $sidebar )): ?>
        <div id="j-sidebar-container" class="<?php echo $col_class;?>2">
            <?php echo $sidebar ; ?>
        </div>
        <div id="j-main-container" class="<?php echo $col_class;?>10">
            <?php else : ?>
            <div id="j-main-container">
                <?php endif;?>
                <div  class ="box-widget-body ">
                    <div id="container" class ="box-widget-body " style="clear:both;">
                        <div class="<?php echo $row_class;?>">
                            <?php echo J2Store::plugin()->eventWithHtml('BeforeCpanelView'); ?>
                        </div>
                        <div class="<?php echo $row_class;?>">
                            <?php echo J2Store::help()->free_topbar(); ?>
                        </div>
                        <div class="<?php echo $row_class;?>">
                            <div class="<?php echo $col_class;?>12">
                                <?php echo J2Store::help()->alert(
                                    'coupon_update',
                                    JText::_('J2STORE_ATTENTION'),
                                    JText::_('J2STORE_COUPON_TYPES_EXTENDED_NOTIFICATION')
                                ); ?>
                                <?php if(JPluginHelper::isEnabled('system', 'cache')): ?>
                                    <?php echo J2Store::help()->alert_with_static_message(
                                        'danger',
                                        JText::_('J2STORE_ATTENTION'),
                                        JText::_('J2STORE_SYSTEM_CACHE_ENABLED_NOTIFICATION')
                                    ); ?>
                                <?php endif; ?>

                                <?php $content_plugin = JPluginHelper::isEnabled('content', 'socialshare'); ?>
                                <?php if($content_plugin):?>
                                    <?php echo J2Store::help()->alert_with_static_message(
                                        'danger',
                                        JText::_('J2STORE_ATTENTION'),
                                        JText::_('J2STORE_CONTENT_SOCIAL_SHARE_ENABLED_WARNING')
                                    );
                                    ?>
                                <?php endif; ?>

                                <?php if(J2Store::isPro()): ?>

                                    <?php $download_id = J2Store::config()->get('downloadid', ''); ?>
                                    <div id="download-warning">

                                    </div>
                                    <div id="dlid-validate-container" class="alert alert-info" style="display: none;">

                                        <p> <?php echo JText::_('J2STORE_DOWNLOAD_ID_NOT_SET'); ?> </strong><a href="<?php echo J2Store::buildHelpLink('my-downloads.html', 'downloadid'); ?>" target="_blank"><?php echo JText::_('J2STORE_FIND_MY_DOWNLOAD_ID'); ?></a></p>
                                        <p><?php echo JText::_('J2STORE_DOWNLOAD_ID_MESSAGE');?> <a class="btn btn-info" href="<?php echo JRoute::_('index.php?option=com_j2store&view=configuration#updates'); ?>"><?php echo JText::_('J2STORE_ENTER_DOWNLOAD_ID'); ?></a></p>
                                        <p><?php echo JText::_('J2STORE_DOWNLOAD_ID_MESSAGE_AFTER');?></p>
                                    </div>

                                <?php endif; ?>
                                <div class="subscription_message" style="display:none;">
                                    <div class="alert alert-block alert-warning">
                                        <h4>
                                            <span class="subscription"></span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="eupdate-notification" style="display:none;">
                                    <div class="alert alert-block alert-warning">
                                        <h4>
                                            <span class="total"></span>
                                            <?php echo JText::_('J2STORE_PLUGIN_UPDATES_NOTIFICATION'); ?>
                                            <a class="btn btn-danger" href="<?php echo JRoute::_('index.php?option=com_j2store&view=eupdates'); ?>">
                                                <?php echo JText::_('J2STORE_VIEW_AND_UPDATE')?>
                                            </a>
                                        </h4>
                                    </div>
                                </div>
                                <?php echo J2Store::help()->watch_video_tutorials(); ?>
                                <div class="<?php echo $row_class;?>">
                                    <!-- Chart-->
                                    <div class="<?php echo $col_class;?>12 stats-mini">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-1');?>
                                    </div>
                                </div>
                                <div class="<?php echo $row_class;?>">
                                    <!-- Chart-->
                                    <div class="<?php echo $col_class;?>12 chart">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-3');?>
                                    </div>
                                </div>
                                <div class="<?php echo $row_class;?>">
                                    <!-- Statistics-->
                                    <div class="<?php echo $col_class;?>6 statistics">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-5');?>
                                    </div>
                                    <!-- Latest orders -->
                                    <div class="<?php echo $col_class;?>6 latest_orders">
                                        <?php echo J2Store::modules()->loadposition('j2store-module-position-4');?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</div>
    </form>

<?php
$document = JFactory::getDocument();
$platform->addInlineScript('setTimeout(function () {
	(function($){
	$.ajax({
		  url: "index.php?option=com_j2store&view=cpanels&task=getEupdates",
		  dataType:\'json\'
		}).done(function(json) {
			if(json[\'total\']){
				$(\'.eupdate-notification .total\').html(json[\'total\']);
				$(\'.eupdate-notification\').show();
			}
		});

	})(j2store.jQuery);

}, 2000);');
if(J2Store::isPro() && empty($download_id)){
    $platform->addInlineScript('(function($){
	$(document).ready(function() {

		$(\'#dlid-validate-container\').show();

	});
})(j2store.jQuery);');
}
if(J2Store::isPro() && !empty($download_id)){
    $platform->addInlineScript('setTimeout(function () {
	(function($){
	$.ajax({
		  url: "index.php?option=com_j2store&view=cpanels&task=getSubscription",
		  dataType:\'json\'
		}).done(function(json) {
			if(json[\'success\']){
				$(\'.subscription_message .subscription\').html(json[\'success\']);
				$(\'.subscription_message\').show();
				if(json[\'valid\'] == 0) {
					$(\'#dlid-validate-container\').show();
				}
			}
		});

	})(j2store.jQuery);

}, 2000);');
}
$platform->addInlineScript('function validateDlid() {
        (function($){
            var sdlid = $(\'#dlid\').val();
            $(\'#download-warning\').html(\'\');
	        var button = $(\'#dlid-validate-button\');
            $.ajax({
                url: "index.php?option=com_j2store&view=cpanels&task=getDownloadIdStatus&download_id="+sdlid,
                dataType:\'json\',
	            beforeSend: function() {
		            $(button).attr(\'disabled\', \'disabled\');
		            $(button).val(\''.addslashes(JText::_('J2STORE_APPLYING_DLID_PLEASE_WAIT')).'\');

	            }
            }).done(function(json) {
                if(json[\'valid\'] == 1){
                    $(\'#download-warning\').html(\'<div class="alert alert-success">'.JText::_('J2STORE_VAILD_DOWNLOAD_ID').'</div>\');
	                location.reload();
                    //$(\'.subscription_message\').show();
                }else{
	                $(button).removeAttr(\'disabled\');
	                $(button).val(\''.addslashes(JText::_('J2STORE_APPLY_DOWNLOAD_BUTTON')).'\');
                    $(\'#download-warning\').html(\'<div class="alert alert-error">'.JText::_('J2STORE_INVAILD_DOWNLOAD_ID').'</div>\');
                }
            });

        })(j2store.jQuery);
    }');
