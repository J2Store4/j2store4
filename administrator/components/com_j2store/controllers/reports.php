<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2storeControllerReports extends F0FController
{
    public $csvHeader = true;
    use list_view;
	public function __construct($config) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
 		/* $task = JFactory::getApplication()->input->getString('task');
		if($task=='view') $this->view(); */
	}

	public function execute($task)
	{
		$app = JFactory::getApplication();
		$reportTask = $app->input->getCmd('reportTask', '');
		$values = $app->input->getArray($_POST);
		// Check if we are in a report method view. If it is so,
		// Try lo load the report plugin controller (if any)
		if ( $task  == "view" && $reportTask != '' )
		{
			$model = $this->getModel('Reports');

			 $id = $app->input->getInt('id', '0');

			if(!$id)
				parent::execute($task);

			$model->setId($id);

			// get the data
			// not using getItem here to enable ->checkout (which requires JTable object)
			$row = $model->getTable();
			$row->load( (int) $model->getId() );
			$element = $row->element;

			// The name of the Report Controller should be the same of the $_element name,
			// without the report_ prefix and with the first letter Uppercase, and should
			// be placed into a controller.php file inside the root of the plugin
			// Ex: report_standard => J2StoreControllerReportStandard in report_standard/controller.php
			$controllerName = str_ireplace('report_', '', $element);
			$controllerName = ucfirst($controllerName);

			$path = JPATH_SITE.'/plugins/j2store/';


			$controllerPath = $path.$element.'/'.$element.'/controller.php';

			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}

			$className    = 'J2StoreControllerReport'.$controllerName;

			if ($controllerName != '' && class_exists($className)){

				// Create the controller
				$controller   = new $className();

				// Add the view Path
				$controller->addViewPath($path);

				// Perform the requested task
				$controller->execute( $reportTask );

				// Redirect if set by the controller
				$controller->redirect();

			} else{
				parent::execute($task);
			}
		} elseif(isset($values['format']) && $values['format'] == 'csv'){
            $view = $this->getThisView();
            $platform = F0FPlatform::getInstance();
            $document = $platform->getDocument();
            $model = J2Store::fof()->getModel('Reports','J2StoreModel');
            $report_id = $app->input->getInt('report_id');
            $row = $model->getItem($report_id);
            $csvFilename = 'report_'.$row->element.'_'.date('Y-m-d').'_'.time().'.csv';
            JPluginHelper::importPlugin('j2store');
            $results = $app->triggerEvent('onJ2StoreGetReportExported', array( $row ) );
            $items = $results[0];
            if ($document instanceof JDocument)
            {
                $document->setMimeEncoding('text/csv');
            }

            $platform->setHeader('Pragma', 'public');
            $platform->setHeader('Expires', '0');
            $platform->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $platform->setHeader('Cache-Control', 'public', false);
            $platform->setHeader('Content-Description', 'File Transfer');
            $platform->setHeader('Content-Disposition', 'attachment; filename="' . $csvFilename . '"');

            $tpl = null;
            if (is_null($tpl))
            {
                $tpl = 'csv';
            }

            F0FPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');

            $hasFailed = false;

            try
            {
                $result = $view->loadTemplate($tpl, true);

                if ($result instanceof Exception)
                {
                    $hasFailed = true;
                }
            }
            catch (Exception $e)
            {
                $hasFailed = true;
            }

            if (!$hasFailed)
            {
                echo $result;
            }
            else
            {
                // Default CSV behaviour in case the template isn't there!

                if (empty($items))
                {
                    return;
                }

                $item    = array_pop($items);
                $keys    = get_object_vars($item);
                $keys    = array_keys($keys);
                $items[] = $item;

                reset($items);

                $max = 1;
                //which item having more fields that will be csv fields
                foreach($items as $item) {

                    $order_field_count = count(get_object_vars($item));
                    if($order_field_count > $max) {
                        $max = $order_field_count;
                        $headeritem = $item;
                    }
                }
                $keys    = get_object_vars($headeritem);
                $keys    = array_keys($keys);
                $csvFields = array();
                if (!empty($csvFields))
                {
                    $temp = array();

                    foreach ($csvFields as $f)
                    {
                        if (in_array($f, $keys))
                        {
                            $temp[] = $f;
                        }
                    }

                    $keys = $temp;
                }

                if ($this->csvHeader)
                {
                    $csv = array();

                    foreach ($keys as $k)
                    {
                        $k = str_replace('"', '""', $k);
                        $k = str_replace("\r", '\\r', $k);
                        $k = str_replace("\n", '\\n', $k);
                        $k = '"' . $k . '"';

                        $csv[] = $k;
                    }

                    echo implode(",", $csv) . "\r\n";
                }

                foreach ($items as $item)
                {

                    $csv  = array();
                    $item = (array) $item;

                    foreach ($keys as $k)
                    {

                        if (!isset($item[$k]))
                        {
                            $v = '';
                        }
                        else
                        {
                            $v = $item[$k];
                        }

                        if (is_array($v))
                        {
                            $v = 'Array';
                        }
                        elseif (is_object($v))
                        {
                            $v = 'Object';
                        }

                        $v = str_replace('"', '""', $v);
                        $v = str_replace("\r", '\\r', $v);
                        $v = str_replace("\n", '\\n', $v);
                        $v = '"' . $v . '"';

                        $csv[] = $v;
                    }

                    echo implode(",", $csv) . "\r\n";
                }

            }
            return false;
        }else{
            parent::execute($task);
        }
	}

	protected function onBeforeBrowse()
	{
		if(!$this->checkACL('j2store.reports'))
		{
			return false;
		}
		return parent::onBeforeBrowse();
	}
    function browse()
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
        $vars->state = $model->getState();
        $option = $app->input->getCmd('option', 'com_foobar');
        $subtitle_key = strtoupper($option . '_TITLE_' . $app->input->getCmd('view', 'cpanel'));
        JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), str_replace('com_', '', $option));
        // Set toolbar icons
        $msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
        JToolBarHelper::deleteList(strtoupper($msg));
        JToolbarHelper::publish();
        JToolbarHelper::unpublish();
        JToolBarHelper::back();
        $header = array(
            'extension_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_REPORT_ID'
            ),
            'name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=reports&amp;task=view&amp;layout=view&amp;id=[ITEM:ID]",
                'url_id' => 'extension_id',
                'label' => 'J2STORE_REPORT_PLUGIN_NAME'
            ),
            'version' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_PLUGIN_VERSION'
            ),
            'view' => array(
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=reports&amp;task=view&amp;layout=view&amp;id=[ITEM:ID]",
                'url_id' => 'extension_id',
                'label' => 'J2STORE_VIEW'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

    function view(){

		    if(!$this->checkACL('j2store.reports'))
		    {
			 	return false;
		    }

	    	$model = $this->getThisModel();
	    	$id = $this->input->getInt('id');
	    	$row = $model->getItem($id);
	    	$view   = $this->getThisView('Report');
	    	$view->setModel( $model, true );
	    	$view->set('row', $row );
	    	$view->setLayout( 'view' );
	    	$view->display();
	    }

}