--
-- Table structure for table `#_j2store_addresses`
--

CREATE TABLE IF NOT EXISTS `#__j2store_addresses` (
`j2store_address_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `zone_id` varchar(255) NOT NULL,
  `country_id` varchar(255) NOT NULL,
  `phone_1` varchar(255) NOT NULL,
  `phone_2` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `tax_number` varchar(255) NOT NULL,
   PRIMARY KEY (`j2store_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_carts` (
  `j2store_cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `cart_type` varchar(255) NOT NULL DEFAULT 'cart',
  `created_on` datetime NOT NULL,
  `modified_on` datetime NOT NULL,
  `customer_ip` varchar(255) NOT NULL,
  `cart_params` text NOT NULL,
  `cart_browser` text NOT NULL,
  `cart_analytics` text NOT NULL,
  PRIMARY KEY (`j2store_cart_id`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_cartitems` (
  `j2store_cartitem_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `variant_id` int(11) unsigned NOT NULL,
  `vendor_id` int(11) unsigned NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `cartitem_params` text NOT NULL,
  `product_qty` decimal(12,4) NOT NULL,
  `product_options` text NOT NULL,
  PRIMARY KEY (`j2store_cartitem_id`),
  KEY `cart_id` (`cart_id`),
  KEY `product_id` (`product_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_configurations` (
  `config_meta_key` varchar(255) NOT NULL,
  `config_meta_value` text,
  `config_meta_default` text NULL DEFAULT NULL,
  PRIMARY KEY (`config_meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_countries`
--

CREATE TABLE IF NOT EXISTS `#__j2store_countries` (
`j2store_country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_name` varchar(255) NOT NULL,
  `country_isocode_2` varchar(5) NOT NULL,
  `country_isocode_3` varchar(5) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
 PRIMARY KEY (`j2store_country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_coupons`
--
CREATE TABLE IF NOT EXISTS `#__j2store_coupons` (
  `j2store_coupon_id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_name` varchar(255) NOT NULL,
  `coupon_code` varchar(255) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `value` decimal(12,4) NOT NULL,
  `value_type` varchar(255) NOT NULL,
  `max_value` varchar(255) NOT NULL,
  `free_shipping` int(11) NOT NULL,
  `max_uses` int(11) NOT NULL,
  `logged` int(11) NOT NULL,
  `max_customer_uses` int(11) NOT NULL,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `product_category` varchar(255) NOT NULL,
  `products` varchar(255) NOT NULL,
  `min_subtotal` varchar(255) NOT NULL,
  `users` text NOT NULL,
  `mycategory` text NOT NULL, 
  `brand_ids` text NOT NULL, 
  PRIMARY KEY (`j2store_coupon_id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_currencies`
--

CREATE TABLE IF NOT EXISTS `#__j2store_currencies` (
  `j2store_currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `currency_title` varchar(32) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `currency_position` varchar(12) NOT NULL,
  `currency_symbol` varchar(255) NOT NULL,
  `currency_num_decimals` int(4) NOT NULL,
  `currency_decimal` varchar(12) NOT NULL,
  `currency_thousands` char(1) NOT NULL,
  `currency_value` float(15,8) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) unsigned NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`j2store_currency_id`),
  UNIQUE KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__j2store_customfields` (
  `j2store_customfield_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `field_table` varchar(50) DEFAULT NULL,
  `field_name` varchar(250) NOT NULL,
  `field_namekey` varchar(50) NOT NULL,
  `field_type` varchar(50) DEFAULT NULL,
  `field_value` longtext NOT NULL,
  `enabled` int(11) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `field_options` text,
  `field_core` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `field_required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `field_default` varchar(250) DEFAULT NULL,
  `field_access` varchar(255) NOT NULL DEFAULT 'all',
  `field_categories` varchar(255) NOT NULL DEFAULT 'all',
  `field_with_sub_categories` tinyint(1) NOT NULL DEFAULT '0',
  `field_frontend` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `field_backend` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `field_display` text NOT NULL,
  `field_display_billing` smallint(5) NOT NULL DEFAULT '0',
  `field_display_register` smallint(5) NOT NULL DEFAULT '0',
  `field_display_shipping` smallint(5) NOT NULL DEFAULT '0',
  `field_display_guest` smallint(5) NOT NULL DEFAULT '0',
  `field_display_guest_shipping` smallint(5) NOT NULL DEFAULT '0',
  `field_display_payment` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`j2store_customfield_id`),
  UNIQUE KEY `field_namekey` (`field_namekey`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `#__j2store_customfields`
--

INSERT IGNORE INTO `#__j2store_customfields` (`j2store_customfield_id`, `field_table`, `field_name`, `field_namekey`, `field_type`, `field_value`, `enabled`, `ordering`, `field_options`, `field_core`, `field_required`, `field_default`, `field_access`, `field_categories`, `field_with_sub_categories`, `field_frontend`, `field_backend`, `field_display`, `field_display_billing`, `field_display_register`, `field_display_shipping`, `field_display_guest`, `field_display_guest_shipping`, `field_display_payment`) VALUES
(1, 'address', 'J2STORE_ADDRESS_FIRSTNAME', 'first_name', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(2, 'address', 'J2STORE_ADDRESS_LASTNAME', 'last_name', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(3, 'address', 'J2STORE_EMAIL', 'email', 'email', '', 1, 99, 'a:8:{s:12:"errormessage";s:36:"J2STORE_VALIDATION_ENTER_VALID_EMAIL";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 0, 1, 0, 0),
(4, 'address', 'J2STORE_ADDRESS_LINE1', 'address_1', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(5, 'address', 'J2STORE_ADDRESS_LINE2', 'address_2', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(6, 'address', 'J2STORE_ADDRESS_CITY', 'city', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(7, 'address', 'J2STORE_ADDRESS_ZIP', 'zip', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(8, 'address', 'J2STORE_ADDRESS_PHONE', 'phone_1', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:0:"";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(9, 'address', 'J2STORE_ADDRESS_MOBILE', 'phone_2', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(10, 'address', 'J2STORE_ADDRESS_COMPANY_NAME', 'company', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:0:"";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(11, 'address', 'J2STORE_ADDRESS_TAX_NUMBER', 'tax_number', 'text', '', 1, 99, 'a:8:{s:12:"errormessage";s:0:"";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 0, '', 'all', 'all', 0, 0, 1, '', 1, 1, 0, 1, 0, 0),
(12, 'address', 'J2STORE_ADDRESS_COUNTRY', 'country_id', 'zone', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:7:"country";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '223', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0),
(13, 'address', 'J2STORE_ADDRESS_ZONE', 'zone_id', 'zone', '', 1, 99, 'a:8:{s:12:"errormessage";s:22:"J2STORE_FIELD_REQUIRED";s:9:"filtering";s:1:"0";s:9:"maxlength";s:1:"0";s:4:"size";s:0:"";s:4:"cols";s:0:"";s:9:"zone_type";s:4:"zone";s:6:"format";s:0:"";s:8:"readonly";s:1:"0";}', 1, 1, '3624', 'all', 'all', 0, 0, 1, '', 1, 1, 1, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_emailtemplates`
--

CREATE TABLE IF NOT EXISTS `#__j2store_emailtemplates` (
`j2store_emailtemplate_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_type` varchar(255) NOT NULL,
  `receiver_type` varchar(255) NOT NULL DEFAULT '*',
  `orderstatus_id` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `paymentmethod` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `body_source` varchar(255) NOT NULL,
  `body_source_file` varchar(255) NOT NULL,
  `language` varchar(10) NOT NULL DEFAULT '*',
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_emailtemplate_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `#__j2store_emailtemplates`
--

INSERT IGNORE INTO `#__j2store_emailtemplates` (`j2store_emailtemplate_id`, `email_type`,  `orderstatus_id`, `group_id`, `paymentmethod`, `subject`, `body`, `language`, `enabled`, `ordering`) VALUES
(1, '', '*',  '', '*', 'Hello [BILLING_FIRSTNAME] [BILLING_LASTNAME], your order has been placed with [SITENAME]', '<table style="width: 100%;" border="0" cellspacing="0" cellpadding="2">\r\n<tbody>\r\n<tr valign="top">\r\n<td colspan="2" rowspan="1">\r\n<p>Hello [BILLING_FIRSTNAME] [BILLING_LASTNAME], we thank you for placing an order with [SITENAME]. Your Order ID is:<strong>[ORDERID]</strong>. We have now started processing your order. The details of your order are as follows:</p>\r\n</td>\r\n</tr>\r\n<tr valign="top">\r\n<td>\r\n<h3>Order Information</h3>\r\n<p><strong>Order ID: </strong>[ORDERID]</p>\r\n<p><strong>Invoice Number: </strong>[INVOICENO]</p>\r\n<p><strong>Date: </strong>[ORDERDATE]</p>\r\n<p><strong>Order Amount: </strong>[ORDERAMOUNT]</p>\r\n<p><strong>Order Status: </strong>[ORDERSTATUS]</p>\r\n<p> </p>\r\n</td>\r\n<td>\r\n<h3>Customer Information</h3>\r\n<p>[BILLING_FIRSTNAME] [BILLING_LASTNAME]</p>\r\n<p>[BILLING_ADDRESS_1] [BILLING_ADDRESS_2]</p>\r\n<p>[BILLING_CITY], [BILLING_ZIP]</p>\r\n<p>[BILLING_STATE] [BILLING_COUNTRY]</p>\r\n<p>[BILLING_PHONE] [BILLING_MOBILE]</p>\r\n<p>[BILLING_COMPANY]</p>\r\n</td>\r\n</tr>\r\n<tr valign="top">\r\n<td>\r\n<h3>Payment Information</h3>\r\n<p><strong>Payment Type: </strong>[PAYMENT_TYPE]</p>\r\n</td>\r\n<td>\r\n<h3>Shipping Information</h3>\r\n<p>[SHIPPING_FIRSTNAME] [SHIPPING_LASTNAME]</p>\r\n<p>[SHIPPING_ADDRESS_1] [SHIPPING_ADDRESS_2]</p>\r\n<p>[SHIPPING_CITY], [SHIPPING_ZIP]</p>\r\n<p>[SHIPPING_STATE] [SHIPPING_COUNTRY]</p>\r\n<p>[SHIPPING_PHONE] [SHIPPING_MOBILE]</p>\r\n<p>[SHIPPING_COMPANY]</p>\r\n<p>[SHIPPING_METHOD]</p>\r\n</td>\r\n</tr>\r\n<tr valign="top">\r\n<td colspan="2" rowspan="1">\r\n<p>[ITEMS]</p>\r\n</td>\r\n</tr>\r\n<tr valign="top">\r\n<td colspan="2">\r\n<p>For any queries and details please get in touch with us. We will be glad to be of service. You can also view the order details by visiting [INVOICE_URL]</p>\r\n<p>You can use your email address and the following token to view the order [ORDER_TOKEN]</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>', '*', 1, 1);


CREATE TABLE IF NOT EXISTS `#__j2store_geozonerules` (
  `j2store_geozonerule_id` int(11) NOT NULL AUTO_INCREMENT,
  `geozone_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,  
  PRIMARY KEY (`j2store_geozonerule_id`),
  UNIQUE KEY `georule` (`geozone_id`,`country_id`,`zone_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


--
-- Table structure for table `#__j2store_filtergroups`
--

CREATE TABLE IF NOT EXISTS `#__j2store_filtergroups` (
  `j2store_filtergroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  PRIMARY KEY (`j2store_filtergroup_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `#__j2store_filters`
--

CREATE TABLE IF NOT EXISTS `#__j2store_filters` (
  `j2store_filter_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `filter_name` varchar(255) DEFAULT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`j2store_filter_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `#__j2store_geozones` (
  `j2store_geozone_id` int(11) NOT NULL AUTO_INCREMENT,
  `geozone_name` varchar(255) NOT NULL,
  `enabled` int(11) NOT NULL,  
  PRIMARY KEY (`j2store_geozone_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_invoicetemplates`
--

CREATE TABLE IF NOT EXISTS `#__j2store_invoicetemplates` (
  `j2store_invoicetemplate_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `invoice_type` varchar(255) NOT NULL,
  `orderstatus_id` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  `paymentmethod` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `language` varchar(10) NOT NULL DEFAULT '*',
  `enabled` tinyint(4) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`j2store_invoicetemplate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_lengths`
--

CREATE TABLE IF NOT EXISTS `#__j2store_lengths` (
  `j2store_length_id` int(11) NOT NULL AUTO_INCREMENT,
  `length_title` varchar(255) NOT NULL,
  `length_unit` varchar(4) NOT NULL,
  `length_value` decimal(15,8) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`j2store_length_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_manufacturers`
--

CREATE TABLE IF NOT EXISTS `#__j2store_manufacturers` (
`j2store_manufacturer_id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_manufacturer_id`),
   KEY `address_id` (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_options`
--

CREATE TABLE IF NOT EXISTS `#__j2store_options` (
`j2store_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `option_unique_name` varchar(255) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  `option_params` text,
   PRIMARY KEY (`j2store_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_optionvalues`
--

CREATE TABLE IF NOT EXISTS `#__j2store_optionvalues` (
`j2store_optionvalue_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `optionvalue_name` varchar(255) NOT NULL,
  `optionvalue_image` longtext NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_optionvalue_id`),
   KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_orderdiscounts` (
	`j2store_orderdiscount_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` varchar(255) NOT NULL,
	`discount_type` varchar(255) NOT NULL,
	`discount_entity_id` int(11) unsigned NOT NULL,
	`discount_title` varchar(255) NOT NULL,
	`discount_code` varchar(255) NOT NULL,
	`discount_value` varchar(255) NOT NULL,
	`discount_value_type` varchar(255) NOT NULL,
	`discount_customer_email` varchar(255) NOT NULL,
	`user_id` int(11) unsigned NOT NULL,
	`discount_amount` decimal(12,4) NOT NULL,
	`discount_params` text NOT NULL,
	PRIMARY KEY (`j2store_orderdiscount_id`),
	KEY `order_id` (`order_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_orderdownloads` (
  `j2store_orderdownload_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `limit_count` bigint(20) NOT NULL,
  `access_granted` datetime NOT NULL,
  `access_expires` datetime NOT NULL,
  PRIMARY KEY (`j2store_orderdownload_id`),
  KEY `download_order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_orderhistories` (
  `j2store_orderhistory_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `order_state_id` int(11) NOT NULL,
  `notify_customer` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`j2store_orderhistory_id`),
  KEY `history_order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__j2store_orderinfos` (
  `j2store_orderinfo_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `billing_company` varchar(255) DEFAULT NULL,
  `billing_last_name` varchar(255) DEFAULT NULL,
  `billing_first_name` varchar(255) DEFAULT NULL,
  `billing_middle_name` varchar(255) DEFAULT NULL,
  `billing_phone_1` varchar(255) DEFAULT NULL,
  `billing_phone_2` varchar(255) DEFAULT NULL,
  `billing_fax` varchar(255) DEFAULT NULL,
  `billing_address_1` varchar(255) NOT NULL DEFAULT '',
  `billing_address_2` varchar(255) DEFAULT NULL,
  `billing_city` varchar(255) NOT NULL DEFAULT '',
  `billing_zone_name` varchar(255) NOT NULL DEFAULT '',
  `billing_country_name` varchar(255) NOT NULL DEFAULT '',
  `billing_zone_id` int(11) NOT NULL DEFAULT '0',
  `billing_country_id` int(11) NOT NULL DEFAULT '0',
  `billing_zip` varchar(255) NOT NULL DEFAULT '',
  `billing_tax_number` varchar(255) DEFAULT NULL,
  `shipping_company` varchar(255) DEFAULT NULL,
  `shipping_last_name` varchar(255) DEFAULT NULL,
  `shipping_first_name` varchar(255) DEFAULT NULL,
  `shipping_middle_name` varchar(255) DEFAULT NULL,
  `shipping_phone_1` varchar(255) DEFAULT NULL,
  `shipping_phone_2` varchar(255) DEFAULT NULL,
  `shipping_fax` varchar(255) DEFAULT NULL,
  `shipping_address_1` varchar(255) NOT NULL DEFAULT '',
  `shipping_address_2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(255) NOT NULL DEFAULT '',
  `shipping_zip` varchar(255) NOT NULL,
  `shipping_zone_name` varchar(255) NOT NULL DEFAULT '',
  `shipping_country_name` varchar(255) NOT NULL DEFAULT '',
  `shipping_zone_id` int(11) NOT NULL DEFAULT '0',
  `shipping_country_id` int(11) NOT NULL DEFAULT '0',
  `shipping_id` varchar(255) NOT NULL DEFAULT '',
  `shipping_tax_number` varchar(255) DEFAULT NULL,
  `all_billing` longtext NOT NULL,
  `all_shipping` longtext NOT NULL,
  `all_payment` longtext NOT NULL,
  PRIMARY KEY (`j2store_orderinfo_id`),
  KEY `idx_orderinfo_order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_orderitemattributes` (
  `j2store_orderitemattribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `orderitem_id` int(11) NOT NULL,
  `productattributeoption_id` int(11) NOT NULL,
  `productattributeoptionvalue_id` int(11) NOT NULL,
  `orderitemattribute_name` varchar(255) NOT NULL,
  `orderitemattribute_value` varchar(255) NOT NULL,
  `orderitemattribute_prefix` varchar(1) NOT NULL,
  `orderitemattribute_price` decimal(12,5) NOT NULL,
  `orderitemattribute_code` varchar(255) NOT NULL,
  `orderitemattribute_type` varchar(255) NOT NULL,
  PRIMARY KEY (`j2store_orderitemattribute_id`),
  KEY `attribute_orderitem_id` (`orderitem_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_orderitems` (
  `j2store_orderitem_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `cartitem_id` int(11) unsigned NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_type` varchar(255) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `orderitem_sku` varchar(255) NOT NULL,
  `orderitem_name` varchar(255) NOT NULL,
  `orderitem_attributes` text NOT NULL,
  `orderitem_quantity` varchar(255) NOT NULL,
  `orderitem_taxprofile_id` int(11) NOT NULL,
  `orderitem_per_item_tax` decimal(12,4) NOT NULL,
  `orderitem_tax` decimal(12,4) NOT NULL,
  `orderitem_discount` decimal(12,4) NOT NULL,
  `orderitem_discount_tax` decimal(12,4) NOT NULL,
  `orderitem_price` decimal(12,5) NOT NULL,
  `orderitem_option_price` decimal(12,4) NOT NULL,
  `orderitem_finalprice` decimal(12,4) NOT NULL,
  `orderitem_finalprice_with_tax` decimal(12,4) NOT NULL,
  `orderitem_finalprice_without_tax` decimal(12,4) NOT NULL,
  `orderitem_params` text NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `orderitem_weight` varchar(255) NOT NULL,
  `orderitem_weight_total` varchar(255) NOT NULL,
  PRIMARY KEY (`j2store_orderitem_id`),
  KEY `orderitem_order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_orders` (
  `j2store_order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `cart_id` int(11) unsigned NOT NULL,
  `invoice_prefix` varchar(255) NOT NULL,
  `invoice_number` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `order_total` decimal(12,4) NOT NULL,
  `order_subtotal` decimal(12,4) NOT NULL,
  `order_tax` decimal(12,4) NOT NULL,
  `order_shipping` decimal(12,4) NOT NULL,
  `order_shipping_tax` decimal(12,4) NOT NULL,
  `order_discount` decimal(12,4) NOT NULL,
  `order_credit` decimal(12,4) NOT NULL,
  `order_surcharge` decimal(12,4) NOT NULL,
  `orderpayment_type` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `transaction_status` varchar(255) NOT NULL,
  `transaction_details` text NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(255) NOT NULL,
  `currency_value` decimal(12,4) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `is_shippable` int(11) NOT NULL,
  `is_including_tax` int(11) NOT NULL,
  `customer_note` text NOT NULL,
  `customer_language` varchar(255) NOT NULL,
  `customer_group` varchar(255) NOT NULL,
  `order_state_id` int(11) NOT NULL,
  `order_state` varchar(255) NOT NULL COMMENT 'Legacy compatibility',
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `modified_on` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  PRIMARY KEY (`j2store_order_id`),
  KEY `idx_order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__j2store_ordershippings` (
  `j2store_ordershipping_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL DEFAULT '0',
  `ordershipping_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'Element name of shipping plugin',
  `ordershipping_price` decimal(15,5) DEFAULT '0.00000',
  `ordershipping_name` varchar(255) NOT NULL DEFAULT '',
  `ordershipping_code` varchar(255) NOT NULL DEFAULT '',
  `ordershipping_tax` decimal(15,5) DEFAULT '0.00000',
  `ordershipping_extra` decimal(15,5) DEFAULT '0.00000',
  `ordershipping_tracking_id` mediumtext NOT NULL,
  PRIMARY KEY (`j2store_ordershipping_id`),
  KEY `idx_order_shipping_order_id` (`order_id`)  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_orderstatuses` (
  `j2store_orderstatus_id` int(11) NOT NULL AUTO_INCREMENT,
  `orderstatus_name` varchar(32) NOT NULL,
  `orderstatus_cssclass` text NOT NULL,
  `orderstatus_core` int(1) NOT NULL DEFAULT '0',
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_orderstatus_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

INSERT IGNORE INTO `#__j2store_orderstatuses` (`j2store_orderstatus_id`, `orderstatus_name`, `orderstatus_cssclass`, `orderstatus_core`, `enabled`, `ordering`) VALUES
(1, 'J2STORE_CONFIRMED', 'label-success', 1, 1, 1),
(2, 'J2STORE_PROCESSED', 'label-info', 1, 1, 2),
(3, 'J2STORE_FAILED', 'label-important', 1, 1, 3),
(4, 'J2STORE_PENDING', 'label-warning', 1, 1, 4),
(5, 'J2STORE_NEW', 'label-warning', 1, 1, 5),
(6, 'J2STORE_CANCELLED', 'label-warning', 1, 1, 6);

CREATE TABLE IF NOT EXISTS `#__j2store_ordertaxes` (
  `j2store_ordertax_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `ordertax_title` varchar(255) NOT NULL,
  `ordertax_percent` decimal(12,4) NOT NULL,
  `ordertax_amount` decimal(12,4) NOT NULL,
  PRIMARY KEY (`j2store_ordertax_id`),
  KEY `ordertax_order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__j2store_productfiles` (
  `j2store_productfile_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_file_display_name` varchar(255) NOT NULL,
  `product_file_save_name` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `download_total` int(11) NOT NULL,
  PRIMARY KEY (`j2store_productfile_id`),
  KEY `productfile_product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_productfilters`
--

CREATE TABLE IF NOT EXISTS `#__j2store_product_filters` (
  `product_id` int(11) NOT NULL,
  `filter_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `#__j2store_productimages`
--

CREATE TABLE IF NOT EXISTS `#__j2store_productimages` (
`j2store_productimage_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `main_image` text,
  `main_image_alt` varchar(255) NOT NULL,
  `thumb_image` text,
  `thumb_image_alt` varchar(255) NOT NULL,
  `additional_images` longtext,
  `additional_images_alt` longtext,
   PRIMARY KEY (`j2store_productimage_id`),
   KEY `productimage_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_productquantities`
--

CREATE TABLE IF NOT EXISTS `#__j2store_productquantities` (
`j2store_productquantity_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_attributes` text NOT NULL COMMENT 'A CSV of productattributeoption_id values, always in numerical order',
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `on_hold` int(11) NOT NULL,
  `sold` int(11) NOT NULL,
   PRIMARY KEY (`j2store_productquantity_id`),
   UNIQUE KEY `variantidx` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_products`
--
CREATE TABLE IF NOT EXISTS `#__j2store_products` (
  `j2store_product_id` int(11) NOT NULL AUTO_INCREMENT,
  `visibility` int(11) NOT NULL,
  `product_source` varchar(255) DEFAULT NULL,
  `product_source_id` int(11) DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `taxprofile_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `has_options` int(11) DEFAULT NULL,
  `addtocart_text` varchar(255) NOT NULL,
  `enabled` int(11) DEFAULT NULL,
  `plugins` text,
  `params` text,
  `created_on` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_on` varchar(45) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `up_sells` varchar(255) NOT NULL,
  `cross_sells` varchar(255) NOT NULL,
  `productfilter_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`j2store_product_id`),
  UNIQUE KEY `catalogsource` (`product_source`,`product_source_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_product_options`
--
CREATE TABLE IF NOT EXISTS `#__j2store_product_options` (
  `j2store_productoption_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ordering` tinyint(7) NOT NULL,
  `required` int(11) NOT NULL,
  `is_variant` int(11) NOT NULL,
  PRIMARY KEY (`j2store_productoption_id`),
  KEY `productoption_product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_product_optionvalues`
--

CREATE TABLE IF NOT EXISTS `#__j2store_product_optionvalues` (
  `j2store_product_optionvalue_id` int(11) NOT NULL AUTO_INCREMENT,
  `productoption_id` int(11) NOT NULL,
  `optionvalue_id` int(11) NOT NULL,
  `parent_optionvalue` text NOT NULL,
  `product_optionvalue_price` decimal(15,8) NOT NULL,
  `product_optionvalue_prefix` varchar(255) NOT NULL,
  `product_optionvalue_weight` decimal(15,8) NOT NULL,
  `product_optionvalue_weight_prefix` varchar(255) NOT NULL,
  `product_optionvalue_sku` varchar(255) NOT NULL,
  `product_optionvalue_default` int(11) NOT NULL,
  `product_optionvalue_attribs` text NOT NULL,
  PRIMARY KEY (`j2store_product_optionvalue_id`)  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_product_prices`
--

CREATE TABLE IF NOT EXISTS `#__j2store_product_prices` (
  `j2store_productprice_id` int(11) NOT NULL AUTO_INCREMENT,
  `variant_id` int(11) DEFAULT NULL,
  `quantity_from` decimal(15,5) DEFAULT NULL,
  `quantity_to` decimal(15,5) DEFAULT NULL,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `customer_group_id` int(11) DEFAULT NULL,
  `price` decimal(15,5) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`j2store_productprice_id`),
  KEY `price_variant_id` (`variant_id`)  
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_productprice_index` (
  `product_id` int(11) NOT NULL,
  `min_price` decimal(12,4) NOT NULL,
  `max_price` decimal(12,4) NOT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_id` (`product_id`),
  KEY `min_price` (`min_price`),
  KEY `max_price` (`max_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_product_variant_optionvalues`
--
CREATE TABLE IF NOT EXISTS `#__j2store_product_variant_optionvalues` (
  `variant_id` int(11) NOT NULL,
  `product_optionvalue_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`variant_id`),
  UNIQUE KEY `variant_id` (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `#__j2store_shippingmethods` (
  `j2store_shippingmethod_id` int(11) NOT NULL AUTO_INCREMENT,
  `shipping_method_name` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `shipping_method_type` tinyint(1) NOT NULL,
  `tax_class_id` int(11) NOT NULL,
  `address_override` varchar(255) NOT NULL,
  `subtotal_minimum` decimal(15,3) NOT NULL,
  `subtotal_maximum` decimal(15,3) NOT NULL,
  PRIMARY KEY (`j2store_shippingmethod_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__j2store_shippingrates` (
  `j2store_shippingrate_id` int(11) NOT NULL AUTO_INCREMENT,
  `shipping_method_id` int(11) NOT NULL,
  `geozone_id` int(11) NOT NULL,
  `shipping_rate_price` decimal(12,5) NOT NULL,
  `shipping_rate_weight_start` decimal(11,3) NOT NULL,
  `shipping_rate_weight_end` decimal(11,3) NOT NULL,
  `shipping_rate_handling` decimal(12,5) NOT NULL,
  `created_date` datetime NOT NULL COMMENT 'GMT Only',
  `modified_date` datetime NOT NULL COMMENT 'GMT Only',
  PRIMARY KEY (`j2store_shippingrate_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_taxprofiles`
--

CREATE TABLE IF NOT EXISTS `#__j2store_taxprofiles` (
`j2store_taxprofile_id` int(11) NOT NULL AUTO_INCREMENT,
  `taxprofile_name` varchar(255) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_taxprofile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_taxrates`
--

CREATE TABLE IF NOT EXISTS `#__j2store_taxrates` (
`j2store_taxrate_id` int(11) NOT NULL AUTO_INCREMENT,
  `geozone_id` int(11) NOT NULL,
  `taxrate_name` varchar(255) NOT NULL,
  `tax_percent` decimal(11,3) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_taxrate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_taxrules`
--

CREATE TABLE IF NOT EXISTS `#__j2store_taxrules` (
`j2store_taxrule_id` int(11) NOT NULL AUTO_INCREMENT,
  `taxprofile_id` int(11) NOT NULL,
  `taxrate_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,  
   PRIMARY KEY (`j2store_taxrule_id`),
   UNIQUE KEY (`taxprofile_id`, `taxrate_id`, `address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_uploads` (
  `j2store_upload_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_name` varchar(255) NOT NULL,
  `mangled_name` varchar(255) NOT NULL,
  `saved_name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`j2store_upload_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_variants`
--
CREATE TABLE IF NOT EXISTS `#__j2store_variants` (
  `j2store_variant_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `is_master` int(11) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `upc` varchar(255) DEFAULT NULL,
  `price` decimal(15,5) DEFAULT NULL COMMENT 'Regular price of the product',
  `pricing_calculator` varchar(255) NOT NULL,
  `shipping` int(11) NOT NULL,
  `length` decimal(15,5) DEFAULT NULL,
  `width` decimal(15,5) DEFAULT NULL,
  `height` decimal(15,5) DEFAULT NULL,
  `length_class_id` int(11) DEFAULT NULL,
  `weight` decimal(15,5) DEFAULT NULL,
  `weight_class_id` int(11) DEFAULT NULL,  
  `created_on` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_on` varchar(45) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `manage_stock` int(11) DEFAULT NULL,
  `quantity_restriction` int(11) NOT NULL,
  `min_out_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_min_out_qty` int(11) DEFAULT NULL,
  `min_sale_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_min_sale_qty` int(11) DEFAULT NULL,
  `max_sale_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_max_sale_qty` int(11) DEFAULT NULL,
  `notify_qty` decimal(15,5) DEFAULT NULL,
  `use_store_config_notify_qty` int(11) DEFAULT NULL,
  `availability` int(11) DEFAULT NULL,
  `allow_backorder` int(11) NOT NULL,  
  `isdefault_variant` int(11) NOT NULL,
  PRIMARY KEY (`j2store_variant_id`),
  KEY `variant_product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_vendors`
--

CREATE TABLE IF NOT EXISTS `#__j2store_vendors` (
`j2store_vendor_id` int(11) NOT NULL AUTO_INCREMENT,
  `j2store_user_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_vendor_id`),
   UNIQUE KEY `j2store_user_id` (`j2store_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------


--
-- Table structure for table `#__j2store_vouchers`
--

CREATE TABLE IF NOT EXISTS `#__j2store_vouchers` (
  `j2store_voucher_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `email_to` varchar(255) NOT NULL,
  `voucher_code` varchar(255) NOT NULL,
  `voucher_type` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `email_body` longtext NOT NULL,
  `voucher_value` decimal(15,8) NOT NULL,
  `ordering` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`j2store_voucher_id`),
  UNIQUE KEY `voucher_code` (`voucher_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_weights`
--

CREATE TABLE IF NOT EXISTS `#__j2store_weights` (
  `j2store_weight_id` int(11) NOT NULL AUTO_INCREMENT,
  `weight_title` varchar(255) NOT NULL,
  `weight_unit` varchar(4) NOT NULL,
  `weight_value` decimal(15,8) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`j2store_weight_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__j2store_zones`
--

CREATE TABLE IF NOT EXISTS `#__j2store_zones` (
`j2store_zone_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `zone_code` varchar(255) NOT NULL,
  `zone_name` varchar(255) NOT NULL,
  `enabled` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
   PRIMARY KEY (`j2store_zone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_orderdiscounts` (
	`j2store_orderdiscount_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` varchar(255) NOT NULL,
	`discount_type` varchar(255) NOT NULL,
	`discount_entity_id` int(11) unsigned NOT NULL,
	`discount_title` varchar(255) NOT NULL,
	`discount_code` varchar(255) NOT NULL,
	`discount_value` varchar(255) NOT NULL,
	`discount_value_type` varchar(255) NOT NULL,
	`discount_customer_email` varchar(255) NOT NULL,
	`user_id` int(11) unsigned NOT NULL,
	`discount_amount` decimal(12,4) NOT NULL,
	`discount_tax` decimal(12,4) NOT NULL,
	`discount_params` text NOT NULL,
	PRIMARY KEY (`j2store_orderdiscount_id`),
	KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__j2store_metafields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `metakey` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `metavalue` text NOT NULL,
  `valuetype` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `owner_resource` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `metafields_owner_id_index` (`owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__j2store_queues` (
 `j2store_queue_id` int(11) NOT NULL AUTO_INCREMENT,
 `relation_id` varchar(255) NOT NULL,
 `queue_type` varchar(255) NOT NULL,
 `queue_data` longtext NOT NULL,
 `params` longtext NOT NULL,
 `priority` int(11) NOT NULL,
 `status` varchar(255) NOT NULL,
 `expired` datetime NOT NULL,
 `created_on` varchar(255) DEFAULT NULL,
 `modified_on` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`j2store_queue_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

