<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelFiltergroups extends F0FModel {

	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as &$res){
			$res->filtervalues = F0FModel::getTmpInstance('Filters' ,'J2StoreModel')->group_id($res->j2store_filtergroup_id)->getList();
		}
	}
	protected function onAfterGetItem(&$record)
	{
		if(isset($record->j2store_filtergroup_id))
		$record->filtervalues = F0FModel::getTmpInstance('Filters' ,'J2StoreModel')->group_id($record->j2store_filtergroup_id)->getList();
	}
    public function onBeforeSave(&$data, &$table){
        $status = true;
        foreach ($data['filter_value'] as $filter_value){
            if(empty($filter_value['filter_name'])){
                $this->setError(JText::_('J2STORE_FILTER_VALUE_IS_EMPTY'));
                $status = false;
            }
        }
        return $status;
    }
	public function save($data) {
		$app = JFactory::getApplication ();
		$task = $app->input->getString ( 'task' );
		if ($task == 'saveorder')
			return parent::save ( $data );
		
		if (parent::save ( $data )) {
			if (isset ( $this->otable->j2store_filtergroup_id )) {
				if (isset ( $data ['filter_value'] ) && count ( $data ['filter_value'] )) {

					$status = true;
					foreach ( $data ['filter_value'] as $filtervalue ) {
						$ovTable = F0FTable::getInstance ( 'filter', 'J2StoreTable' )->getClone();
						$ovTable->load ( $filtervalue ['j2store_filter_id'] );
						$filtervalue ['group_id'] = $this->otable->j2store_filtergroup_id;
						if (! $ovTable->save ( $filtervalue )) {
							$status = false;
						}
					}
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
		return true;
	}


    public function getSFQuery($overrideLimits=false) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query = $query->select('*')->from('#__j2store_filters');
        $filter_group_id = $this->getState('filter_group_id',0);
        if($filter_group_id > 0){
            $query->where('group_id ='.$db->q($filter_group_id));
        }

        return $query;
    }

    /** the following section is specially created for product layouts. And it only works with Joomla articles */

    public function getSFProducts() {
        if (empty($this->_sflist))
        {
            $query = $this->getSFQuery();
            $this->getState('filters.list.limit');
            $this->_sflist = $this->_getSFList((string) $query, $this->getStart(), $this->getState('filters.list.limit'));
        }
        return $this->_sflist;
    }

    public function getSFAllProducts(){
        if(empty($this->_sfalllist)){
            $query = $this->getSFQuery();
            $query->clear('select')->clear('order')->clear('limit')->select('#__j2store_filters.j2store_filter_id');
            $this->_sfalllist = $this->_getSFList((string) $query);
        }
        return $this->_sfalllist;
    }
    /**
     * Returns an object list
     *
     * @param   string   $query       The query
     * @param   integer  $limitstart  Offset from start
     * @param   integer  $limit       The number of records
     * @param   string   $group       The group by clause
     *
     * @return  array  Array of objects
     */
    protected function _getSFList($query, $limitstart = 0, $limit = 0, $group = '')
    {
        try {
            $this->_db->setQuery($query, $limitstart, $limit);
            $result = $this->_db->loadObjectList($group);
        } catch (Exception $e) {
            $result = array();
        }
        return $result;
    }


    public function getSFPagination()
    {

        if (empty($this->_sfpagination))
        {
            // Import the pagination library
            JLoader::import('joomla.html.pagination');
            // Prepare pagination values
            $total = $this->getSFPageTotal();
            //echo $this->getStart();
            // Create the pagination object
            $this->_sfpagination = new JPagination($total, $this->getStart(), $this->getState('filters.list.limit'),'filter');
        }
        return $this->_sfpagination;
    }


    /**
     * Get the number of all items
     *
     * @return  integer
     */
    public function getSFPageTotal()
    {
        if (is_null($this->_sfpagetotal))
        {
            //var_dump(debug_backtrace());
            $query = $this->getSFQuery();
            $query = clone $query;
            $query->clear('select')->clear('order')->clear('limit')->select('COUNT(*)');
            /* if ($query instanceof JDatabaseQuery
            && $query->type == 'select')
            {
                $query = clone $query;
                $query->clear('select')->clear('order')->clear('limit')->select('COUNT(*)');

                $this->_db->setQuery($query);
                $this->_sfpagetotal = (int) $this->_db->loadResult();
            } else { */

            // Otherwise fall back to inefficient way of counting all results.
            try {
                $this->_db->setQuery($query);
                $this->_db->execute();
                $this->_sfpagetotal = (int) $this->_db->loadResult();
            }catch (Exception $e) {
                $this->_sfpagetotal = 0;
            }
            //}

        }
        return $this->_sfpagetotal;
    }

    public function getStart()
    {
        $start = $this->getState('filters.list.start');
        $limit = $this->getState('filters.list.limit');
        $total = $this->getSFPageTotal();

        if ($start > $total - $limit)
        {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }

        return $start;
    }

}