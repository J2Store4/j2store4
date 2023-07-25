<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class Calculator {
	
	public $calculator; 
	
	
	public function __construct($type, $config=array()) {
		
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}
		
		$suffix = 'Calculator';
		
		if(empty($type)) $type = 'standard';
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$calculatorClass = ucfirst($type).$suffix;
		
		if (array_key_exists('input', $config))
		{
			if (!($config['input'] instanceof JInput))
			{
				if (!is_array($config['input']))
				{
					$config['input'] = (array) $config['input'];
				}
		
				$config['input'] = array_merge($_REQUEST, $config['input']);
				$config['input'] = new JInput($config['input']);
			}
		}
		else
		{
			$config['input'] = new JInput;
		}
		
		
		if (!class_exists($calculatorClass))
		{	
			$path = JPATH_ADMINISTRATOR.'/components/com_j2store/library/calculators/'.strtolower($calculatorClass).'.php';
			if(JFile::exists($path)) {
				require_once $path;
			}else {
				require_once JPATH_ADMINISTRATOR.'/components/com_j2store/library/calculators/standard.php';
			}
		}
		
		$result = new $calculatorClass($config);
		$this->setPricingCalculator($result);
	}
	
	
	public function setPricingCalculator($class) {
		$this->calculator = $class;
	}
	
	public function getPricingCalculator() {
		return $this->calculator;
	}
	
	public function calculate() {
		return $this->getPricingCalculator()->calculate();
	}
	
}