<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/selectable/base.php');
class J2StoreModelCustomfields extends F0FModel
{
	public function visible($publish = 1, $user = null)
	{
			$table = $this->getTable($this->table);

			if(!$table->visible($this->id_list, $publish) ) {
				$this->setError($table->getError());
				return false;
			}
		return true;
	}

	public function buildQuery($overrideLimits = false) {
		$db = $this->getDbo();
		$query = $db->getQuery(true)
		->from($db->qn('#__j2store_customfields').' AS '.$db->qn('tbl'));

		$this->_buildQueryColumns($query);
		$this->_buildQueryWhere($query);
		//echo $query;exit;
		return $query;
	}

	protected function _buildQueryColumns($query)
	{
		$db = $this->getDbo();
			$query->select(array(
					$db->qn('tbl').'.*',
					$db->qn('tbl').'.'.$db->qn('field_namekey').' AS '.$db->qn('custom_field_namekey'),
					$db->qn('tbl').'.'.$db->qn('field_name').' AS '.$db->qn('custom_field_name'),
			));

		$order = $this->getState('filter_order', 'j2store_customfield_id', 'cmd');
		if (!in_array($order, array_keys($this->getTable()->getData())))
		{
			$order = 'j2store_customfield_id';
		}

		$dir = strtoupper($this->getState('filter_order_Dir', 'DESC', 'cmd'));
		if (!in_array($dir,array('ASC','DESC')))
		{
			$dir = 'ASC';
		}
		$query->order($order.' '.$dir);
	}

	protected function _buildQueryWhere($query)
	{
		$db = $this->getDbo();
		$state = $this->getFilterValues();

		if($state->fieldnamekey) {
				$fieldnamekey = "%{$state->fieldnamekey}%";
				$query->where(
						$db->qn('tbl').'.'.$db->qn('field_namekey').' LIKE '.$db->q($fieldnamekey)
						);
		}

		if($state->fieldname) {
			$fieldname = "%{$state->fieldname}%";
			$query->where(
					$db->qn('tbl').'.'.$db->qn('field_name').' LIKE '.$db->q($fieldname)
			);
		}

	}

	/**
	 * This method runs before the $data is saved to the $table. Return false to
	 * stop saving.
	 *
	 * @param   array     &$data   The data to save
	 * @param   F0FTable  &$table  The table to save the data to
	 *
	 * @return  boolean  Return false to prevent saving, true to allow it
	 */
	public function save($data)
	{
		$status = true;
		//get the customfieldtable
		$table = $this->getTable();
		$selectableBase = J2Store::getSelectableBase();
		$result = $selectableBase->save();
		if($result) {
			//get process field because result is true
			$data = $selectableBase->fielddata;
			//$table = $this->getTable();
			//$table->bind($data);
			$status = parent::save($data);
			//return true;
			//return $table->field_id;
		} else {
			//error get it
			$errors = $selectableBase->errors;
			$error = implode(',', $errors);
			throw new Exception($error );
			$status = false;
		}
	return true;
	}
}
