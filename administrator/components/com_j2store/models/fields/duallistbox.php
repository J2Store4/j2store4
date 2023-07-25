<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class JFormFieldDuallistbox  extends JFormFieldList  {

	protected $type = 'Duallistbox';
	public function getInput(){
		$json = $this->getOptions();
		$json = json_encode($json,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
		$title = !empty($this->element['data_title']) ? $this->element['data_title'] : $this->element['label'];
		JHtml::_('script', 'media/j2store/js/dual-list-box.js', false, false);
		$script ="jQuery('select').DualListBox();";
		//JFactory::getDocument()->addScriptDeclaration($script);
		$selected = json_encode($this->value);
		$input_id = !empty($this->id) ? $this->id : 'duallistbox-input';
		$html ='<div id="dual-list-box" class="row-fluid">';
		$html .='<select id='.$input_id.' multiple="multiple" data-title='.JText::_($title).'
					data-source='. stripslashes($json).'
					data-realdata='.$json.'
					data-json='.$selected.'
					data-value='.$this->element['data_value'].'
					data-textLength="100"
					data-text='.$this->element['data_text'].'
					name='.$this->element['name'].'[]'.'
					data-maxAllBtn='.$this->element['data_maxAllBtn'].'></select>';
		$html .='<script type="text/javascript">
		var selected = '. $selected .';
		var paramOptions;
		(function($){
		jQuery(\'#'.$input_id.'\').DualListBox(paramOptions, selected);
		})(j2store.jQuery);
		</script></div>';
		return $html;

	}


	/**
	 * Method to get the field options.
	 *
	 * Ordering is disabled by default. You can enable ordering by setting the
	 * 'order' element in your form field. The other order values are optional.
	 *
	 * - order					What to order.			Possible values: 'name' or 'value' (default = false)
	 * - order_dir				Order direction.		Possible values: 'asc' = Ascending or 'desc' = Descending (default = 'asc')
	 * - order_case_sensitive	Order case sensitive.	Possible values: 'true' or 'false' (default = false)
	 *
	 * @return  array  The field option objects.
	 *
	 * @since	Ordering is available since F0F 2.1.b2.
	 */
	protected function getOptions()
	{
		// Ordering is disabled by default for backward compatibility
		$order = false;

		// Set default order direction
		$order_dir = 'asc';

		// Set default value for case sensitive sorting
		$order_case_sensitive = false;

		if ($this->element['order'] && $this->element['order'] !== 'false')
		{
			$order = $this->element['order'];
		}

		if ($this->element['order_dir'])
		{
			$order_dir = $this->element['order_dir'];
		}

		if ($this->element['order_case_sensitive'])
		{
			// Override default setting when the form element value is 'true'
			if ($this->element['order_case_sensitive'] == 'true')
			{
				$order_case_sensitive = true;
			}
		}

		// Create a $sortOptions array in order to apply sorting
		$i = 0;
		$sortOptions = array();

		foreach ($this->element->children() as $option)
		{
			$name = JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname));

			$sortOptions[$i] = new stdClass;
			$sortOptions[$i]->option = $option;
			$sortOptions[$i]->value = $option['value'];
			$sortOptions[$i]->name = $name;
			$i++;
		}

		// Only order if it's set
		if ($order)
		{
			jimport('joomla.utilities.arrayhelper');
			F0FUtilsArray::sortObjects($sortOptions, $order, $order_dir == 'asc' ? 1 : -1, $order_case_sensitive, false);
		}

		// Initialise the options
		$options = array();

		// Get the field $options
		foreach ($sortOptions as $sortOption)
		{
			$option = $sortOption->option;
			$name = $sortOption->name;

			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			$tmp = JHtml::_('select.option', (string) $option['value'], $name, 'value', 'text', ((string) $option['disabled'] == 'true'));

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		// Do we have a class and method source for our options?
		$source_file      = empty($this->element['source_file']) ? '' : (string) $this->element['source_file'];
		$source_class     = empty($this->element['source_class']) ? '' : (string) $this->element['source_class'];
		$source_method    = empty($this->element['source_method']) ? '' : (string) $this->element['source_method'];
		$source_key       = empty($this->element['source_key']) ? '*' : (string) $this->element['source_key'];
		$source_value     = empty($this->element['source_value']) ? '*' : (string) $this->element['source_value'];
		$source_translate = empty($this->element['source_translate']) ? 'true' : (string) $this->element['source_translate'];
		$source_translate = in_array(strtolower($source_translate), array('true','yes','1','on')) ? true : false;
		$source_format	  = empty($this->element['source_format']) ? '' : (string) $this->element['source_format'];

		if ($source_class && $source_method)
		{

			// Maybe we have to load a file?
			if (!empty($source_file))
			{
				$source_file = F0FTemplateUtils::parsePath($source_file, true);

				if (F0FPlatform::getInstance()->getIntegrationObject('filesystem')->fileExists($source_file))
				{
					include_once $source_file;
				}
			}

			// Make sure the class exists
			if (class_exists($source_class, true))
			{				// ...and so does the option
				if (in_array($source_method, get_class_methods($source_class)))
				{
					// Get the data from the class
					if ($source_format == 'optionsobject')
					{
						$options = array_merge($options, $source_class::$source_method());
					}
					else
					{
						// Get the data from the class
						$source_data = $source_class::$source_method();
					}
				}
			}
		}

		//to avoid jquery error
		foreach($source_data as $cat){
			$cat->title = str_replace(' ', '_',$cat->title);
		}
		return $source_data;
	}
}