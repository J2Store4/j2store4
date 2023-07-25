<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();

class modJ2storeChartHelper{

	/**
	 * Method  for Getting Data - year.
	 * @access public
	 * @return array after executing the queries
	 */
	function getYear($order_status)
	{
		if(!isset($order_status) || empty($order_status)) {
			$order_status = array('*');
		}

		if(is_array($order_status)){
			// array. So implode it.
			$requested_order_status = implode(',',$order_status);
		}else{
			//seems a string. So just assign.
			$requested_order_status = $order_status;
		}


		//Joomla platform factory class.
		$db= JFactory::getDbo();

		/**
		 * Get the current query object or a new JDatabaseQuery object.
		 *
		 * @param   boolean  $new  False to return the current query object, True to return a new JDatabaseQuery object.
		 */
		$query = $db->getQuery(true);

		//sum of order payment amount.
		$query->select('o.order_state_id ,SUM(o.order_total) AS total,YEAR(o.created_on) AS dyear,COUNT(*) AS total_num_orders')->from('#__j2store_orders AS o');

		if(!in_array('*' ,$order_status)){
			$query->where('o.order_state_id IN ('. $requested_order_status .')');
		}

		// To load only the Normal orders
		$query->where('o.order_type = \'normal\'');

		//$query->where('o.order_state_id=1');
		//group by year in created date column.
		$query->group('YEAR(o.created_on)');


		/**
		 * Sets the SQL statement string for later execution.
		 *
		 * @param   mixed    $query   The SQL statement to set either as a JDatabaseQuery object or a string.
		 */
		$db->setQuery($query);

		/**
		 * Method to get an array of the result set rows from the database query where each row is an associative array
		 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
		 * a sequential numeric array.
		 */
		$row=$db->loadAssocList();

		return $row;
	}

	/**
	 * Method  for Getting Data - month.
	 * @access public
	 * @return array after executing the queries
	 */
	function getMonth($order_status)
	{

		if(!isset($order_status) || empty($order_status)) {
			$order_status = array('*');
		}

		if(is_array($order_status)){
			// array. So implode it.
			$requested_order_status = implode(',',$order_status);
		}else{
			//seems a string. So just assign.
			$requested_order_status = $order_status;
		}
		//Joomla platform factory class.
		$db= JFactory::getDbo();

		$today = getdate();
		$year =  $today['year'];
		/**
		 * Get the current query object or a new JDatabaseQuery object.
		 *
		 * @param   boolean  $new  False to return the current query object, True to return a new JDatabaseQuery object.
		 */
		$query = $db->getQuery(true);

		//sum of order payment amount.
		$query->select('o.order_state_id,SUM(o.order_total) AS total, DATE_FORMAT(o.created_on,"%M") AS dmonth,COUNT(*) AS total_num_orders')->from('#__j2store_orders AS o');
		$query->where("YEAR(DATE(o.created_on)) = ".$year);
		//$query->where('o.order_state_id=1');

		if(!in_array('*' ,$order_status)){
			$query->where('o.order_state_id IN ('. $requested_order_status .')');
		}

		// To load only the Normal orders
		$query->where('o.order_type = \'normal\'');

		//group by year in created date column.
		$query->group('MONTH(o.created_on)');

		/**
		 * Sets the SQL statement string for later execution.
		 *
		 * @param   mixed    $query   The SQL statement to set either as a JDatabaseQuery object or a string.
		 */
		$db->setQuery($query);

		/**
		 * Method to get an array of the result set rows from the database query where each row is an associative array
		 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
		 * a sequential numeric array.
		 */
		$row=$db->loadAssocList();

		return $row;
	}

	/**
	 * Method  for Getting Data - date.
	 * @access public
	 * @return array after executing the queries
	 */
	function getDay($order_status)
	{

		if(!isset($order_status) || empty($order_status)) {
			$order_status = array('*');
		}

		if(is_array($order_status)){
			// array. So implode it.
			$requested_order_status = implode(',',$order_status);
		}else{
			//seems a string. So just assign.
			$requested_order_status = $order_status;
		}
		/**
		 * joomla platform factory class.
		 */
		$db=JFactory::getDbo();
		$today = getdate();
		$month =  $today['mon'];
		$year =  $today['year'];
		/**
		 * Get the current query object or a new JDatabaseQuery object.
		 *
		 * @param   boolean  $new  False to return the current query object, True to return a new JDatabaseQuery object.
		 */
		$query = $db->getQuery(true);
		//sum of order payment amount. DATE_FORMAT(entrydate, '%M')
		$query->select('o.order_state_id ,SUM(o.order_total) AS total,DATE_FORMAT(o.created_on,"%d") AS dday, COUNT(*) AS total_num_orders')->from('#__j2store_orders AS o');
		$query->where("YEAR(DATE(o.created_on)) = ".$year." AND MONTH(DATE(o.created_on)) = ".$month);
		//$query->where('o.order_state_id=1');

		if(!in_array('*' ,$order_status)){
			$query->where('o.order_state_id IN ('. $requested_order_status .')');
		}

		// To load only the Normal orders
		$query->where('o.order_type = \'normal\'');

		//group by year in created date column.
		$query->group('DAY(o.created_on)');


		/**
		 * Sets the SQL statement string for later execution.
		 *
		 * @param   mixed    $query   The SQL statement to set either as a JDatabaseQuery object or a string.
		 */
		$db->setQuery($query);

		/**
		 * Method to get an array of the result set rows from the database query where each row is an associative array
		 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
		 * a sequential numeric array.
		 */
		$rows=$db->loadAssocList();


		if(!$rows){
			foreach($rows as $row){
				$row['dday'] = 1;
				$row['total'] = 0;
			}
		}

		return $rows;

	}

	function getOrders($order_status){
		if(!isset($order_status) || empty($order_status)) {
			$order_status = array('*');
		}

		if(is_array($order_status)){
			// array. So implode it.
			$requested_order_status = implode(',',$order_status);
		}else{
			//seems a string. So just assign.
			$requested_order_status = $order_status;
		}

		//get filters
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('o.created_on');
		$query->select('o.order_state_id,SUM(o.order_total) AS total,DATE_FORMAT(o.created_on,"%a,%c %Y") AS dday, COUNT(*) AS total_num_orders');
		//$query->where('o.order_state_id=0');

		if(!in_array('*' ,$order_status)){
			$query->where('o.order_state_id IN ('. $requested_order_status .')');
		}

		// To load only the Normal orders
		$query->where('o.order_type = \'normal\'');

		$query->from('#__j2store_orders AS o');
		$query->order('o.created_on DESC')->order('o.j2store_order_id DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}