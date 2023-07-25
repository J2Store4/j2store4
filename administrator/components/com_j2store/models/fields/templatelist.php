<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


class JFormFieldTemplateList extends JFormFieldList {

	protected $type = 'TemplateList';

	public function getInput() {
		jimport('joomla.filesystem.folder');
		$fieldName =  $this->name ;
		$db = JFactory::getDBO();
		$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
		$db->setQuery($query);
		$defaultemplate = $db->loadResult();
		if (JFolder::exists(JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_j2store/templates'))
		{
			$templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_j2store/templates';
		}
		else
		{
			$templatePath = JPATH_SITE.'/templates/'.$defaultemplate.'/html/com_j2store/products';
		}
        $componentFolders = array();
        J2Store::plugin()->event('TemplateFolderList',array(&$componentFolders));
		if (JFolder::exists($templatePath))
		{
			$templateFolders = JFolder::folders($templatePath);
			$folders = @array_merge($templateFolders, $componentFolders);
			$folders = @array_unique($folders);
		}
		else
		{
			$folders = $componentFolders;
		}
		$exclude_array = array(
			'tag',
			'default'
		);
		//$exclude = 'default';
		$options = array();
		foreach ($folders as $folder)
		{
			foreach ($exclude_array as $exclude){
				$substring = substr ( $folder,0,strlen ( $exclude )  );
				if($substring == $exclude){
					continue 2;
				}
			}
			if($folder != 'tmpl') {
				$options[] = JHTML::_('select.option', $folder, $folder);
			}
		}

		array_unshift($options, JHTML::_('select.option', 'default', JText::_('J2STORE_USE_DEFAULT')));
		return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $this->value, $this->control_name.$this->name);
	}

}
