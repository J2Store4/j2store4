<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;

class J2StoreToolbar extends F0FToolbar
{
	public function j2storeHelperRenderSubmenu($vName)
	{

		return $this->renderSubmenu($vName);
	}

	public function renderSubmenu($vName = null)
	{
		$app = JFactory::getApplication();
		if(is_null($vName)) {
			$vName = $this->input->getCmd('view','cpanels');
		}

		$this->input->set('view', $vName);

    	//parent::renderSubmenu(); //render menubar tab
		$views = array(
				'cpanel',
				'COM_J2STORE_MAINMENU_CATALOG' => array(
						array('name'=>'products','icon'=>'fa fa-tags'),
						array('name'=>'options','icon'=>'fa fa-list-ol'),
						array('name'=>'filtergroups','icon'=>'fa fa-list-ol'),
						array('name'=>'vendors','icon'=>'fa fa-list-ol'),
						array('name'=>'manufacturers','icon'=>'fa fa-list-ol'),

				),
				'COM_J2STORE_MAINMENU_SALES' => array(
						array('name'=>'orders','icon'=>'fa fa-list-ol'),
						array('name'=>'customers','icon'=>'fa fa-list-ol'),
						array('name'=>'coupons','icon'=>'fa fa-list-ol'),
						array('name'=>'vouchers','icon'=>'fa fa-list-ol'),
						array('name'=>'promotions','icon'=>'fa fa-list-ol'),
				),


				'COM_J2STORE_MAINMENU_LOCALISATION' => array(
						array('name'=>'countries','icon'=>'fa fa-list-ol'),
						array('name'=>'zones','icon'=>'fa fa-list-ol'),
						array('name'=>'geozones','icon'=>'fa fa-list-ol'),
						array('name'=>'taxrates','icon'=>'fa fa-list-ol'),
						array('name'=>'taxprofiles','icon'=>'fa fa-list-ol'),
						array('name'=>'lengths','icon'=>'fa fa-list-ol'),
						array('name'=>'weights','icon'=>'fa fa-list-ol'),
						array('name'=>'orderstatuses','icon'=>'fa fa-list-ol'),
				),
				'COM_J2STORE_MAINMENU_DESIGN' => array(
						array('name'=>'emailtemplates','icon'=>'fa fa-list-ol'),
						array('name'=>'invoicetemplates','icon'=>'fa fa-list-ol'),
				),
				'COM_J2STORE_MAINMENU_SETUP' => array(
					array('name'=>'configuration','icon'=>'fa fa-list-ol'),
					array('name'=>'currencies','icon'=>'fa fa-list-ol'),
					array('name'=>'payments','icon'=>'fa fa-list-ol'),
					array('name'=>'shippings','icon'=>'fa fa-truck'),
					array('name'=>'shippingtroubles','icon'=>'fa fa-bug'),						
					array('name'=>'customfields','icon'=>'fa fa-list-ol'),
				),
				'J2STORE_MAINMENU_APPLICATIONS' => array(
					array('name'=>'apps','icon'=>'fa fa-tools'),
				),
				'J2STORE_MAINMENU_REPORT' => array(
					array('name'=>'reports','icon'=>'fa fa-list-ol')
				),

		);

		foreach($views as $label => $view) {
			if(!is_array($view)) {
				$this->addSubmenuLink($view);
			} else {
				$label = JText::_($label);
				$this->appendLink($label, '', false);
				foreach($view as $v) {
					$this->addSubmenuLink($v['name'], $label, $v['icon']);
					//	$this->renderCategorySubmenu($v);

				}
			}
		}
	}

	private function addSubmenuLink($view, $parent = null, $icon=null)
	{
		static $activeView = null;
		if(empty($activeView)) {
			$activeView = $this->input->getCmd('view','cpanel');
		}

		if ($activeView == 'cpanels')
		{
			$activeView = 'cpanel';
		}

		$key = strtoupper($this->component).'_TITLE_'.strtoupper($view);
		if(strtoupper(JText::_($key)) == $key) {
			$altview = F0FInflector::isPlural($view) ? F0FInflector::singularize($view) : F0FInflector::pluralize($view);
			$key2 = strtoupper($this->component).'_TITLE_'.strtoupper($altview);
			if(strtoupper(JText::_($key2)) == $key2) {
				$name = ucfirst($view);
			} else {
				$name = JText::_($key2);
			}
		} else {
			$name = JText::_($key);
		}

		$link = 'index.php?option='.$this->component.'&view='.$view;

		$active = $view == $activeView;

		$this->appendLink($name, $link, $active, $icon, $parent);
	}

	public function onCpanelsBrowse()
	{
		$this->renderSubmenu();

		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		JToolBarHelper::preferences($option, 550, 875);
	}

	public function onPostconfigsBrowse()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
	}

	public function onProductsBrowse(){

		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		// Set toolbar icons
		JToolbarHelper::addNew('create');

		if ($this->perms->delete)
		{
			$msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
			JToolBarHelper::deleteList(strtoupper($msg));
		}

	}

	public function onProductRead()
	{

	}

	public function onCouponsBrowse() {
		if(J2Store::isPro()) {
			parent::onBrowse();
		}else {
			$this->noToolbar();
		}
	}

	public function onVouchersBrowse()
	{
		if(J2Store::isPro()) {
			parent::onBrowse();
			JToolbarHelper::custom('history','list','',JText::_('J2STORE_VOUCHER_HISTORY'));
			JToolbarHelper::custom('send','mail','',JText::_('J2STORE_VOUCHER_SEND'));
		}else {
			$this->noToolbar();
		}
	}

	public function onVouchersEdit()
	{
		if(J2Store::isPro()) {
			parent::onEdit();
			JToolbarHelper::save2copy ('copy');
		}else {
			$this->noToolbar();
		}
	}

	public function onVouchersHistory()
	{
		if(J2Store::isPro()) {
			$bar = JToolBar::getInstance('toolbar');
			// Add "Export to CSV"
			$link = JURI::getInstance();
			$query = $link->getQuery(true);
			$query['option'] = 'com_j2store';
			$query['view'] = 'vouchers';
			$link->setQuery($query);

			JToolBarHelper::divider();
			$icon = 'arrow-left';
			$bar->appendButton('Link', $icon, JText::_('J2STORE_BACK'), $link->toString());

		}else {
			$this->noToolbar();
		}
	}


	public function onVendorsBrowse()
	{
		if(J2Store::isPro()) {
			parent::onBrowse();
			$this->exportButton('vendors');
		}else {
			$this->noToolbar();
		}
	}

	public function onManufacturersBrowse() {
		parent::onBrowse();
		$this->exportButton('manufacturers');
	}

	public function oninventoriesBrowse()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
	}

	public function onEmailtemplatesBrowse() {
		if(J2Store::isPro()) {
			parent::onBrowse();
		}else {
			$this->noToolbar();
		}
	}

	public function onEmailtemplatesEdit() {
		if(J2Store::isPro()) {
			parent::onEdit();
			$bar = JToolBar::getInstance('toolbar');
			$bar->appendButton('Standard','mail' , JText::_('J2STORE_EMAILTEMPLATE_SEND_TEST_EMAIL_TO_YOURSELF'), 'sendtest', false);

		}else {
			$this->noToolbar();
		}
	}

	public function onInvoicetemplatesBrowse() {
		if(J2Store::isPro()) {
			parent::onBrowse();
		}else {
			$this->noToolbar();
		}
	}

	public function onCustomersBrowse()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		JToolBarHelper::deleteList();
		$this->exportButton('customers');

	}

	public function onConfigurationsAdd()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}
		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_EDIT';
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);

		// Set toolbar icons
		if ($this->perms->edit || $this->perms->editown)
		{
			// Show the apply button only if I can edit the record, otherwise I'll return to the edit form and get a
			// 403 error since I can't do that
			JToolBarHelper::apply();
		}
		JToolBarHelper::save();
		JToolBarHelper::cancel();
	}

	public function onPromotionsBrowse()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		// Set toolbar icons

		JToolBarHelper::back(JText::_('J2STORE_BACK_TO_DASHBOARD'), 'index.php?option=com_j2store&view=cpanel');

	}

	public function onAppsBrowse()
	{
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		// Set toolbar icons

		JToolBarHelper::back(JText::_('J2STORE_BACK_TO_DASHBOARD'), 'index.php?option=com_j2store&view=cpanel');

	}
	public function onPaymentsBrowse(){

		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		// Set toolbar icons
		JToolBarHelper::back(JText::_('J2STORE_BACK_TO_DASHBOARD'), 'index.php?option=com_j2store&view=cpanel');
	}

 	public function onShippingsBrowse(){
 		$option = $this->input->getCmd('option', 'com_foobar');
 		$componentName = str_replace('com_', '', $option);
 		// On frontend, buttons must be added specifically
 		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
 		{
 			$this->renderSubmenu();
 		}

 		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
 		{
 			return;
 		}
 		// Set toolbar title
 		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
 		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
 		// Set toolbar icons
 		JToolBarHelper::back(JText::_('J2STORE_BACK_TO_DASHBOARD'), 'index.php?option=com_j2store&view=cpanel');
	}

	public function onShippingMethodsBrowse(){
		$app = JFactory::getApplication();
		$shippingTask = $app->input->getString('shippingTask');
		if(!isset($shippingTask) && !($shippingTask =='view' || $shippingTask =='newMethod')){
			parent::onBrowse();

		}else{
			JToolbarHelper::apply();
			JToolbarHelper::save();
			JToolbarHelper::cancel();

		}
	}
	
	public function onShippingtroublesBrowse(){
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
	}
	public function onOrdersEdit(){

		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		$id = $this->input->get('id',0);

		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}

		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_EDIT';
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		//JText::_('J2STORE_CLOSE')
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'cancel','J2STORE_CLOSE','index.php?option=com_j2store&view=orders');
		//JToolBarHelper::cancel('cancel',  );
		if(JFactory::getUser()->authorise('core.edit',$option)){

			$bar->appendButton( 'Link', 'edit', 'JTOOLBAR_EDIT', 'index.php?option=com_j2store&view=orders&task=createOrder&oid='.$id );
		}

	}

	public function onOrdersBrowse(){
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		if(F0FPlatform::getInstance()->isBackend()) {
			JToolbarHelper::addNew('createOrder','JTOOLBAR_NEW');
			if(JFactory::getUser()->authorise('core.edit',$option)){
				JToolbarHelper::editList('createOrder', 'JTOOLBAR_EDIT');
			}
		}

		if ($this->perms->delete)
		{
			$msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
			JToolBarHelper::deleteList(strtoupper($msg));
		}
		//JToolBarHelper::back('JTOOLBAR_EXPORT', 'index.php?option=com_j2store&view=orders&format=csv')
		//JToolBarHelper::custom('exportOrders','icon icon-download', $iconOver = '', $alt = 'JTOOLBAR_EXPORT',$listSelect=false);

		$bar = JToolBar::getInstance('toolbar');
		// Add "Export to CSV"
		$link = JURI::getInstance();
		$query = $link->getQuery(true);
		$query['format'] = 'csv';
		$query['option'] = 'com_j2store';
		$query['view'] = 'orders';
		$query['task'] = 'browse';
		$link->setQuery($query);

		JToolBarHelper::divider();
		$icon = 'download';
		$bar->appendButton('Link', $icon, JText::_('JTOOLBAR_EXPORT'), $link->toString());
	}
	public function onOrdersCreateOrder(){
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		//block multiple toolbar display
		// here we use shipping and items view so we use static variable to block multiple toolbar
		static $is_toolbar_absent = true;
		if($is_toolbar_absent){
			$is_toolbar_absent = false;
			$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_CREATE_ORDER';
			JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
			JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_j2store&view=orders');
			if(J2Store::isPro() == 1) {
				JToolBarHelper::cancel('cancel', JText::_('J2STORE_CLOSE') );
				JToolbarHelper::apply('saveAdminOrder');
			}

		}
	}
	public function onReportsView(){
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		JToolBarHelper::cancel();
	}

	public function onReportsBrowse(){
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		// Set toolbar icons
		$msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
		JToolBarHelper::deleteList(strtoupper($msg));
		JToolbarHelper::publish();
		JToolbarHelper::unpublish();
		JToolBarHelper::back();
	}


	public function onAppsView(){
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
		JToolBarHelper::cancel();
	}

	private function noToolbar() {
		$option = $this->input->getCmd('option', 'com_foobar');
		$componentName = str_replace('com_', '', $option);
		// On frontend, buttons must be added specifically
		if (F0FPlatform::getInstance()->isBackend() || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!F0FPlatform::getInstance()->isBackend() && !$this->renderFrontendButtons)
		{
			return;
		}
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . F0FInflector::pluralize($this->input->getCmd('view', 'cpanel')));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), $componentName);
	}

	private function exportButton($view) {
		if(!isset($view) || empty($view)) return;
		// Add "Export to CSV"
		$bar = JToolBar::getInstance('toolbar');
		$link = JURI::getInstance();
		$query = $link->getQuery(true);
		$query['format'] = 'csv';
		$query['option'] = 'com_j2store';
		$query['view']   = $view;
		$query['task']   = 'browse';
		$link->setQuery($query);
		JToolBarHelper::divider();
		$icon = version_compare(JVERSION, '3.0', 'lt') ? 'export' : 'download';
		$bar->appendButton('Link', $icon, JText::_('J2STORE_EXPORTCSV'), $link->toString());
	}


	public function onCouponsEdit(){
		if(J2Store::isPro()) {
			$app = JFactory::getApplication();
			parent::onEdit();
			JToolBarHelper::divider();
			JToolbarHelper::save2copy ('copy');
			$bar = JToolBar::getInstance('toolbar');
			$bar->appendButton('Link', 'list', JText::_('J2STORE_COUPON_HISTORY'), 'index.php?option=com_j2store&view=coupon&task=history&coupon_id='.$app->input->getInt('id'));
		}else {
			$this->noToolbar();
		}
	}

	public function onCouponsHistory(){
		if(J2Store::isPro()) {
			$this->noToolbar();
			$app = JFactory::getApplication();
			$bar = JToolBar::getInstance('toolbar');
			// Add "Export to CSV"
			$link = JURI::getInstance();
			$query = $link->getQuery(true);
			$query = array(
					       'option' => 'com_j2store',
						   'view' => 'coupon',
						   'task' => 'edit',
						   'id' => $app->input->getInt('coupon_id',0)
					);
			$link->setQuery($query);
			JToolBarHelper::divider();
			$icon = 'arrow-left';
			$bar->appendButton('Link', $icon, JText::_('J2STORE_BACK'), $link->toString());
		}else {
			$this->noToolbar();
		}


	}
}
