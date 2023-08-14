<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
class J2StoreModelOrderdownloads extends F0FModel {

	public function buildQuery($overrideLimits = false) {

		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('#__j2store_orderdownloads.*')->from('#__j2store_orderdownloads');

		$order_id = $this->getState('order_id', null);
		$token = $this->getState('token', null);
		$email = $this->getState('email', null);
		$product_id = $this->getState('product_id', null);

		//products
		$query->select($this->_db->qn('#__j2store_products.params').' AS product_params');
		$query->join('INNER', '#__j2store_products ON #__j2store_products.j2store_product_id=#__j2store_orderdownloads.product_id');

		//orders
		$query->select($this->_db->qn('#__j2store_orders.order_state_id'));
		$query->select($this->_db->qn('#__j2store_orders.token'));

		$query->join('INNER', '#__j2store_orders ON #__j2store_orders.order_id=#__j2store_orderdownloads.order_id');

		$valid_statuses = $this->getValidOrderStatuses();
		$query->where($this->_db->qn('#__j2store_orders.order_state_id').' IN ( '.implode(',', $valid_statuses).' )');

		if(!empty($email)) {
			$query->where($this->_db->qn('#__j2store_orderdownloads.user_email').' = '.$this->_db->q($email));
		}

		if($order_id) {
			$query->where($this->_db->qn('#__j2store_orderdownloads.order_id').' = '.$this->_db->q($order_id));
		}

		if($product_id) {
			$query->where($this->_db->qn('#__j2store_orderdownloads.product_id').' = '.$this->_db->q($product_id));
		}

		if(!empty($token)) {
			$query->where($this->_db->qn('#__j2store_orders.token').' = '.$this->_db->q($token));
		}
		return $query;
	}

	public function &getItemList($overrideLimits=false, $group='') {
		$items = array();
		try {
			$items = parent::getItemList($overrideLimits, $group);
		} catch (Exception $e) {
			JLog::add($e->getMessage(), 128);
			echo $e->getMessage();
			return $items;
		}

		foreach($items as &$item) {
			$item->download_limit = $this->getDownloadLimit($item->product_params);
			$item->files = F0FModel::getTmpInstance('ProductFiles', 'J2StoreModel')->product_id($item->product_id)->getList();
		}
		return $items;
	}

	public function getDownloadLimit($product_params) {
        if(empty($product_params)){
            return 0;
        }
		$registry = J2Store::platform()->getRegistry($product_params);
		return $registry->get('download_limit');
	}

	public function setDownloads($order, $override_status=false) {
		if($override_status) {
			if(!$this->isStatusValid($order->order_state_id)) return;
		}

		$model = $this->getModel ();
		$downloads = $model->order_id ( $order->order_id )->email($order->user_email)->getList ();

		foreach ( $downloads as $download ) {

			if ($download->access_granted == JFactory::getDbo ()->getNullDate ()) {
				unset($table);
				$table = F0FTable::getAnInstance ( 'Orderdownload', 'J2StoreTable' )->getClone();

				if ($table->load ( $download->j2store_orderdownload_id )) {

					$tz = JFactory::getConfig ()->get ( 'offset' );
					$date = JFactory::getDate ( 'now', $tz );
					$table->access_granted = $date->toSql ( true );

					$product = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' )->getClone();
					$product->load ( $table->product_id );

					$access_expires = JFactory::getDbo ()->getNullDate ();
					if (! empty ( $product->params )) {
						$registry = J2Store::platform()->getRegistry($product->params);
						$expires = $registry->get ( 'download_expiry', '' );

						if (! empty ( $expires )) {
							$days = ( int ) $expires;
							if ($days)
								$access_expires = JFactory::getDate ( "+" . $days . " days" )->toSql ( true );
						}
					}
					if (isset ( $access_expires )) {
						$table->access_expires = $access_expires;
					} else {
						$table->access_expires = JFactory::getDbo ()->getNullDate ();
					}
					$table->store ();
					//add a note
					if($order instanceof J2StoreTableOrder) {
						$order->add_history(JText::_('J2STORE_DOWNLOAD_PERMISSION_GRANTED'));
					}
				}
			}
		}
	}

	public function resetDownloadLimit($order,$override_status = false){
		if($override_status) {
			if(!$this->isStatusValid($order->order_state_id)) return;
		}

		$model = $this->getModel ();
		$downloads = $model->order_id ( $order->order_id )->email($order->user_email)->getList ();

		foreach ( $downloads as $download ) {

			unset($table);
			$table = F0FTable::getAnInstance('Orderdownload', 'J2StoreTable')->getClone();

			if ($table->load($download->j2store_orderdownload_id)) {
				//just re-set the limit count.
				$table->limit_count = 0;
				$table->store ();
				//add a note
				if($order instanceof J2StoreTableOrder) {
					$order->add_history(JText::_('J2STORE_DOWNLOAD_LIMIT_HAS_BEEN_RESET'));
				}
			}
		}
	}

	public function resetDownloads($order, $override_status=false) {
		if($override_status) {
			if(!$this->isStatusValid($order->order_state_id)) return;
		}

		$model = $this->getModel ();
		$downloads = $model->order_id ( $order->order_id )->email($order->user_email)->getList ();

		foreach ( $downloads as $download ) {

			unset($table);
			$table = F0FTable::getAnInstance ( 'Orderdownload', 'J2StoreTable' )->getClone();

			if ($table->load ( $download->j2store_orderdownload_id )) {

				$tz = JFactory::getConfig ()->get ( 'offset' );
				$date = JFactory::getDate ( 'now', $tz );
				$table->access_granted = $date->toSql ( true );

				$product = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' )->getClone();
				$product->load ( $table->product_id );
				if (! empty ( $product->params )) {
					$registry = J2Store::platform()->getRegistry($product->params);
					$expires = $registry->get ( 'download_expiry', '' );

					if (! empty ( $expires )) {
						$days = ( int ) $expires;
						if ($days)
							$access_expires = JFactory::getDate ( "+" . $days . " days" )->toSql ( true );
					}
				}
				if (isset ( $access_expires )) {
					$table->access_expires = $access_expires;
				} else {
					$table->access_expires = JFactory::getDbo ()->getNullDate ();
				}
				$table->store ();
				//add a note
				if($order instanceof J2StoreTableOrder) {
					$order->add_history(JText::_('J2STORE_DOWNLOAD_EXPIRY_HAS_BEEN_RESET'));
				}
			}

		}
	}

	function getDownloads() {

		$app = JFactory::getApplication();
		$token = $app->input->getString('token', '');
		$productfile_id = $app->input->getInt('pid', 0);
		if(empty($token) || $productfile_id == 0 || empty($productfile_id)) {
			$this->setError(JText::_('J2STORE_DOWNLOAD_ERROR_WRONG_TOKEN'));
			return false;
		}
		//first load the product file
		$productfile = F0FTable::getAnInstance('Productfile', 'J2StoreTable');
		$productfile->load($productfile_id);
		//now get the downloads
		$this->clearState()->setState('token', $token);
		$this->setState('product_id', $productfile->product_id);

		$orderdownloads = $this->getList();
		$orderdownload = $orderdownloads[0];

		if($this->validateDownload($orderdownload, $productfile) == false) {
			$this->setError($this->getError());
			return false;
		}

		//file is valid. trigger download
		$this->triggerDownload($orderdownload, $productfile);
		$app->close();
	}

	function validateDownload($orderdownload, $productfile) {

		$standard_error_text = JText::_('J2STORE_DOWNLOAD_ERROR_WRONG_TOKEN');
		//is the order right
		if(!isset($orderdownload->order_id) || empty($orderdownload->order_id)) {
			$this->setError($standard_error_text);
			return false;
		}

		//is the product file right
		if(!isset($productfile->j2store_productfile_id) || empty($productfile->j2store_productfile_id)) {
			$this->setError($standard_error_text);
			return false;
		}

		//order state id is confirmed
		if(!$this->isStatusValid($orderdownload->order_state_id)) {
			$this->setError(JText::_('J2STORE_DOWNLOAD_ERROR_ORDER_NOT_CONFIRMED'));
			return false;
		}

		//check limits
		$registry = J2Store::platform()->getRegistry($orderdownload->product_params);
		$download_limit = $registry->get('download_limit', 0);

		if($download_limit == 0) {
			$this->setError(JText::_('J2STORE_DOWNLOAD_ERROR_DOWNLOAD_DISABLED'));
			return false;
		}

		if($download_limit > 0 && $orderdownload->limit_count >= $download_limit ) {
			$this->setError(JText::_('J2STORE_DOWNLOAD_ERROR_DOWNLOAD_LIMIT_REACHED'));
			return false;
		}

		//access expires
		$nullDate = JFactory::getDbo()->getNullDate();
		if($orderdownload->access_expires != $nullDate) {
			$now = JFactory::getDate('now', JFactory::getConfig()->get('offset'))->toUnix();
			$expires = JFactory::getDate($orderdownload->access_expires, JFactory::getConfig()->get('offset'))->toUnix();

			if($now > $expires) {
				$this->setError(JText::_('J2STORE_DOWNLOAD_ERROR_DOWNLOAD_LIMIT_REACHED'));
				return false;
			}
		}
		//does file exists in the path
		if($this->getFilePath($productfile) == false) {
			$this->setError($standard_error_text);
			return false;
		}
		return true;
	}

	private function triggerDownload($orderdownload, $productfile) {
        $platform = J2Store::platform();
		$app = $platform->application();
		$file = $this->getFilePath($productfile);
		J2Store::plugin()->event('BeforeDownload',  array(&$orderdownload, &$productfile, &$file));
		//$app->triggerEvent('onJ2StoreBeforeDownload',  array(&$orderdownload, &$productfile, &$file));

		if ($platform->isClient('site')) {
			$this->hitCount($orderdownload, $productfile);
		}
		$this->downloadFile($file);
		$app->close();
	}

	public function downloadFile($file, $mask='') {
		// only show errors and remove warnings from corrupting file
		error_reporting(E_ERROR);

		ob_clean();
		if (connection_status()!=0) return(FALSE);

		$fn = !empty($mask)? $mask : basename($file);
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Content-Transfer-Encoding: binary");

		//TODO:  Not sure of this is working
		if (function_exists('mime_content_type')) {
			$ctype = mime_content_type($file);
		}
		else if (function_exists('finfo_file')) {
			$finfo    = finfo_open(FILEINFO_MIME);
			$ctype = finfo_file($finfo, $file);
			finfo_close($finfo);
		}
		else {
			$ctype = "application/octet-stream";
		}

		header('Content-Type: ' . $ctype);

		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
		{
			/*//workaround for IE filename bug with multiple periods / multiple dots in filename
			 //that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe*/
			$iefilename = preg_replace('/\./', '%2e', $fn, substr_count($fn, '.') - 1);
			header("Content-Disposition: attachment; filename=\"$iefilename\"");
		}
		else
		{
			header("Content-Disposition: attachment; filename=\"$fn\"");
		}

		header("Accept-Ranges: bytes");

		$range = 0; // default to begining of file
		//TODO make the download speed configurable
		$size=filesize($file);

		//check if http_range is set. If so, change the range of the download to complete.
		if(isset($_SERVER['HTTP_RANGE']))
		{
			list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
			str_replace($range, "-", $range);
			$size2=$size-1;
			$new_length=$size-$range;
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $new_length");
			header("Content-Range: bytes $range$size2/$size");
		}
		else
		{
			$size2=$size-1;
			header("HTTP/1.0 200 OK");
			header("Content-Range: bytes 0-$size2/$size");
			header("Content-Length: ".$size);
		}

		//check to ensure it is not an empty file so the feof does not get stuck in an infinte loop.
		if ($size == 0 ) {
            J2Store::platform()->raiseError(500,'ERROR.ZERO_BYTE_FILE');
			exit;
		}
		//Not required because it is deprecated already. However, humans are stupid and many are still using it

		if(function_exists('set_magic_quotes_runtime')) {
			set_magic_quotes_runtime(0); // in case someone has magic quotes on. Which they shouldn't as good practice.
		}

		// we should check to ensure the file really exits to ensure feof does not get stuck in an infite loop, but we do so earlier on, so no need here.
		$fp=fopen("$file","rb");

		//go to the start of missing part of the file
		fseek($fp,$range);
		if (function_exists("set_time_limit"))
			set_time_limit(0);
		while(!feof($fp) && connection_status() == 0)
		{
			//reset time limit for big files
			if (function_exists("set_time_limit"))
				set_time_limit(0);
			print(fread($fp,1024*8));
			flush();
			ob_flush();
		}
		sleep(1);
		fclose($fp);
		return((connection_status()==0) and !connection_aborted());
	}

	private function getFilePath($productfile) {

		$params = J2Store::config();
		$path = $params->get('attachmentfolderpath');
		//$savepath = $path.DS.'products';
		$file = JPath::clean($path.'/'.$productfile->product_file_save_name);

		if(!JFile::exists($file)) {
            $path = JPATH_ROOT. $path . '/';
            $current = JPath::clean($path.'/'.$productfile->product_file_save_name);
            $file = $root.trim($current,'/');
        }

		if(!JFile::exists($file)) {
            $path = JPATH_ROOT.'/';
            $file = JPath::clean($path.'/'.$productfile->product_file_save_name);
        }

		//if does not exists, check inside the web root
		if(!JFile::exists($file)) {
			$path = JPATH_ROOT.'/'.$path;
			$file = JPath::clean($path.'/'.$productfile->product_file_save_name);
		}

		//legacy compatibility
		if(!JFile::exists($file)) {
			$path = trim($params->get('attachmentfolderpath'));
			$savepath = $path.DIRECTORY_SEPARATOR.'products';
			$file = $savepath.DIRECTORY_SEPARATOR.$productfile->product_id.DIRECTORY_SEPARATOR.$productfile->product_file_save_name;
		}

		if(!JFile::exists($file)) {
			$path = trim($params->get('attachmentfolderpath'));
			$savepath = $path.DIRECTORY_SEPARATOR.'products';
			$product = F0FTable::getInstance('Product', 'J2StoreTable')->getClone();
			if($product->load($productfile->product_id)) {
				$product_source_id = $product->product_source_id;
			}

			$file = $savepath.DIRECTORY_SEPARATOR.$product_source_id.DIRECTORY_SEPARATOR.$productfile->product_file_save_name;
		}

		$file = JPath::clean($file);

		if (JFile::exists($file)) {
			return $file;
		}
		return false;
	}

	protected function hitCount($orderdownload, $productfile) {

		$table = F0FTable::getAnInstance('Orderdownload', 'J2StoreTable');
		$table->load($orderdownload->j2store_orderdownload_id);

		$table->limit_count = $table->limit_count + 1;
		$table->store();

		$productfile->download_total = $productfile->download_total +1;
		$productfile->store();

	}

	public function getValidOrderStatuses()
	{
		$statuses = array( 1 );
		J2Store::plugin()->event('GetValidOrderStatuses', array(&$statuses));
		return $statuses;
	}

	public function isStatusValid($status)
	{
		if ( in_array( $status, $this->getValidOrderStatuses() ) )
		{
			return true;
		}

		return false;
	}
}
