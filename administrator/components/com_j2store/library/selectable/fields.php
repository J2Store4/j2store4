<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 * based on Hikashop field class
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/selectable/base.php');
class J2StoreSelectableFields {

	protected static $instance;
	var $allValues;
	var $externalValues;

	function __construct($args=array()) {
		$this->externalValues = null;
	}

	public static function getInstance()
	{
		if (!is_object(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	function load($type=''){
		$this->allValues = array();
		$this->allValues["text"] = JText::_('J2STORE_TEXT');
		$this->allValues["email"] = JText::_('J2STORE_EMAIL');
		$this->allValues["textarea"] = JText::_('J2STORE_TEXTAREA');
		$this->allValues["wysiwyg"] = JText::_('J2STORE_WYSIWYG');
		$this->allValues["radio"] = JText::_('J2STORE_RADIO');
		$this->allValues["checkbox"] = JText::_('J2STORE_CHECKBOX');
		$this->allValues["singledropdown"] = JText::_('J2STORE_SINGLEDROPDOWN');
		$this->allValues["zone"] = JText::_('J2STORE_ZONELIST');
		$this->allValues["date"] = JText::_('J2STORE_DATE');
		$this->allValues["time"] = JText::_('J2STORE_TIME');
		$this->allValues["datetime"] = JText::_('J2STORE_DATETIME');
		$this->allValues["customtext"] = JText::_('J2STORE_CUSTOM_TEXT');

		if($this->externalValues == null) {
            $app = JFactory::getApplication();
            $this->externalValues = array();
			JPluginHelper::importPlugin('j2store');
            $app->triggerEvent('onJ2StoreFieldsLoad', array(&$this->externalValues));
			if(!empty($this->externalValues)) {
				foreach($this->externalValues as $value) {
					if(substr($value->name,0,4) != 'plg.')
						$value->name = 'plg.'.$value->name;
					$this->allValues[$value->name] = $value->text;
				}
			}
		}
	}

	function addJS(){
		$externalJS = '';
		if(!empty($this->externalValues)){
			foreach($this->externalValues as $value) {
				$externalJS .= "\r\n\t\t\t".$value->js;
			}
		}
		$js = "function updateFieldType(){
			newType = document.getElementById('fieldtype').value;
			hiddenAll = new Array('multivalues','cols','rows','size','required','format','zone','coupon','default','customtext','columnname','filtering','maxlength','allow','readonly','place_holder');
			allTypes = new Array();
			allTypes['text'] = new Array('size','required','default','columnname','filtering','maxlength','readonly','place_holder');
			allTypes['email'] = new Array('size','required','default','columnname','filtering','maxlength','readonly','place_holder');
			allTypes['link'] = new Array('size','required','default','columnname','filtering','maxlength','readonly');
			allTypes['textarea'] = new Array('cols','rows','required','default','columnname','filtering','readonly','maxlength','place_holder');
			allTypes['wysiwyg'] = new Array('cols','rows','required','default','columnname','filtering');
			allTypes['radio'] = new Array('multivalues','required','default','columnname');
			allTypes['checkbox'] = new Array('multivalues','required','default','columnname');
			allTypes['singledropdown'] = new Array('multivalues','required','default','columnname');
			allTypes['multipledropdown'] = new Array('multivalues','size','default','columnname');
			allTypes['date'] = new Array('required','format','size','default','columnname','allow');
			allTypes['time'] = new Array('required','format','size','default','columnname','allow');
			allTypes['datetime'] = new Array('required','format','size','default','columnname','allow');
			allTypes['zone'] = new Array('required','zone','default','columnname');
			allTypes['file'] = new Array('required','default','columnname');
			allTypes['image'] = new Array('required','default','columnname');
			allTypes['coupon'] = new Array('size','required','default','columnname');
			allTypes['customtext'] = new Array('customtext');".$externalJS."
			for (var i=0; i < hiddenAll.length; i++){
				jQuery('tr[class='+hiddenAll[i]+']').each(function(el) {
					jQuery(this).css('display', 'none');
				});
			}
			for (var i=0; i < allTypes[newType].length; i++){
				jQuery('tr[class='+allTypes[newType][i]+']').each(function(el) {
					jQuery(this).css('display', '');
				});
			}
		}
		jQuery(document).ready(function(){
			updateFieldType();
		});";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	public function display($map,$value,$type){
		$this->load($type);
		$this->addJS();
		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal){
			$this->values[] = JHTML::_('select.option', $oneType,$oneVal);
		}

		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onchange="updateFieldType();"', 'value', 'text', (string) $value,'fieldtype');
    }
}

class j2storeZoneType {
	function load($form=false){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', '', JText::_('J2STORE_ALL_ZONES') );
		}
		$this->values[] = JHTML::_('select.option', 'country',JText::_('J2STORE_COUNTRIES'));
		$this->values[] = JHTML::_('select.option', 'zone',JText::_('J2STORE_ZONES'));
	}

	function display($map,$value,$form=false){
		$this->load($form);
		$dynamic = ($form ? '' : 'onchange="document.adminForm.submit( );"');
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'. $dynamic, 'value', 'text', $value );
	}
}


class j2storeCountryType{
	var $type = 'country';
	var $published = false;
	var $allName = 'J2STORE_ALL_ZONES';
	var $country_name = '';
	var $country_id = '';
	protected $country_list = null;

	function load(){

		if($this->type == 'country') {
			static $sets;
			if ( !is_array( $sets) )
			{
				$sets= array( );
			}

			if(!isset($sets[1])) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('a.*')->from('#__j2store_countries AS a');
				$query->where('a.enabled=1')
					->order('a.country_name ASC');
				$db->setQuery($query);
				$sets[1] = $db->loadObjectList();
			}

			$list = $sets[1];

		} elseif($this->type == 'zone') {

			static $sets1;
			if ( !is_array( $sets1) )
			{
				$sets1= array( );
			}
			if(!isset($sets1[$this->country_id])) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('a.*')->from('#__j2store_zones AS a');
				$query->where('a.enabled=1')
					->order('a.zone_name ASC');
				//if(isset($this->country_id)) {
					$query->where('a.country_id='.$db->q($this->country_id));
				//}
				$db->setQuery($query);
				$sets1[$this->country_id] = $db->loadObjectList();
			}
			$list = $sets1[$this->country_id];

		}
		return $list;
	}

	function display($map, $value, $form = true, $options = 'class="inputbox" size="1"',$id=false){
		$countries = $this->load();
		$this->values = array();
		if($form){
			$this->values[] = JHTML::_('select.option', '0', JText::_($this->allName) );
			//$options .= ' onchange="document.adminForm.submit( );"';
		}
		foreach($countries as $country){
			$this->values[] = JHTML::_('select.option', $country->j2store_country_id, JText::_($country->country_name));
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', (int)$value, $id );
	}


	function displayZone($map, $value, $form = true, $options = 'class="inputbox" size="1"',$id=false){
		$zones = $this->load();
		$this->values = array();
		if($form){
			$this->values[] = JHTML::_('select.option', '', JText::_('J2STORE_SELECT_STATE') );
			//$options .= ' onchange="document.adminForm.submit( );"';
		}
		foreach($zones as $zone){
			$this->values[] = JHTML::_('select.option', $zone->j2store_zone_id, JText::_($zone->zone_name));
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', (int)$value, $id );

	}

}
