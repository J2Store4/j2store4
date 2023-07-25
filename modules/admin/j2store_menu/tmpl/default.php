<?php
/*------------------------------------------------------------------------
# mod_j2store_menu
# ------------------------------------------------------------------------
# author    Gokila Priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$platform = J2Store::platform();
$platform->addStyle('j2store-font-awesome-css','/media/j2store/css/font-awesome.min.css');
$platform->addInlineStyle('ul.nav.j2store-admin-menu > li ul { overflow: visible; }');
//$doc = JFactory::getDocument();
//$doc->addStyleSheet(JUri::root().'media/j2store/css/font-awesome.min.css');
//$doc->addStyleDeclaration('ul.nav.j2store-admin-menu > li ul { overflow: visible; }');
if(version_compare(JVERSION,'3.99.99','ge')){
	$platform->addStyle('j2store-menu-module','/administrator/modules/mod_j2store_menu/css/j2store_module_menu.css');
    //$doc->addStyleSheet(rtrim(JUri::root(),'/').'/administrator/modules/mod_j2store_menu/css/j2store_module_menu.css');
}

$icons = array (
		'dashboard' => 'fa fa-th-large',
		'COM_J2STORE_MAINMENU_CATALOG' => 'fa fa-tags',
		'products' => 'fa fa-tags',
		'options' => 'fa fa-list-ol',
		'vendors' => 'fa fa-male',
		'manufacturers' => 'fa fa-user',
		'filtergroups' => 'fa fa-filter',
		'COM_J2STORE_MAINMENU_SALES' => '',
		'orders' => 'fa fa-list-alt',
		'customers' => 'fa fa-users',
		'coupons' => 'fa fa-scissors',
		'promotions' => 'fa fa-trophy',
		'vouchers' => 'fa fa-gift',
		'COM_J2STORE_MAINMENU_LOCALISATION' => '',
		'countries' => 'fa fa-globe',
		'zones' => 'fa fa-flag',
		'geozones' => 'fa fa-pie-chart',
		'taxrates' => 'fa fa-calculator',
		'taxprofiles' => 'fa fa-sitemap',
		'lengths' => 'fa fa-arrows-v',
		'weights' => 'fa fa-arrows-h',
		'orderstatuses' => 'fa fa-check-square',
		'COM_J2STORE_MAINMENU_DESIGN' => '',
		'layouts' => 'fa fa-list-ol',
		'emailtemplates' => 'fa fa-envelope',
		'invoicetemplates' => 'fa fa-print',

		'COM_J2STORE_MAINMENU_SETUP' => '',
		'storeprofiles' => 'fa fa-edit',
		'currencies' => 'fa fa-dollar',
		'payments' => 'fa fa-credit-card',
		'shippings' => 'fa fa-truck',
		'reports' => 'fa fa-signal',
		'customfields' => 'fa fa-th-list',
		'configuration' => 'fa fa-cogs',
		'J2STORE_MAINMENU_APPLICATIONS'=>'',
		'apps' => 'fa fa-wrench'
);


$menus = array (
		array (
				'name' => 'Dashboard',
				'icon' => 'fa fa-th-large',
				'active' => 1
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_CATALOG' ),
				'icon' => 'fa fa-tags',
				'submenu' => array (
						'products' => 'fa fa-tags',
						'inventories' => 'fa fa-database',
						'options' => 'fa fa-list-ol',
						'vendors' => 'fa fa-male',
						'manufacturers' => 'fa fa-user',
						'filtergroups' => 'fa fa-filter'
				)
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_SALES' ),
				'icon' => 'fa fa-money',
				'submenu' => array (
						'orders' => 'fa fa-list-alt',
						'customers' => 'fa fa-users',
						'coupons' => 'fa fa-scissors',
						'vouchers' => 'fa fa-gift'
				)
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_LOCALISATION' ),
				'icon' => 'fa fa-globe fa-lg',
				'submenu' => array (
						'countries' => 'fa fa-globe',
						'zones' => 'fa fa-flag',
						'geozones' => 'fa fa-pie-chart',
						'taxrates' => 'fa fa-calculator',
						'taxprofiles' => 'fa fa-sitemap',
						'lengths' => 'fa fa-arrows-v',
						'weights' => 'fa fa-arrows-h',
						'orderstatuses' => 'fa fa-check-square'
				)
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_DESIGN' ),
				'icon' => 'fa fa-paint-brush',
				'submenu' => array (
						'emailtemplates' => 'fa fa-envelope',
						'invoicetemplates' => 'fa fa-print'
				)
		),

		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_SETUP' ),
				'icon' => 'fa fa-cogs',
				'submenu' => array (
						'configuration' => 'fa fa-cogs',
						'currencies' => 'fa fa-dollar',
						'payments' => 'fa fa-credit-card',
						'shippings' => 'fa fa-truck',
						'shippingtroubles' => 'fa fa-bug',
						'customfields' => 'fa fa-th-list',
				)
		),
		array (
				'name' => 'Apps',
				'icon' => 'fa fa-wrench',
				'active' => 0
		),

		array (
				'name' => 'Reporting',
				'icon' => 'fa fa-signal',
				'submenu' => array (
						'Reports' => 'fa fa-signal'
				)
		)
);
?>
<?php if (version_compare(JVERSION, '3.99.99', 'lt')) :?>
<ul id="menu" class="nav j2store-admin-menu">
	<li class="dropdown" >
		<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo JText::_('COM_J2STORE');?><span class="caret"></span></a>
			<ul aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu">
			<?php foreach($menus as $key => $value):?>
                  <?php if(isset($value['submenu']) && count($value['submenu'])):?>
                  <li class="dropdown-submenu">
                    <a href="#" tabindex="-1">
                    	<i class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i>
                    	<span class="submenu-title"><?php echo $value['name'];?></span>
                    </a>
                    <ul class="dropdown-menu">

                    <!-- Here starts Submenu -->
                     <?php foreach($value['submenu'] as $key => $value): ?>
                      	<li>
                      		<a href="<?php echo 'index.php?option=com_j2store&view='.strtolower($key);?>"  tabindex="-1">
                      			<i class="<?php echo !empty($value) ? $value: '';?>"></i>
                      			<span>
	                           		<?php echo JText::_('COM_J2STORE_TITLE_'.strtoupper($key));?>
	                           	</span>
	                         </a>
	                       </li>
                         <?php endforeach;?>
                    </ul>
                  </li>
                 <?php else:?>
                  <li>
                      <?php
	           	 		if($value['name']=='Dashboard'):?>
							<a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo 'index.php?option=com_j2store&view=cpanels';?>">
						<?php elseif($value['name']=='Apps'): ?>
							<a href="<?php echo 'index.php?option=com_j2store&view=apps';?>">
						<?php else:?>
							<a href="javascript:void(0);">
						<?php endif;?>
						<i class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i>
							<span class="submenu-title"><?php echo JText::_('COM_J2STORE_MAINMENU_'.$value['name']);?></span>
						</a>
					</li>
                <?php endif; ?>
               <?php endforeach;?>
			</ul>
	</li>
</ul>
<?php else: ?>
    <?php \Joomla\CMS\HTML\HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');?>
<div class="header-item-content dropdown header-profile">
    <button class="dropdown-toggle d-flex align-items-center ps-0 py-0" data-bs-toggle="dropdown" type="button"
            title="<?php echo JText::_('COM_J2STORE'); ?>">
        <div class="header-item-icon">
            <span class="icon-th-large" aria-hidden="true"></span>
        </div>
        <div class="header-item-text">
            <?php echo JText::_('COM_J2STORE'); ?>
        </div>
        <span class="icon-angle-down" aria-hidden="true"></span>
    </button>
    <div id="j2menu" class="dropdown-menu dropdown-menu-end">
        <?php foreach($menus as $key => $value): ?>
            <?php if(isset($value['submenu']) && count($value['submenu'])):?>
                <a   class="dropdown-item j2submenu" href="#">
                    <span class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>" aria-hidden="true"></span>
                    <?php echo $value['name'];?>
                </a>
        <div class="j2submenu-list dropdown-menu dropdown-menu-end">
            <?php foreach($value['submenu'] as $sub_key => $sub_value): ?>
                <a class="dropdown-item" href="<?php echo JRoute::_('index.php?option=com_j2store&view='.strtolower($sub_key)); ?>">
                    <span class="<?php echo isset($sub_value) ? $sub_value : '';?>" aria-hidden="true"></span>
                    <?php echo JText::_('COM_J2STORE_TITLE_'.strtoupper($sub_key));?>
                </a>
            <?php endforeach;?>
        </div>
            <?php else:?>
            <?php $url = 'javascript:void(0);';
            if($value['name']=='Dashboard'){
                $url = JRoute::_('index.php?option=com_j2store&view=cpanels');
            }elseif($value['name']=='Apps') {
                $url = JRoute::_('index.php?option=com_j2store&view=apps');
            }
            ?>
            <a class="dropdown-item" href="<?php echo $url; ?>">
                <span class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>" aria-hidden="true"></span>
                <?php echo JText::_('COM_J2STORE_MAINMENU_'.$value['name']); ?>
            </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<script>
    var dropdowns = document.querySelectorAll('.j2submenu')
    var width = screen.width;
    dropdowns.forEach((dd)=>{
        dd.addEventListener('mouseover', function (e) {
            var rect = document.getElementById("j2menu").getBoundingClientRect();
            var el = this.nextElementSibling
            if(rect.x > 1000){
                el.style.class = el.classList.add("j2right");
            }else{
                el.style.class = el.classList.add("j2left");
            }
            el.style.class = el.classList.add("show");
        });
        dd.addEventListener('touchstart', function (e) {
            var rect = document.getElementById("j2menu").getBoundingClientRect();
            var el = this.nextElementSibling
            if(rect.x > 1000){
                el.style.class = el.classList.add("j2right");
            }else{
                el.style.class = el.classList.add("j2left");
            }
            el.style.class = el.classList.add("show");
        });
        dd.addEventListener('mouseout', function (e) {
                var el = this.nextElementSibling
                el.style.class = el.classList.remove("show");
        });
        dd.addEventListener('touchend', function (e) {
            var el = this.nextElementSibling
            el.style.class = el.classList.remove("show");
        });
    });
</script>
<?php endif; ?>
