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


class JFormFieldTagTemplateList extends JFormFieldList {

	protected $type = 'TagTemplateList';

	public function getInput() {
		jimport('joomla.filesystem.folder');
		$fieldName =  $this->name ;
		//$componentPath =  JPATH_SITE.'/components/com_j2store/templates';
		//$componentFolders = JFolder::folders($componentPath);
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

		$include_array = array(
			'tag'
		);
		$options = array();
		foreach ($folders as $folder)
		{
			foreach ($include_array as $include){
				$substring = substr ( $folder,0,strlen ( $include )  );
				if(($substring != $include) || ($folder == 'tag_default')){
					continue 2;
				}
			}
			if($folder != 'tmpl') {
				$options[] = JHTML::_('select.option', $folder, $folder);
			}
		}

		array_unshift($options, JHTML::_('select.option', 'tag_default', JText::_('J2STORE_USE_DEFAULT')));
		return JHTML::_('select.genericlist', $options, $fieldName, 'class="inputbox"', 'value', 'text', $this->value, $this->control_name.$this->name);
	}

}
