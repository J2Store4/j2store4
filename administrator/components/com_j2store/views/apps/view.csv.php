<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewApps extends F0FViewCsv
{
	public function __construct($config = array())
	{
		$app = JFactory::getApplication();
		$model = F0FModel::getTmpInstance('Apps','J2StoreModel');
		$app_id = $app->input->getInt('app_id');
		$row = $model->getItem($app_id);
		$config['csv_filename'] ='app_'.$row->element.'_'.date('dmY').'_'.time().'.csv';
		parent::__construct($config);
	}

	protected function onDisplay($tpl = null)
	{
		// Load the model
		//$model = $this->getModel();
		$app = JFactory::getApplication();
		$platform = F0FPlatform::getInstance();
		$document = $platform->getDocument();
		$model = F0FModel::getTmpInstance('Apps','J2StoreModel');

		$app_id = $app->input->getInt('app_id');
		$row = $model->getItem($app_id);
		JPluginHelper::importPlugin('j2store');
		$results = array();
		$results = $app->triggerEvent('onJ2StoreAppExportCsv', array( $row ) );
		$this->items = array();

		if(!empty($results) ){
			foreach($results as $key => $value){
				$this->items = $value;
			}
		}
		$items = $this->items;

	 	$platform = F0FPlatform::getInstance();
		$document = $platform->getDocument();

		if ($document instanceof JDocument)
		{
			$document->setMimeEncoding('text/csv');
		}

		$platform->setHeader('Pragma', 'public');
        $platform->setHeader('Expires', '0');
        $platform->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $platform->setHeader('Cache-Control', 'public', false);
        $platform->setHeader('Content-Description', 'File Transfer');
        $platform->setHeader('Content-Disposition', 'attachment; filename="' . $this->csvFilename . '"');

		if (is_null($tpl))
		{
			$tpl = 'csv';
		}

		F0FPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');

		$hasFailed = false;

		try
		{
			$result = $this->loadTemplate($tpl, true);

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

			if (!empty($this->csvFields))
			{
				$temp = array();

				foreach ($this->csvFields as $f)
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
	}
}