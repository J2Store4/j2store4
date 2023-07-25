<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelEmailtemplates extends F0FModel {
	
	
	public function onBeforePreprocessForm(F0FForm &$form, &$data) {
		
		if(isset($data['body_source']) && $data['body_source'] == 'file') {
			$app = JFactory::getApplication ();
			// Codemirror or Editor None should be enabled
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from('#__extensions as a')
			->where(
					'(a.name =' . $db->quote('plg_editors_codemirror') .
					' AND a.enabled = 1) OR (a.name =' .
					$db->quote('plg_editors_none') .
					' AND a.enabled = 1)'
			);
			$db->setQuery($query);
			$state = $db->loadResult();
			
			if ((int) $state < 1)
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_EDITOR_DISABLED'), 'warning');
			}
			//get the file name
			$filename = $data['body_source_file'];
			if(!empty($filename) && $filename != '-1') { 
				
				$source = $this->getSource($filename);			
				$data['source'] = $source->source;
			}else {
				$form->removeField('source');
			}	
			
			//remove fields
			$form->removeField('body');
		}else {
			$form->removeField('body_source_file');
			$form->removeField('source');
		}
	}
	

	
	public function getSource($filename) {
		$app = JFactory::getApplication ();
		$item = new stdClass ();
		
		if ($filename) {
			$input = JFactory::getApplication ()->input;
			$filePath = JPath::clean ( JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tpls/'.$filename);
			
			if (file_exists ( $filePath )) {
				$item->filename = $filename;
				$item->source = file_get_contents ( $filePath );
			} else {
				$app->enqueueMessage ( JText::_ ( 'J2STORE_EMAILTEMPLATE_ERROR_SOURCE_FILE_NOT_FOUND' ), 'error' );
			}
		}
		
		return $item;
	}
    protected function onBeforeSave(&$data, &$table){
        $app = J2Store::platform()->application();
        $body = $app->input->getRaw('body','');
        if(!empty($body)){
            $data['body'] = $body;
        }
        return true;
    }
	protected function onAfterSave(&$table) {

		if($table->body_source == 'file' && $table->body_source_file != '-1') {
			
			jimport('joomla.filesystem.file');
			
			$app = JFactory::getApplication();
			$fileName = $table->body_source_file;
			
			$filePath = JPath::clean ( JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tpls/'.$fileName);
			
			// Include the extension plugins for the save events.
			JPluginHelper::importPlugin('extension');
			
			$user = get_current_user();
			chown($filePath, $user);
			JPath::setPermissions($filePath, '0644');
			
			// Try to make the template file writable.
			if (!is_writable($filePath))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_WRITABLE'), 'warning');
				$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_PERMISSIONS' . JPath::getPermissions($filePath)), 'warning');
			
				if (!JPath::isOwner($filePath))
				{
					$app->enqueueMessage(JText::_('COM_TEMPLATES_CHECK_FILE_OWNERSHIP'), 'warning');
				}
			
				return false;
			}
			$source = JFactory::getApplication()->input->get('source', '', 'RAW');
			jimport('joomla.filter.filterinput');
			$filter = JFilterInput::getInstance(array(), array(), 1, 1);
			$value = $filter->clean($source, 'raw');
			$return = true;
			if(!empty($value)) {			
				$return = JFile::write($filePath, $value);
			}	
			
			// Try to make the template file unwritable.
			if (JPath::isOwner($filePath) && !JPath::setPermissions($filePath, '0644'))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_UNWRITABLE'), 'error');
			
				return false;
			}
			elseif (!$return)
			{
				$app->enqueueMessage(JText::sprintf('COM_TEMPLATES_ERROR_FAILED_TO_SAVE_FILENAME', $fileName), 'error');
			
				return false;
			}
		}	
		
		
	}
	
	public function sendTestEmail($emailtemplate_id) {
		
		// load the template
		$emailtemplate = F0FTable::getInstance ( 'Emailtemplate', 'J2StoreTable' );
		if ($emailtemplate->load ( $emailtemplate_id )) {
			// template loaded. Now check if there is at least one order placed.
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true )->select ( '*' )->from ( '#__j2store_orders' )->order ( 'j2store_order_id DESC' );
			$db->setQuery ( $query, 0, 1 );
			$orders = $db->loadObjectList ();
			if (count ( $orders ) && isset ( $orders [0] )) {
				$order = F0FTable::getInstance ( 'Order', 'J2StoreTable' );
				if ($order->load ( $orders [0]->j2store_order_id )) {
					
					$config = JFactory::getConfig ();
					$params = J2Store::config ();
					
					$sitename = $config->get ( 'sitename' );
					
					
					$mailer = $this->getTestMail($order, $emailtemplate);					
					$mailfrom = $config->get ( 'mailfrom' );
					$fromname = $config->get ( 'fromname' );
					$mailer->setSender ( array (
							$mailfrom,
							$fromname 
					) );
					
					$user_email = JFactory::getUser ()->email;
					
					if (isset ( $user_email ) && ! empty ( $user_email ) && $mailer != false) {
						$mailer->addRecipient ( $user_email );
						
						try {
							$send = $mailer->send ();
						} catch ( Exception $e ) {
							throw new Exception ( $e->getMessage () );
							return false;
						}
						
						$mailer = null;
					}
					
					if ($send) {
						return $user_email;
					} else {
						return false;
					}
				}
			} else {
				throw new Exception ( JText::_ ( 'J2STORE_EMAILTEMPLATE_NO_ORDERS_FOUND' ) );
				return false;
			}
		} else {
			throw new Exception ( JText::_ ( 'J2STORE_EMAILTEMPLATE_NOT_FOUND' ) );
			return false;
		}
	}
	
	private function getTestMail($order, $template, $isHTML = 1) {
		
		$mailer = clone JFactory::getMailer();
		
		$mailer->IsHTML($isHTML);
		// Required in order not to get broken characters
		$mailer->CharSet = 'UTF-8';
		
		$extras= array();
		
		$subject = $template->subject;
			
		if(isset($template->body_source) && $template->body_source == 'file') {
			$templateText = J2Store::email()->getTemplateFromFile($template, $order);
		}else {
			$templateText = $template->body;
		}
		
		$this->loadLanguageOverrides($order);
		
		$templateText = J2Store::email()->processTags($templateText, $order, $extras);
		
		$subject = J2Store::email()->processTags($subject, $order, $extras);
		
		$baseURL = str_replace('/administrator', '', JURI::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');
		
		$mailer->setSubject($subject);
		
		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';
		
			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
				$url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					$ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						// Relative path, make absolute
						$url = $baseURL.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img'.$imageSubs[$url];
					}
				}
		
				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		
		}
		
		
		
		$htmlExtra = '';
		$lang = JFactory::getLanguage();
		if($lang->isRTL()) {
			$htmlExtra = ' dir="rtl"';
		}
		$body = '<html'.$htmlExtra.'><head>'.
				'<meta http-equiv="Content-Type" content="text/html; charset='.$mailer->CharSet.'">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				</head>';
		//echo $body.$templateText; exit;
		$mailer->setBody($body.$templateText);
		return $mailer;
	}
	
	function loadLanguageOverrides($order) {
		
		$extension = 'com_j2store';
		$jlang = JFactory::getLanguage();
		// -- English (default fallback)
		$jlang->load($extension, JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, 'en-GB', true);
		// -- Default site language
		$jlang->load($extension, JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		// -- Current site language
		$jlang->load($extension, JPATH_ADMINISTRATOR, null, true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, null, true);
		
		$jlang->load($extension, JPATH_ADMINISTRATOR, $order->customer_language, true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, $order->customer_language, true);
		
	}
	
}