<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2storeControllerShippings extends F0FController
{
    use list_view;
	public function __construct($config) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
 		/* $task = JFactory::getApplication()->input->getString('task');
		if($task=='view') $this->view(); */
	}
    public function browse()
    {
        $app = JFactory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['name'] = $app->input->getString('name','');
        $state['filter_order']= $app->input->getString('filter_order','extension_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $this->toolbarBacktodashboard();
        $vars->state = $model->getState();


        $header = array(
            'extension_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_SHIPPING_ID'
            ),
            'name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' =>'true',
                'label' => 'J2STORE_SHIPPING_PLUGIN_NAME'
            ),
            'version' => array(
                'type' => 'field',
                'filterclass' => 'input-small',
                'label' => 'J2STORE_PLUGIN_VERSION'
            ),
            'view' => array(
                'type' => 'shipping_link',
                'show_link' => 'true' ,
                'label' => 'J2STORE_VIEW',
                'translate' => true
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )

        );

        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        $vars->extra_content = $this->_getLayout('shipping_info',$vars);
        echo $this->_getLayout('default',$vars);
    }

	public function execute($task)
	{
		$app = JFactory::getApplication();
		 $shippingTask = $app->input->getCmd('shippingTask', '');
		$values = $app->input->getArray($_POST);
		// Check if we are in a shipping method view. If it is so,
		// Try lo load the shipping plugin controller (if any)
		if ( $task  == "view" && $shippingTask != '' )
		{
			$model = $this->getModel('Shippings');
			$id = $app->input->getInt('id', '0');
			if(!$id)
				parent::execute($task);

			$model->setId($id);
			// get the data
			// not using getItem here to enable ->checkout (which requires JTable object)
			$row = $model->getTable();
			$row->load( (int) $model->getId() );
			$element = $row->element;

			// The name of the Shipping Controller should be the same of the $_element name,
			// without the shipping_ prefix and with the first letter Uppercase, and should
			// be placed into a controller.php file inside the root of the plugin
			// Ex: shipping_standard => J2StoreControllerShippingStandard in shipping_standard/controller.php
			$controllerName = str_ireplace('shipping_', '', $element);
			$controllerName = ucfirst($controllerName);
			$path = JPATH_SITE.'/plugins/j2store/';
			$controllerPath = $path.$element.'/'.$element.'/controller.php';
			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}

			$className    = 'J2StoreControllerShipping'.$controllerName;


			if ($controllerName != '' && class_exists($className)){

				// Create the controller
				$controller   = new $className( );
				// Add the view Path
				$controller->addViewPath($path);
				// Perform the requested task
				$controller->execute( $shippingTask );

				// Redirect if set by the controller
				$controller->redirect();

			} else{
				parent::execute($task);
			}
		} else{

			parent::execute($task);
		}
	}

	 function view()
	    {
	    	$model = $this->getModel( 'shippings' );
	    	$id = $this->input->getInt('id');
	    	$row = $model->getItem($id);
	    	$view   = $this->getThisView( 'shippings');
	    	$view->setModel( $model, true );
	    	$view->set('item', $row );
	    	$view->setLayout( 'view' );
	    	$view->display();
	}

}