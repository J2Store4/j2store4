<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

require_once(JPATH_ADMINISTRATOR .'/components/com_j2store/helpers/j2html.php');

class JFormFieldShipping extends JFormFieldList
{
	protected $type = 'shipping';
	function getInput(){

		$options = $this->getShippingMethods();

		//sanity check
		if(!is_array($options)) $options = array();

        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            return J2Html::select()->clearState()
                ->type('genericlist')
                ->attribs(array('multiple' => 'multiple'))
                ->name($this->name)
                ->value($this->value)
                ->setPlaceHolders($options)
                ->getHtml();
        }else{
            $displayData = array(
                'class' => 'input-small',
                'name' => $this->name,
                'value' => $this->value  ,
                'options' => $options,
                'autofocus' => '',
                'onchange' => '',
                'dataAttribute' => '',
                'readonly' => '',
                'disabled' => false,
                'hint' => '',
                'required' => false,
                'id' => '',
                'multiple'=> true
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/list-fancy-select.php';
            $media_render = self::getRenderer('joomla.form.field.list-fancy-select', $path);
            return $media_render->render($displayData);
        }
	}

	public function getShippingMethods(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__extensions')
			->where('type='.$db->q('plugin'))
			->where('folder='.$db->q('j2store'))
			->where('element LIKE '.$db->q('shipping_%'))
		;
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$j2store_shipping = array(
			'shipping_standard',
			'shipping_postcode',
			'shipping_additional',
			'shipping_incremental',
			'shipping_flatrate_advanced'
		);
		$data =array();
		foreach($row as $item){
			//exclude free shipping method
			if($item->element == 'shipping_free') continue;

			if(in_array($item->element, $j2store_shipping)){
				$query = $db->getQuery(true)->select('*')->from('#__j2store_shippingmethods')->where('published = 1');
				$db->setQuery($query);
				$ship_methods = $db->loadObjectList();
				//print_r($ship_methods);exit;
				foreach ($ship_methods as $ship_method){
					if(!in_array($ship_method->shipping_method_name, $data)){
						$data[$ship_method->shipping_method_name] = JText::_($ship_method->shipping_method_name);
					}
				}

			}else{
				$data[$item->element] = JText::_($item->name);
			}

		}
		return $data;
	}
}