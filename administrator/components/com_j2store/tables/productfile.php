<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die;
class J2StoreTableProductfile extends F0FTable
{
	public function __construct($table, $key, &$db)
	{

		parent::__construct($table, $key, $db);
	}

	public function check(){
		$result = true;


		//check variant id exists
		if(empty($this->product_id)){
			$this->setError(JText::_('COM_J2STORE_VARIANT_ID_MISSING'));
			$result = false;
		}
		if(empty($this->product_file_display_name)){
			$this->setError(JText::_('J2STORE_PRODUCT_FILE_DISPLAY_NAME_IS_EMPTY'));
			$result = false;
		}

		//check product file path is not empty
		if(empty($this->product_file_save_name)){
			$this->setError(JText::_('J2STORE_PRODUCT_FILE_PATH_IS_EMPTY'));
			$result = false;
		}

		//to check given file path is valid
		if($this->checkAttachmentPathExists()){
			$this->setError(JText::_('J2STORE_PRODUCT_FILE_PATH_IS_EMPTY'));
			$result = false;
		}
		return parent::check() && $result;
	}

	public function checkAttachmentPathExists(){
		$error = false;
		JLoader::import('joomla.filesystem.folder');
		JLoader::import('joomla.filesystem.file');
		$params = J2Store::config();
		$root = $params->get('attachmentfolderpath');
		$folder = JPATH_ROOT.'/'.$root;
		if(empty($folder) || !JFolder::exists($folder)) {
			//in case, the attachment path is outside the root
			if(!JFolder::exists($root)) {
				$error = true;
			}
		}
		return $error;
	}

}