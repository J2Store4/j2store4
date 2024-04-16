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

// no direct access
defined('_JEXEC') or die('Restricted access');


class J2Email {

	public static $instance = null;
	protected $state;
	var $is_template_file = false;

	public function __construct($properties=null) {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}
	/**
	 * Method to send all the order related emails to the stake holders
	 * @param J2StoreOrder $order J2Store Order object
	 * */
	public function getOrderEmails($order , $receiver_type = '*'){
		/**
		 * 1. Get order emails by type
		 * 2. filter by language and process each mail template ( process tags )
		 * 3. prepare the mailer for each template (intialize the mailer object)
		 * 4. set the receivers ( customer emails / admins )
		 * 5. return the array
		 * */

		$params = J2Store::config();

		// 1. Get all the mail templates related to this order
		$mail_templates = $this->getEmailTemplates( $order , $receiver_type );

        //load language overrides
        $this->loadLanguageOverrides($order);

		// filter language
		$mail_templates = $this->filterByLanguage($order, $mail_templates );

		$is_admin_mail_sent = false;

		foreach ($mail_templates as &$template) {
			// process each mail template ( process tags )
			$template->mailer = $this->processTemplate($order, $template,$receiver_type);
			//set a default in case none is set.
			if(!isset($template->receiver_type) || empty($template->receiver_type)) $template->receiver_type = '*';

			if ( in_array($template->receiver_type, array('customer','*') ) && $receiver_type =='customer' ) {
				if (isset ( $order->user_email ) && ! empty ( $order->user_email ) && $template->mailer != false) {
					$template->mailer->addRecipient ( $order->user_email );
				}
			} elseif ( in_array($template->receiver_type, array('admin','*') ) && $receiver_type =='admin'  ) {
				$admin_emails = $params->get ( 'admin_email' );
				$admin_emails = explode ( ',', $admin_emails );
				$template->mailer->addRecipient ( $admin_emails );
				$template->mailer->addReplyTo ( $order->user_email );
			}
		}

		return $mail_templates;
	}

	/**
	 * Method to filter email templates by language
	 * @param J2StoreOrder  $order
	 * @param array 		$mail templates
	 * @return array filtered mail templates
	 * */
	protected function filterByLanguage($order, $mail_templates){

		$filtered_templates = array();
		$default_template_group = array();
		$all_lang_templates = array();
		$params = J2Store::config();

		// Look for desired languages
		$jLang = JFactory::getLanguage();
		$userLang = $order->customer_language;
		$languages = array(
			$userLang, $jLang->getTag(), $jLang->getDefault(), 'en-GB'
		);

		if(count($mail_templates) && J2Store::isPro() == 1)
		{
			// Pass 1 - Give match scores to each template
			$preferredIndex = null;
			$preferredScore = 0;

			foreach($mail_templates as $idx => $template)
			{
				// Get the language and level of this template
				$myLang = $template->language;

				// all language templates need not be filtered
				if ($template->language == '*') {
					$all_lang_templates[] = $template;
				}

				// Make sure the language matches one of our desired languages, otherwise skip it
				$langPos = array_search($myLang, $languages);
				if ($langPos === false)
				{
					continue;
				}
				$langScore = (5 - $langPos);

				$template->lang_score = $langScore ;
				$filtered_templates[$langScore][]=$template;
			}
		} else {

				$standard_template = array('j2store_emailtemplate_id' => 0 ,
				                          'email_type' => '',
				                          'receiver_type' => '*' ,
				                          'receiver' => '*' ,
				                          'orderstatus_id' => '*' ,
				                          'group_id' => '',
				                          'paymentmethod' => '*' ,
				                          'subject' => JText::_('J2STORE_ORDER_EMAIL_TEMPLATE_STANDARD_SUBJECT'),
				                          'body' => JText::_('J2STORE_ORDER_EMAIL_TEMPLATE_STANDARD_BODY'),
				                          'body_source' => 'html' ,
				                          'body_source_file' => -1 ,
				                          'language' => '*' ,
				                          'enabled' => 1 ,
				                          'ordering' => 1 ,
				                          'lang_score' => 1 ) ;
			if ( J2Store::isPro() == 1 ) {
				if ($params->get('send_default_email_template',1) == 1) {
					$default_template_group[] =  (object) $standard_template;	
				}
			}else{
				$default_template_group[] =  (object) $standard_template;
			}
		}
		// sort by language prefernce
		krsort($filtered_templates);

		$result = $default_template_group ;

		if ( count($filtered_templates) > 0 ) {
			foreach ($filtered_templates as $template_group) {
				if (count($template_group) == 0) {
					continue;
				}else {
					$result = $template_group;
					break;
				}
			}
		}

		$result = array_merge($result, $all_lang_templates) ;

		return $result;
	}

	protected function processTemplate($order, $template,$receiver_type = '*'){
		if(!isset($order->order_id) || empty($order->order_id)) return false;
		if(is_array ( $template )){
			$template = J2Store::platform()->toObject($template);

		}
		$config = JFactory::getConfig();
		$extras= array();

		if(isset($template->body_source) && $template->body_source == 'file') {
			$templateText = $this->getTemplateFromFile($template, $order);
			$this->is_template_file = true;
		}else {
			$templateText = $template->body;
		}

		$templateText = $this->processTags($templateText, $order, $extras,$receiver_type);
		$subject = $this->processTags($template->subject, $order, $extras,$receiver_type);

		$baseURL = str_replace('/administrator', '', JURI::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');
		$image_url = str_replace ( JUri::base (true), '', JUri::base () );
		$isHTML =true;
		// Get the mailer
		$mailer = $this->getMailer($isHTML);

		if(version_compare(JVERSION, '3.0', 'ge')) {
			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');
		} else {
			$mailfrom = $config->getValue('config.mailfrom');
			$fromname = $config->getValue('config.fromname');
		}

		// set the sender information
		$mailer->setSender(array( $mailfrom, $fromname ));

		// set encoding information
		$mailer->CharSet = 'utf-8';
		$mailer->Encoding = 'base64';

		$mailer->setSubject($subject);

		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
				$url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					$ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						// Relative path, make absolute
						//$url = $baseURL.ltrim($url,'/');
						$url = $image_url.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img'.$imageSubs[$url];
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		}

		$htmlExtra = '';
		$lang = JFactory::getLanguage();
		if($lang->isRTL()) {
			$htmlExtra = ' dir="rtl"';
		}
		$body = '<html'.$htmlExtra.'><head>'.
			'<meta http-equiv="Content-Type" content="text/html; charset='.$mailer->CharSet.'">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				</head>'.'<body>'.$templateText.'</body></html>';
		$mailer->setBody($body);
		$mailer->AltBody = $this->textVersion($body);
		return $mailer;
	}


	protected function loadEmailTemplate($order) {

		// Initialise
		$templateText = '';
		$subject = '';
		$loadLanguage = null;
		$isHTML = false;

		// Look for desired languages
		$jLang = JFactory::getLanguage();

		$userLang = $order->customer_language;
		$languages = array(
				$userLang, $jLang->getTag(), $jLang->getDefault(), 'en-GB', '*'
		);

		//load all templates
		$allTemplates = $this->getEmailTemplates($order);

		if(count($allTemplates) && J2Store::isPro() == 1)
		{
			// Pass 1 - Give match scores to each template
			$preferredIndex = null;
			$preferredScore = 0;

			foreach($allTemplates as $idx => $template)
			{
				// Get the language and level of this template
				$myLang = $template->language;

				// Make sure the language matches one of our desired languages, otherwise skip it
				$langPos = array_search($myLang, $languages);
				if ($langPos === false)
				{
					continue;
				}
				$langScore = (5 - $langPos);


				// Calculate the score
				$score = $langScore;
				if ($score > $preferredScore)
				{
					$loadLanguage = $myLang;
					$subject = $template->subject;

					if(isset($template->body_source) && $template->body_source == 'file') {
						$templateText = $this->getTemplateFromFile($template, $order);
						$this->is_template_file = true;

					}else {
						$templateText = $template->body;
					}

					$preferredScore = $score;

					$isHTML = true;
				}
			}
		} else {

			$isHTML = true;
			$templateText = JText::_('J2STORE_ORDER_EMAIL_TEMPLATE_STANDARD_BODY');
			$subject = JText::_('J2STORE_ORDER_EMAIL_TEMPLATE_STANDARD_SUBJECT');
		}
		return array($isHTML, $subject, $templateText, $loadLanguage);
	}


	public function processTags($text, $order, $extras=array(), $receiver_type = '*') {
        $platform = J2Store::platform();
		$params = J2Store::config();
		$currency = J2Store::currency();
		$order_model = F0FModel::getTmpInstance('Orders', 'J2StoreModel');

		// -- Get the site name
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$sitename = $config->get('sitename');
		} else {
			$sitename = $config->getValue('config.sitename');
		}

		//site url
		$baseURL = JURI::base();
		$subpathURL = JURI::base(true);
		//replace administrator string, if present
		$baseURL = str_replace('/administrator', '', $baseURL);
		$subpathURL = str_replace('/administrator', '', $subpathURL);
        if (version_compare(J2STORE_VERSION, '3.9.0', 'lt')) {
            $default_invoice_url = JRoute::_('index.php?option=com_j2store&view=myprofile',false);
        }else{
            $default_invoice_url = $platform->getMyprofileUrl();
        }
		//invoice url
		$url = str_replace('&amp;','&', $default_invoice_url);
		$url = str_replace('/administrator', '', $url);
		$url = ltrim($url, '/');
		$subpathURL = ltrim($subpathURL, '/');
		if(substr($url,0,strlen($subpathURL)+1) == "$subpathURL/") $url = substr($url,strlen($subpathURL)+1);
		$invoiceURL = rtrim($baseURL,'/').'/'.ltrim($url,'/');

		//order date
		//$order_date = JHTML::_('date', $order->created_on, $params->get('date_format', JText::_('DATE_FORMAT_LC1')));
		$tz = JFactory::getConfig()->get('offset');
		$date = JFactory::getDate($order->created_on);
		$date->setTimezone(new DateTimeZone($tz));
		$order_date = $date->format($params->get('date_format', JText::_('DATE_FORMAT_LC1')), true);

		//items table
		$items = $order_model->loadItemsTemplate($order,$receiver_type);
		$invoice_number = $order->getInvoiceNumber();
		//now process tags
		$orderinfo = $order->getOrderInformation();
		$shipping = $order->getOrderShippingRate();
		$ordercoupon = $order->getOrderCoupons();
		$status = F0FModel::getTmpInstance('Orderstatuses', 'J2StoreModel')->getItem($order->order_state_id);
		$coupon_code = '';
		if($ordercoupon) {
			$coupon_code = $ordercoupon[0]->coupon_code;
		}
		$orderinfo->billing_country_name = F0FModel::getTmpInstance('Countries','J2StoreModel')->getItem($orderinfo->billing_country_id)->country_name;
		$orderinfo->shipping_country_name = F0FModel::getTmpInstance('Countries','J2StoreModel')->getItem($orderinfo->shipping_country_id)->country_name;
		$orderinfo->billing_zone_name = F0FModel::getTmpInstance('Zones','J2StoreModel')->getItem($orderinfo->billing_zone_id)->zone_name;
		$orderinfo->shipping_zone_name = F0FModel::getTmpInstance('Zones','J2StoreModel')->getItem($orderinfo->shipping_zone_id)->zone_name;

		if(isset($order->order_params)) {
			$order_params = json_decode($order->order_params);
		}
		$bank_transfer_info = '';
		if(isset($order_params->payment_banktransfer)) {
			$bank_transfer_info = $order_params->payment_banktransfer;
		}
        if(empty($order->customer_language) || $order->customer_language == '*' || $order->customer_language == ''){
		    $language = JFactory::getLanguage();

        }else{
            $conf = JFactory::getConfig();
            $debug = $conf->get('debug_lang');
            $language = JLanguage::getInstance($order->customer_language, $debug);
            //$language = \Joomla\CMS\Language\Language::getInstance($order->customer_language,false);
        }

		$tags = array(
				"\\n"					=> "\n",
				'[SITENAME]'			=> $sitename,
				'[SITEURL]'				=> $baseURL,
				'[INVOICE_URL]'				=> $invoiceURL,
				'[ORDERID]'				=> $order->order_id,

				'[INVOICENO]'			=> $invoice_number,
				'[ORDERDATE]'			=> $order_date,
				'[ORDERSTATUS]'			=> $language->_($status->orderstatus_name),
				'[ORDERAMOUNT]'			=> $currency->format($order->get_formatted_grandtotal(), $order->currency_code, $order->currency_value ),

				'[CUSTOMER_NAME]'		=> $orderinfo->billing_first_name.' '.$orderinfo->billing_last_name,
				'[BILLING_FIRSTNAME]'	=> $orderinfo->billing_first_name,
				'[BILLING_LASTNAME]'	=> $orderinfo->billing_last_name,
				'[BILLING_EMAIL]'		=> $order->user_email,
				'[BILLING_ADDRESS_1]'	=> $orderinfo->billing_address_1,
				'[BILLING_ADDRESS_2]'	=> $orderinfo->billing_address_2,
				'[BILLING_CITY]'		=> $orderinfo->billing_city,
				'[BILLING_ZIP]'			=> $orderinfo->billing_zip,
				'[BILLING_COUNTRY]'		=> $language->_($orderinfo->billing_country_name),
				'[BILLING_STATE]'		=> $language->_($orderinfo->billing_zone_name),
				'[BILLING_COMPANY]'		=> $orderinfo->billing_company,
				'[BILLING_VATID]'		=> $orderinfo->billing_tax_number,
				'[BILLING_PHONE]'		=> $orderinfo->billing_phone_1,
				'[BILLING_MOBILE]'		=> $orderinfo->billing_phone_2,

				'[SHIPPING_FIRSTNAME]'	=> $orderinfo->shipping_first_name,
				'[SHIPPING_LASTNAME]'	=> $orderinfo->shipping_last_name,
				'[SHIPPING_ADDRESS_1]'	=> $orderinfo->shipping_address_1,
				'[SHIPPING_ADDRESS_2]'	=> $orderinfo->shipping_address_2,
				'[SHIPPING_CITY]'		=> $orderinfo->shipping_city,
				'[SHIPPING_ZIP]'		=> $orderinfo->shipping_zip,
				'[SHIPPING_COUNTRY]'	=> $language->_($orderinfo->shipping_country_name),
                '[SHIPPING_STATE]'		=> $language->_($orderinfo->shipping_zone_name),
				'[SHIPPING_COMPANY]'	=> $orderinfo->shipping_company,
				'[SHIPPING_VATID]'		=> $orderinfo->shipping_tax_number,
				'[SHIPPING_PHONE]'		=> $orderinfo->shipping_phone_1,
				'[SHIPPING_MOBILE]'		=> $orderinfo->shipping_phone_2,

				'[SHIPPING_METHOD]'		=> $language->_($shipping->ordershipping_name),
				'[SHIPPING_TYPE]'		=> $language->_($shipping->ordershipping_name),
				'[SHIPPING_TRACKING_ID]'	=> $shipping->ordershipping_tracking_id,

				'[CUSTOMER_NOTE]'		=> nl2br($order->customer_note),
				'[PAYMENT_TYPE]'		=> $language->_($order->orderpayment_type),
				'[ORDER_TOKEN]'			=> $order->token,
				'[TOKEN]'				=> $order->token,
				'[COUPON_CODE]'			=> $coupon_code,
				'[BANK_TRANSFER_INFORMATION]' => $bank_transfer_info,
				'[SHIPPING_TOTAL_WEIGHT]' => $order->getTotalShippingWeight(),
				'[ITEMS]'				=> $items,

		);

		// get the customer user group
		if ($order->user_id > 0) {
			$groupNames = J2Store::user()->getUserGroupNames($order->user_id);
			$customer_groups = implode(',', $groupNames);
			$customer_groups = trim($customer_groups, ',');
			$tags['CUSTOMER_GROUPS'] = $customer_groups;
		}
		
		$tags = array_merge($tags, $extras);
		foreach ($tags as $key => $value)
		{
            if (!empty($key) && !empty($value) && !empty($text)) {
                $text = str_replace($key, $value, $text);
            }
		}
		//process custom fields.
		//billing Format [CUSTOM_BILLING_FIELD:KEYNAME]
		$text = $this->processCustomFields($orderinfo, 'billing', $text,$language);
		//shipping Format [CUSTOM_SHIPPING_FIELD:KEYNAME]
		$text = $this->processCustomFields($orderinfo, 'shipping', $text,$language);

		//payment Format [CUSTOM_PAYMENT_FIELD:KEYNAME]
		$text = $this->processCustomFields($orderinfo, 'payment', $text,$language);

		J2Store::plugin()->event('AfterProcessTags', array(&$text, $order, $tags));

		//now we have unprocessed fields. remove any other square brackets found.
		preg_match_all("^\[(.*?)\]^",$text,$removeFields, PREG_PATTERN_ORDER);
		if(count($removeFields[1])) {
			foreach($removeFields[1] as $fieldName) {
			    if(!in_array($fieldName,array('if mso','endif'))){
                    $text = str_replace('['.$fieldName.']', '', $text);
                }
			}
		}
		return $text;

	}

	private function getDecodedFields($json) {
		$result = array();
		if(!empty($json)) {
			$registry = J2Store::platform()->getRegistry($json);
			$result = $registry->toArray();
		}
		return $result;
	}

	private function processCustomFields($row, $type, $text, $language = '') {
		if ($type == 'billing') {
			$field = 'all_billing';
		} elseif ($type == 'shipping') {
			$field = 'all_shipping';
		} elseif ($type == 'payment') {
			$field = 'all_payment';
		}
        if(empty($language)){
            $language = JFactory::getLanguage();
        }
		$fields = array ();
		if (! empty ( $row->$field ) && strlen ( $row->$field ) > 0) {

			$custom_fields = $this->getDecodedFields ( $row->$field );

			if (isset ( $custom_fields ) && count ( $custom_fields )) {
				foreach ( $custom_fields as $namekey => $field ) {
	
					if (! property_exists ( $row, $type . '_' . $namekey ) && ! property_exists ( $row, 'user_' . $namekey ) && $namekey != 'country_id' && $namekey != 'zone_id' && $namekey != 'option' && $namekey != 'task' && $namekey != 'view') {
						if(is_array($field['value'])){
                            $field['value'] = implode(',',$field['value']);
                        }
						$field['value'] = nl2br($field['value']);

						$fields [$namekey] = $field;
					}				
					
				}
			}
		}
        J2Store::plugin()->event("BeforeReplaceCustomFields",array(&$fields,&$text,$type));
		if (isset ( $fields ) && count ( $fields )) {
			foreach ( $fields as $namekey => $field ) {
				$string = '';
				if (is_array ( $field ['value'] )) {
					foreach ( $field ['value'] as $value ) {
						$string .= '-' . $language->_ ( $value ) . '\n';
					}
				} elseif (is_object ( $field ['value'] )) {
					// convert the object into an array
					$obj_array = J2Store::platform()->fromObject ( $field ['value'] );
					$string .= '\n';
					foreach ( $obj_array as $value ) {
						$string .= '- ' . JText::_ ( $value ) . '\n';
					}
				} elseif (is_string ( $field ['value'] ) && J2store::utilities ()->isJson ( stripcslashes ( $field ['value'] ) )) {
					$json_values = json_decode ( stripcslashes ( $field ['value'] ) );
					if (is_array ( $json_values )) {
						foreach ( $json_values as $value ) {
							$string .= '-' . $language->_ ( $value ) . '\n';
						}
					} else {
						$string .= $language->_ ( $field ['value'] );
					}
				} else {
					$string = $language->_ ( $field ['value'] );
				}

				if(isset($field['zone_type']) && !empty($field['value'])){
				    if($field['zone_type'] == 'zone'){
                        $string = $language->_($this->getZoneById($field['value'])->zone_name);
                    }elseif($field['zone_type'] == 'country'){
                        $string = $language->_($this->getCountryById($field['value'])->country_name);
                    }
                }

				$value = $language->_ ( $field ['label'] ) . ' : ' . $string;

				$tag_value = '[CUSTOM_' . strtoupper ( $type ) . '_FIELD:' . strtoupper ( $namekey ) . ']';

				$text = str_replace ( $tag_value, $value, $text );
			}
		}

		return $text;
	}
    public function getCountryById($country_id) {
        $country = F0FTable::getInstance('Country', 'J2StoreTable')->getClone();
        $country->load($country_id);
        return $country;
    }

    public function getZoneById($zone_id) {
        $zone = F0FTable::getInstance('Zone', 'J2StoreTable')->getClone();
        $zone->load($zone_id);
        return $zone;
    }
	public function getEmailTemplates($order, $receiver_type='*') {

 		$db = JFactory::getDbo();

			$query = $db->getQuery(true)
			->select('*')
			->from('#__j2store_emailtemplates')
			->where($db->qn('enabled').'='.$db->q(1))
			->where(' CASE WHEN orderstatus_id = '.$db->q($order->order_state_id) .' THEN orderstatus_id = '.$db->q($order->order_state_id).'
							ELSE orderstatus_id ="*" OR orderstatus_id =""
						END
					');
        if(isset($order->customer_group) && !empty($order->customer_group)) {
            $query->where(' CASE WHEN group_id IN( '.$order->customer_group.') THEN group_id IN('.$order->customer_group.')
                                ELSE group_id ="*" OR group_id ="1" OR group_id ="" OR group_id ="0"
                            END
                ');

        }
        $query->where(' CASE WHEN paymentmethod ='.$db->q($order->orderpayment_type).' THEN paymentmethod ='.$db->q($order->orderpayment_type).'
                        ELSE paymentmethod="*" OR paymentmethod=""
                    END
                ');

        $query->where(' CASE WHEN receiver_type ='.$db->q( $receiver_type ).' THEN receiver_type ='.$db->q( $receiver_type ).'
                        ELSE receiver_type="*" OR receiver_type=""
                    END
                ');
			$db->setQuery($query);
			try {
				$allTemplates = $db->loadObjectList();
			} catch (Exception $e) {
				$allTemplates = array();
			}
		return $allTemplates;
	}

	/**
	 * Creates a PHPMailer instance
	 *
	 * @param   boolean  $isHTML
	 *
	 * @return  PHPMailer  A mailer instance
	 */
	private static function &getMailer($isHTML = true)
	{
		$mailer = clone JFactory::getMailer();

		$mailer->IsHTML($isHTML);
		// Required in order not to get broken characters
		$mailer->CharSet = 'UTF-8';

		return $mailer;
	}

	private function initMailer() {
		$config = JFactory::getConfig();

		$mailer = $this->getMailer();
		$mailfrom = $config->get('mailfrom');
		$fromname = $config->get('fromname');
		$mailer->setSender(array( $mailfrom, $fromname ));

		return $mailer;
	}


	/**
	 * Method to get the pre-loaded mailer function
	 *
	 * @param object $order
	 * @return PHPMailer  A mailer instance
	 */

	public function getEmail($order) {

		if(!isset($order->order_id) || empty($order->order_id)) return false;
		$this->getOrderEmails($order);
		list($isHTML, $subject, $templateText, $loadLanguage) = $this->loadEmailTemplate($order);

		//load language overrides
		$this->loadLanguageOverrides($order);

		$extras= array();
		$templateText = $this->processTags($templateText, $order, $extras);

		$subject = $this->processTags($subject, $order, $extras);

		$baseURL = str_replace('/administrator', '', JURI::base());
		//replace administrator string, if present
		$baseURL = ltrim($baseURL, '/');
		// Get the mailer
		$mailer = $this->getMailer($isHTML);
		$mailer->setSubject($subject);

		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
					 $url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					 $ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						$base_path = str_replace('/administrator', '', JURI::base(true));
						//replace sub path
						$url = str_replace ( $base_path,'' ,$url );
						// Relative path, make absolute
						$url = $baseURL.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img'.$imageSubs[$url];
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;

		}
		$htmlExtra = '';
		$lang = JFactory::getLanguage();
		if($lang->isRTL()) {
			$htmlExtra = ' dir="rtl"';
		}
		$body = '<html'.$htmlExtra.'><head>'.
				'<meta http-equiv="Content-Type" content="text/html; charset='.$mailer->CharSet.'">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				</head>'.'<body>'.$templateText.'</body></html>';
		$mailer->setBody($body);
		$mailer->AltBody = $this->textVersion($body);
		return $mailer;
	}

	public function processInlineImages($templateText, &$mailer) {

		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if($number_of_matches > 0) {

			$baseURL = str_replace('/administrator', '', JURI::base());
			//replace administrator string, if present
			$baseURL = ltrim($baseURL, '/');

			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if($entry[1] > 0)
					$temp .= substr($templateText, $last_position, $entry[1]-$last_position);
				// Examine the current URL
				$url = $entry[0];
				if( (substr($url,0,7) == 'http://') || (substr($url,0,8) == 'https://') ) {
					// External link, skip
					$temp .= $url;
				} else {
					$ext = strtolower(JFile::getExt($url));
					if(!JFile::exists($url)) {
						// Relative path, make absolute
						$url = $baseURL.ltrim($url,'/');
					}
					if( !JFile::exists($url) || !in_array($ext, array('jpg','png','gif')) ) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if(!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							$mailer->AddEmbeddedImage($url, 'img'.$imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img'.$imageSubs[$url];
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if($last_position < strlen($templateText))
				$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;

		}

	}


	/**
	 * Sends error messages to site administrators
	 *
	 * @param string $message
	 * @param string $paymentData
	 * @return boolean
	 * @access protected
	 */
	public function sendErrorEmails($receiver, $subject, $body,  $cc = null, $bcc = null)
	{
		if(!isset($receiver)) return false;

		$mainframe = JFactory::getApplication();
		$config = JFactory::getConfig();

			$mailer = $this->initMailer();
			$mailer->addRecipient($receiver);
			$mailer->setSubject($subject);
			$mailer->setBody($body);
			$mailer->addCC($cc);
			$mailer->addCC($bcc);
		return $mailer->Send();
	}

	public function getTemplateFromFile($template, $order) {

		//sanity check
		if(isset($template->body_source) && $template->body_source == 'file') {

			if(empty($template->body_source_file)) return $template->body;

			//we have the file name
			jimport('joomla.filesystem.file');

			$app = JFactory::getApplication();
			$fileName = $template->body_source_file;

			$filePath = JPath::clean ( JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tpls/'.$fileName);

			//file exists
			if (!file_exists ( $filePath )) {
				return $template->body;
			}

			// Try to make the template file writable.
            if(function_exists('chown')) {
                $user = get_current_user();
                chown($filePath, $user);
            }

			JPath::setPermissions($filePath, '0644');

			if (!is_readable($filePath)) {
				return $template->body;
			}
			//the file is readable. get the contents
			$templateText = $this->_getLayout($filePath, $order);
			return $templateText;
		}

		return $template->body;
	}

	/**
	 * Gets the parsed layout file
	 *
	 * @param string $layout The name of  the layout file
	 * @param object $vars Variables to assign to
	 * @param string $plugin The name of the plugin
	 * @param string $group The plugin's group
	 * @return string
	 * @access protected
	 */
	function _getLayout($layout, $order)
	{
		ob_start();
		$this->loadLanguageOverrides($order);
		include($layout);
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	function loadLanguageOverrides($order) {

		$extension = 'com_j2store';
		$jlang = JFactory::getLanguage();
		// -- English (default fallback)
		$jlang->load($extension, JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, 'en-GB', true);
		// -- Default site language
		$jlang->load($extension, JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		// -- Current site language
		$jlang->load($extension, JPATH_ADMINISTRATOR, null, true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, null, true);

		$jlang->load($extension, JPATH_ADMINISTRATOR, $order->customer_language, true);
		$jlang->load($extension.'.override', JPATH_ADMINISTRATOR, $order->customer_language, true);

	}

	/**
	 * Method to extract a plain text email from a html email
	 * strip html tags and other unwanted html stuff
	 * @param 	string $html html content of the mail body
	 * @return 	string plain text content of the mail body
	 * */
	function textVersion($html){

		$html = preg_replace('# +#',' ',$html);
		$html = str_replace(array("\n","\r","\t"),'',$html);
		$removeScript = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
		$removeStyle = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
		$removeStrikeTags =  '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
		$replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
		$replaceByStars = '#< *li[^>]*>#Ui';
		$replaceByReturnChar1 = '#< */ *(li|td|tr|div|p)[^>]*> *< *(li|td|tr|div|p)[^>]*>#Ui';
		$replaceByReturnChar = '#< */? *(br|p|h1|h2|h3|li|ul|h4|h5|h6|tr|td|div)[^>]*>#Ui';
		$replaceLinks = '/< *a[^>]*href *= *"([^"]*)"[^>]*>(.*)< *\/ *a *>/Uis';
		$text = preg_replace(array($removeScript,$removeStyle,$removeStrikeTags,$replaceByTwoReturnChar,$replaceByStars,$replaceByReturnChar1,$replaceByReturnChar,$replaceLinks),array('','','',"\n\n","\n* ","\n","\n",'${2} ( ${1} )'),$html);
		$text = str_replace(array(" ","&nbsp;"),' ',strip_tags($text));
		$text = trim(@html_entity_decode($text,ENT_QUOTES,'UTF-8'));
		$text = preg_replace('# +#',' ',$text);
		$text = preg_replace('#\n *\n\s+#',"\n\n",$text);
		return $text;
	}
}