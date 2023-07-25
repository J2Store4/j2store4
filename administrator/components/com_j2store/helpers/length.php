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

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class J2Length {

	private $lengths = array();

	/*
	 * J2StoreWeight instance
	*
	* since 2.6
	*/

	protected static $instance;

	public function __construct() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('*')
					->from('#__j2store_lengths');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row) {
      		$this->lengths[$row->j2store_length_id] = array(
        		'length_class_id' => $row->j2store_length_id,
        		'title'           => $row->length_title,
				'unit'            => $row->length_unit,
				'value'           => $row->length_value
      		);
    	}
  	}

  	public static function getInstance()
  	{
  		if (!is_object(self::$instance))
  		{
  			self::$instance = new self();
  		}

  		return self::$instance;
  	}


  	public function convert($value, $from, $to) {
		if ($from == $to) {
      		return $value;
		}

		if (isset($this->lengths[$from])) {
			$from = $this->lengths[$from]['value'];
		} else {
			$from = 1;
		}

		if (isset($this->lengths[$to])) {
			$to = $this->lengths[$to]['value'];
		} else {
			$to = 1;
		}
		
		return $value * ($to / $from);
  	}

	public function format($value, $length_class_id, $decimal_point = '.', $thousand_point = ',') {
		if (isset($this->lengths[$length_class_id])) {
    		return number_format($value, 2, $decimal_point, $thousand_point) . $this->lengths[$length_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}

	public function getUnit($length_class_id) {
		if (isset($this->lengths[$length_class_id])) {
    		return $this->lengths[$length_class_id]['unit'];
		} else {
			return '';
		}
	}
}