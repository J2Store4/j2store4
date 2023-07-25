<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');

class J2Currency {
  	private $code;
  	private $currencies = array();
  	private $input;
  	private $session;
	/*
	 * J2StoreCurrency instance
	 *
	 * since 2.6
	 */

  	protected static $instance;

  	public function __construct($config=array()) {

		$this->session = JFactory::getSession();
		$this->input = JFactory::getApplication()->input;
		
		if(count($this->currencies) < 1) {
			$rows = F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->enabled(1)->getList();
	    	foreach ($rows as $result) {
	      		$this->currencies[$result->currency_code] = (array) $result;
	    	}
		}
    	$currency = $this->input->get('currency');

		if (isset($currency) && (array_key_exists($currency, $this->currencies))) {
			$this->set($currency);
    	} elseif ($this->session->has('currency', 'j2store') && (array_key_exists($this->session->get('currency', '', 'j2store'), $this->currencies))) {
      		$this->set($this->session->get('currency', '', 'j2store'));
    	} else {
      		$this->set(J2Store::storeProfile()->get('config_currency'));
    	}
  	}

  	public static function getInstance($config=array())
  	{
  		if (!is_object(self::$instance))
  		{
  			self::$instance = new self($config);
  		}

  		return self::$instance;
  	}


  	public function set($currency) {
    	$this->code = $currency;

    	if (!$this->session->has('currency', 'j2store') || ($this->session->get('currency', '', 'j2store') != $currency)) {
      		$this->session->set('currency', $currency, 'j2store');
    	}
  	}

  	public function format($number, $currency = '', $value = '', $format = true) {
		if ($currency && $this->has($currency)) {
			$currency_position  = $this->currencies[$currency]['currency_position'];
			$currency_symbol  = $this->currencies[$currency]['currency_symbol'];
      		$decimal_place = $this->currencies[$currency]['currency_num_decimals'];
    	} else {
    		$currency_position  = $this->currencies[$this->code]['currency_position'];
    		$currency_symbol  = $this->currencies[$this->code]['currency_symbol'];
      		$decimal_place = $this->currencies[$this->code]['currency_num_decimals'];

			$currency = $this->code;
    	}

    	if ($value) {
      		$value = $value;
    	} else {
      		$value = $this->currencies[$currency]['currency_value'];
    	}

    	if ($value) {
      		$value = (float)$number * $value;
    	} else {
      		$value = $number;
    	}

    	$string = '';

    	if (($currency_position == 'pre') && ($format)) {
      		$string .= $currency_symbol;
    	}

		if ($format) {
			$decimal_point = $this->currencies[$currency]['currency_decimal'];
		} else {
			$decimal_point = '.';
		}

		if ($format) {
			$thousand_point = $this->currencies[$currency]['currency_thousands'];
		} else {
			$thousand_point = '';
		}

    	$string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);

  		if (($currency_position == 'post') && ($format)) {
      		$string .= $currency_symbol;
    	}

    	return $string;
  	}

  	public function convert($value, $from, $to) {
		if (isset($this->currencies[$from])) {
			$from = $this->currencies[$from]['currency_value'];
		} else {
			$from = 0;
		}

		if (isset($this->currencies[$to])) {
			$to = $this->currencies[$to]['currency_value'];
		} else {
			$to = 0;
		}

		return $value * ($to / $from);
  	}

  	public function getId($currency = '') {
		if (!$currency) {			
			return $this->currencies[$this->code]['j2store_currency_id'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['j2store_currency_id'];
		} else {
			return 0;
		}
  	}

	public function getSymbol($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_symbol'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_symbol'];
		} else {
			return '';
		}
  	}

	public function getSymbolPosition($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_position'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_position'];
		} else {
			return 'pre';
		}
  	}

	public function getDecimalPlace($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_num_decimals'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_num_decimals'];
		} else {
			return 0;
		}
  	}

	public function getThousandSysmbol($currency=''){
		if (!$currency) {
			return $this->currencies[$this->code]['currency_thousands'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_thousands'];
		} else {
			return 0;
		}
	}

  	public function getCode() {
    	return $this->code;
  	}

  	public function getValue($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_value'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_value'];
		} else {
			return 0;
		}
  	}

  	public function has($currency) {
    	return isset($this->currencies[$currency]);
  	}
  	
  	public static function getNumericCurrencies(){
  		$currencies = self::getNumericCode();
  		$result = array();
  		foreach($currencies as $key => $value){
  			$result[$key] = $key;
  		}
  		return $result;
  	}
  	
  	public static function getCurrenciesNumericCode($code){
  		$result = self::getNumericCode();
  		if(isset($code)){
  			$result = $result[$code];
  		}
  	
  		return $result;
  	}
  	
  	
  	
  	
  	
  	
  	/**
  	 * Method to get Numerice code
  	 * @param string $code alpha 3 digit code
  	 * @return int numberic code
  	 */
  	public static function getNumericCode(){
  		$result = array('AFN' => 4,
  				'ALL' => 8,
  				'DZD' => 12,
  				'USD' => 581,
  				'EUR' => 336,
  				'AOA' => 24,
  				'XCD' => 670,
  				'ARS' => 32,
  				'AMD' => 51,
  				'AWG' => 533,
  				'AUD' => 798,
  				'AZN' => 31,
  				'BSD' => 44,
  				'BHD' => 48,
  				'BDT' => 50,
  				'BBD' => 52,
  				'BYN' => 112,
  				'BZD' => 84,
  				'XOF' => 768,
  				'BMD' => 60,
  				'BTN' => 64,
  				'BOB' => 68,
  				'BAM' => 70,
  				'BWP' => 72,
  				'NOK' => 744,
  				'BRL' => 76,
  				'BND' => 96,
  				'BGN' => 100,
  				'BIF' => 108,
  				'KHR' => 116,
  				'XAF' => 178,
  				'CAD' => 124,
  				'CVE' => 132,
  				'KYD' => 136,
  				'CLP' => 152,
  				'CNY' => 156,
  				'COP' => 170,
  				'KMF' => 174,
  				'NZD' => 772,
  				'CRC' => 188,
  				'HRK' => 191,
  				'CUP' => 192,
  				'CYP' => 196,
  				'CZK' => 203,
  				'CDF' => 180,
  				'DKK' => 304,
  				'DJF' => 262,
  				'DOP' => 214,
  				'EGP' => 818,
  				'SVC' => 222,
  				'ERN' => 232,
  				'EEK' => 233,
  				'ETB' => 231,
  				'FKP' => 238,
  				'FJD' => 242,
  				'XPF' => 876,
  				'GMD' => 270,
  				'GEL' => 268,
  				'GHS' => 288,
  				'GIP' => 292,
  				'GTQ' => 320,
  				'GNF' => 324,
  				'GYD' => 328,
  				'HTG' => 332,
  				'HNL' => 340,
  				'HKD' => 344,
  				'HUF' => 348,
  				'ISK' => 352,
  				'INR' => 356,
  				'IDR' => 360,
  				'IRR' => 364,
  				'IQD' => 368,
  				'ILS' => 275,
  				'JMD' => 388,
  				'JPY' => 392,
  				'JOD' => 400,
  				'KZT' => 398,
  				'KES' => 404,
  				'KWD' => 414,
  				'KGS' => 417,
  				'LAK' => 418,
  				'LVL' => 428,
  				'LBP' => 422,
  				'LSL' => 426,
  				'LRD' => 430,
  				'LYD' => 434,
  				'CHF' => 756,
  				'LTL' => 440,
  				'MOP' => 446,
  				'MKD' => 807,
  				'MGA' => 450,
  				'MWK' => 454,
  				'MYR' => 458,
  				'MVR' => 462,
  				'MTL' => 470,
  				'MRO' => 478,
  				'MUR' => 480,
  				'MXN' => 484,
  				'MDL' => 498,
  				'MNT' => 496,
  				'MAD' => 732,
  				'MZN' => 508,
  				'MMK' => 104,
  				'NAD' => 516,
  				'NPR' => 524,
  				'ANG' => 530,
  				'NIO' => 558,
  				'NGN' => 566,
  				'KPW' => 408,
  				'OMR' => 512,
  				'PKR' => 586,
  				'PAB' => 591,
  				'PGK' => 598,
  				'PYG' => 600,
  				'PEN' => 604,
  				'PHP' => 608,
  				'PLN' => 616,
  				'QAR' => 634,
  				'RON' => 642,
  				'RUB' => 643,
  				'RWF' => 646,
  				'SHP' => 654,
  				'WST' => 882,
  				'STD' => 678,
  				'SAR' => 682,
  				'RSD' => 891,
  				'SCR' => 690,
  				'SLL' => 694,
  				'SGD' => 702,
  				'SKK' => 703,
  				'SBD' => 90,
  				'SOS' => 706,
  				'ZAR' => 710,
  				'GBP' => 826,
  				'KRW' => 410,
  				'LKR' => 144,
  				'SDG' => 736,
  				'SRD' => 740,
  				'SZL' => 748,
  				'SEK' => 752,
  				'SYP' => 760,
  				'TWD' => 158,
  				'TJS' => 762,
  				'TZS' => 834,
  				'THB' => 764,
  				'TOP' => 776,
  				'TTD' => 780,
  				'TND' => 788,
  				'TRY' => 792,
  				'TMM' => 795,
  				'UGX' => 800,
  				'UAH' => 804,
  				'AED' => 784,
  				'UYU' => 858,
  				'UZS' => 860,
  				'VUV' => 548,
  				'VEF' => 862,
  				'VND' => 704,
  				'YER' => 887,
  				'ZMK' => 894,
  				'ZWD' => 716,
		    'BTC'=> 999
  		);
  	
  		return $result;
  	}
}