<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined ( '_JEXEC' ) or die ();
class J2StoreModelProductOptions extends F0FModel {


	public function buildQuery($overrideLimits = false) {

		$query = parent::buildQuery($overrideLimits);		
		$query->select('#__j2store_options.option_unique_name, #__j2store_options.option_name, #__j2store_options.type, #__j2store_options.option_params')
			->join('LEFT OUTER','#__j2store_options ON #__j2store_options.j2store_option_id = #__j2store_product_options.option_id');

		$parent_id = $this->getState('parent_id', null);
		if(isset($parent_id) && !is_null($parent_id))
		{
			$query->where($this->_db->qn('#__j2store_product_options.parent_id').' = '.$this->_db->q($parent_id));
		}
		$query->clear('order');
		$query->order('#__j2store_product_options.ordering ASC');		
		return $query;
	}

	public function getParentOptionValues($parent_id,$product_id){
		$items = array();
		//get the list of product options having the option id
		$parent_option = F0FTable::getAnInstance('Productoption' ,'J2StoreTable');
		$parent_option->load(array('option_id' => $parent_id ,'product_id' =>$product_id));
		if(isset($parent_option) && !empty($parent_option)){
			$items = F0FModel::getTmpInstance('Productoptionvalues','J2StoreModel')
			->productoption_id($parent_option->j2store_productoption_id)
			->getList();
		}
		return $items;
	}


	/**
	 * copy the attributes and options.
	 *
	 * @source_product_id  int  Source product id.
	 * @dest_product_id  int  Destination product id.
	 *
	 * @since   2.7
	 */

	function importAttributeFromProduct($product,$source_product_id, $dest_product_id) {
		// only simple product's options can be imported
		$product_helper = J2Store::product ();
		if($product_helper->is_product_type_allowed($product->product_type,array('simple', 'configurable', 'booking'),'importAttributeFromProduct')){
			$this->getImportedProductOptions($source_product_id,$dest_product_id);
		}else{
			return false;
		}
		return true;
	}



	public function getImportedProductOptions($source_product_id,$dest_product_id){
		$source_attributes = F0FModel::getTmpInstance('ProductOptions','J2StoreModel')->product_id($source_product_id)->getList();
		//first get the attributes of source product
		if(count($source_attributes) < 1) {
			$this->setError(JText::_('J2STORE_PAI_PRODUCT_DONT_HAVE_ATTRIBUTES'));
			return false;
		}
		$map = array();
		$imported_rows = array();
		//now we have the product options. Loop to insert them
		foreach ($source_attributes as $s_attribute) {
			//load source first
			//$sa_item = JTable::getInstance('ProductOptions', 'Table');
			$sa_item =F0FTable::getAnInstance('ProductOption','J2StoreTable');
			$sa_item->load($s_attribute->j2store_productoption_id);

			//now copy it
			$dest_row = F0FTable::getAnInstance('ProductOption','J2StoreTable');
			$dest_row  = $sa_item;
			$dest_row->j2store_productoption_id = NULL;
			$dest_row->product_id = $dest_product_id;
			$dest_row->store();
			$source_attribute_options =F0FModel::getTmpInstance('ProductOptionvalues','J2StoreModel')->productoption_id($s_attribute->j2store_productoption_id)->getList();
			if(count($source_attribute_options)) {
				foreach ($source_attribute_options as $sa_option) {
					unset($sao_item);
					//load source
					$sao_item =F0FTable::getAnInstance('ProductOptionValue','J2StoreTable');
					$sao_item->load($sa_option->j2store_product_optionvalue_id);
					//now copy it;
					$dest_sao_row = F0FTable::getAnInstance('ProductOptionValue','J2StoreTable');
					$dest_sao_row = $sao_item;
					$dest_sao_row->j2store_product_optionvalue_id = NULL;
					$dest_sao_row->productoption_id =$dest_row->j2store_productoption_id;
					$dest_sao_row->product_id = $dest_row->product_id;
					$dest_sao_row->store();
					$imported_rows[] = $dest_sao_row->j2store_product_optionvalue_id;
					$map[$sa_option->j2store_product_optionvalue_id] = $dest_sao_row->j2store_product_optionvalue_id;
				}
			}
		}
		//now we have to migrate the parent option values.
		$this->migrateParentOptionValues($imported_rows, $map);
		return true;
	}

	public function migrateParentOptionValues($imported_rows, $map) {
		foreach ( $imported_rows as $row ) {
			unset ( $table );
			$table = F0FTable::getAnInstance ( 'ProductOptionValue', 'J2StoreTable' )->getClone ();
			if ($table->load ( $row )) {
				// now empty.
				if (! empty ( $table->parent_optionvalue )) {
					$parent_values = explode ( ',', $table->parent_optionvalue );
					$new_values = array ();
					foreach ( $parent_values as $oldvalue ) {
						// this is an old value. Find the new map
						if (isset ( $map [$oldvalue] ))
							$new_values [] = $map [$oldvalue];
					}
					if (count ( $new_values )) {
						// recreate the csv
						$table->parent_optionvalue = implode ( ',', $new_values );
						$table->store ();
					}
				}
			}
		}
	}

	public function getTableFields()
	{
		$tableName = $this->getTable()->getTableName();
		static $sets;

		if ( !is_array( $sets) )
		{
			$sets= array( );
		}

		if(!isset($sets[$tableName])) {

			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sets[$tableName] = $this->getDbo()->getTableColumns($tableName, true);
			}
			else
			{
				$fieldsArray = $this->getDbo()->getTableFields($tableName, true);
				$sets[$tableName] = array_shift($fieldsArray);
			}
		}
		return $sets[$tableName];
	}

}
