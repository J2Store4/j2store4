<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelOptions extends F0FModel {

	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as &$res){
			$res->optionvalues = $this->getOptionValues($res->j2store_option_id);
		}
	}

	protected function onAfterGetItem(&$record)
	{
		$record->optionvalues = $this->getOptionValues($record->j2store_option_id);
	}

	public function chkDuplicateOptionUniqueName($option_unique_name){
        $ovTable = F0FTable::getInstance ( 'Option', 'J2StoreTable' )->getClone();
        $ovTable->load(array(
            'option_unique_name' => $option_unique_name
        ));
        if($ovTable->j2store_option_id){
            $option_unique_name .='_1';
            $option_unique_name = $this->chkDuplicateOptionUniqueName($option_unique_name);
        }
        return $option_unique_name;
    }
	
	public function onBeforeSave(&$data, &$table){
		if(isset($data['option_params']) && !empty($data['option_params'])){
			$data['option_params'] = json_encode($data['option_params']);
		}else{
			$data['option_params'] ='';
		}
        $status = true;
		if(!isset($data['j2store_option_id']) || empty($data['j2store_option_id'])){
            $data['option_unique_name'] = $this->chkDuplicateOptionUniqueName($data['option_unique_name']);
        }
		return $status;
	}

	public function save($data) {

		if (parent::save ( $data )) {
			if ($this->otable->j2store_option_id) {
				if(is_object($data)) {
					$data = (array) $data; 
				}
				if (isset ( $data ['option_value'] ) && count ( $data ['option_value'] )) {

					$status = true;
					foreach ( $data['option_value'] as $optionvalue ) {
						$ovTable = F0FTable::getInstance ( 'optionvalue', 'J2StoreTable' )->getClone();
						$ovTable->load($optionvalue ['j2store_optionvalue_id']);

						$optionvalue ['option_id'] = $this->otable->j2store_option_id;

						if (! $ovTable->save ( $optionvalue )) {
							$status = false;
						}
					}
				}
				else {return true;}
			}
		}else{return false;}
		return true;
	}

	function getOptionValues($option_id){
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('*')
		->from('#__j2store_optionvalues')
		->where('option_id='.$db->q($option_id));
		$db->setQuery($query);
		return $db->loadObjectList();
	}
    public function onBeforeDelete(&$id,&$table)
    {
        $app = J2Store::platform()->application();
        $product_optionValue = F0FTable::getInstance('Productoption','J2StoreTable')->getClone();
        $product_optionValue->load(array(
            'option_id' => $id
        ));
        $status = true;
        if($product_optionValue->j2store_productoption_id > 0){
            $app->enqueueMessage(JText::_('J2STORE_OPTION_USED_IN_SOME_PRODUCT'),'error');
            $status = false;
        }
        return $status;
    }
	protected function onAfterDelete($id) {
        $app = J2Store::platform()->application();
		$model = $this->getTmpInstance('OptionValues', 'J2StoreModel');
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('j2store_optionvalue_id')
		->from('#__j2store_optionvalues')
		->where('option_id='.$db->q($id));
		$db->setQuery($query);
		$idlist= $db->loadColumn();
		if(count($idlist)) {
			$model->setIds($idlist);
			$model->delete();
		}
        $app->enqueueMessage(JText::_('J2STORE_OPTION_DELETED_SUCCESSFULLY'));
		return true;
	}

	public function getOptions($q){
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$product_type = $app->input->getString('product_type');
		$query = $db->getQuery(true);
		$query->select('j2store_option_id, option_unique_name, option_name');
		$query->from('#__j2store_options');
		$query->where('('.'LOWER(option_unique_name) LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false ).' OR '.' LOWER(option_name) LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false ).')');

		//based on the product type
		if(isset($product_type) && $product_type =='variable'){
			$query->where("type IN ('select' , 'radio' ,'checkbox')");
		}
		$query->where('enabled=1');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	public function getParent($q=''){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('j2store_option_id, option_unique_name, option_name');
		$query->from('#__j2store_options');
		if(isset($q) && !empty($q)){
			$query->where('LOWER(option_unique_name) NOT LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false ));
		}
		$query->where("type IN ('select' , 'radio' ,'checkbox')");
		$query->where('enabled=1');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function validateOptionRules($value, $option, &$errors) {
		if($option->type == 'date' || $option->type == 'datetime') {
			$tz = JFactory::getConfig()->get('offset');
            $params = J2Store::platform()->getRegistry($option->option_params);
			if($params->get('hide_pastdates', 0)) {
				$now = JFactory::getDate('now', $tz);
				$date = JFactory::getDate($value, $tz);
				
				$interval = $now->diff($date);
				//	print_r($interval);
				$val = (int) $interval->format('%R%a');
				//echo $interval->format('%R%a');				
				if($val < 0) {
					$errors['error']['option'][$option->j2store_productoption_id] = JText::_('J2STORE_DATE_VALIDATION_ERROR_PAST_DATE');
				} 
			}
		}
		J2Store::plugin()->event('ValidateOptionRules', array($value, $option, $errors));
	}



}
