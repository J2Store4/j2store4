<?php
/**
 * @package J2Store
* @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
* @license GNU GPL v3 or later
*/
// No direct access to this file
defined ( '_JEXEC' ) or die ();


class J2StoreTableOrder extends F0FTable
{

	/* OrderItems table object */
	private $_items = array();

	private $_shipping_methods = '';

	/** @var array holding country id and zone id for billing */
	protected $_billing_address = null;

	/** @var array holding country id and zone id for billing */
	protected $_shipping_address = null;

	/** @var array      tax & shipping geozone objects */
	protected $_billing_geozones = array();
	protected $_shipping_geozones = array();

	/** @var array      The shipping totals JObjects */
	protected $_shipping_totals = null;

	/** @var boolean Has the recurring item been added to the order?
	 * This is used exclusively during orderTotal calculation
	 */
	protected $_recurringItemExists = false;

	/** @var object And OrderItem Object, only populated if the orderitem recurs
	 */
	protected $_recurringItem = false;

	/** @var array An array of J2StoreTableTaxRates objects (the unique taxrates for this order) */
	public $_taxrates = array();

	/** @var array (Base tax rates for this order) This is not the same as the item tax rates. This is based on the shop address */
	public $_shop_taxrates = array();

	/** @var array An array of tax amounts, indexed by tax_rate_id */
	protected $_taxrate_amounts = array();

	/** @var array An array of J2StoreTableTaxRates objects (the unique taxclasses for this order) */
	protected $_taxclasses = array();

	/** @var array An array of tax amounts, indexed by tax_class_id */
	protected $_taxclass_amounts = array();

	protected $_ordertaxes = array();

	/** @var array An array of J2StoreTableCoupons objects */
	protected $_coupons = array();

	/** @var array An array of J2StoreTableOrderCoupons objects */
	protected $_ordercoupons = array();

	/** @var array An array of J2StoreTableOrderVouchers objects */
	protected $_ordervouchers = array();

	/** @var array An array of J2StoreTableOrderInfo object */
	protected $_orderinfo = null;

	/** @var array An array of J2StoreTableOrderVouchers objects */
	protected $_orderdownloads = array();

	protected $_orderhistory = array();

	protected $_orderdiscounts = array();

	public $fees = null;

	/** @var float Cart subtotal. */
	public $subtotal;

	/** @var float Cart subtotal without tax. */
	public $subtotal_ex_tax;

	/** @var float Total cart tax. */
	public $tax_total;

	/** @var array An array of taxes/tax rates for the cart. */
	public $taxes;

	/** @var array An array of taxes/tax rates for the shipping. */
	public $shipping_taxes;

	/** @var float Discount amount before tax */
	public $discount_cart;

	/** @var float Discounted tax amount. Used predominantly for displaying tax inclusive prices correctly */
	public $discount_cart_tax;

	public $free_shipping = false;

	public $cart_contents_total;
	public $cart_contents = array();

	public $line_items = array();

	/**
	 * Method to set items from cart object to Order object     *
	 * @param object $items Cart items
	 */

	public function setItems ( $items )
	{
		foreach ( $items as $item ) {
			$this->addItem ( $item );
		}
	}

	/**
	 * Method to get order items.
	 * Fetches items either from the current object or from the orderitems table
	 * @return object List of OrderItem table object
	 */

	public function getItems ()
	{

		if ( empty( $this->_items ) && !empty( $this->order_id ) ) {
			//retrieve the order's items
			$model = F0FModel::getTmpInstance ( 'OrderItems', 'J2StoreModel' )
				->order_id ( $this->order_id );
			$model->setState ( 'order', 'tbl.orderitem_name' );
			$model->setState ( 'direction', 'ASC' );
			$orderitems = $model->getList ();
			foreach ( $orderitems as $orderitem ) {
				unset( $table );
				$table = F0FTable::getInstance ( 'OrderItem', 'J2StoreTable' )->getClone ();
				$table->load ( $orderitem->j2store_orderitem_id );
				$table->orderitem_quantity = J2Store::utilities ()->stock_qty ( $table->orderitem_quantity );
				$table->orderitemattributes = $orderitem->orderitemattributes;
				$this->addItem ( $table );
			}
		}

		$items = $this->_items;
		if ( !is_array ( $items ) ) {
			$items = array();
			$this->_items = $items;
		}

		return $this->_items;
	}

	/**
	 * Reset order Id and items
	 * */
	public function resetOrderID ( $id, $order_type = '' )
	{
		if ( $id != '' ) {
			$this->_items = array();
			$this->order_id = $id;
			if($order_type != ''){
				$this->order_type = $order_type;
			}
		}
	}

	/**
	 * Method to get number of items in the order
	 * @return number
	 */
	public function getItemCount ()
	{
		if ( isset( $this->_items ) ) {
			return count ( $this->_items );
		} else {
			return 0;
		}
	}

	/**
	 * Method to add the item to OrderItem table
	 * @param object $item
	 */

	public function addItem ( $item )
	{
		$orderItem = F0FTable::getAnInstance ( 'OrderItem', 'J2StoreTable' )->getClone ();
		if ( is_array ( $item ) ) {
			$orderItem->bind ( $item );
		} elseif ( is_object ( $item ) && is_a ( $item, 'J2StoreTableOrderItem' ) ) {
			$orderItem = $item;
		} elseif ( is_object ( $item ) ) {
			$orderItem->product_id = $item->product_id;
			$orderItem->variant_id = $item->variant_id;
			$orderItem->orderitem_quantity = $item->orderitem_quantity;
			$orderItem->vendor_id = $item->vendor_id;
			$orderItem->orderitemattributes = $item->orderitemattributes;
			$orderItem->orderitem_attributes = $item->orderitem_attributes;
		} else {
			$orderItem->product_id = $item;
			$orderItem->orderitem_quantity = '1';
			$orderItem->vendor_id = '0';
			$orderItem->orderitem_attributes = '';
		}

		// Use hash to separate items when customer is buying the same product from multiple vendors and with different attribs
		$hash = intval ( $orderItem->cartitem_id ) . "." . intval ( $orderItem->product_id ) . "." . intval ( $orderItem->variant_id ) . "." . intval ( $orderItem->vendor_id ) . "." . $orderItem->orderitem_attributes;
		$this->_items [ $hash ] = $orderItem;
	}

	/**
	 * Method to initialise the order object with totals     *
	 * @param string $taxes
	 */

	public function getTotals ( $taxes = true )
	{


		$this->order_discount = 0;

		//set the order information
		$this->setOrderInformation ();

		$this->getOrderProductTotals ();

		// then calculate shipping total
		$this->getOrderShippingTotals ();

		// discount
		$this->getOrderDiscountTotals ();

		// Trigger the fees API where developers can add fees or additional cost to order
		$this->getOrderFeeTotals ();

		// then calculate the tax
		$this->getOrderTaxTotals ();

		// this goes last, to be sure it gets the fully adjusted figures
		//	$this->calculateVendorTotals();
		// sum totals
		$subtotal =
			$this->cart_contents_total
			+ $this->order_shipping
			+ $this->order_shipping_tax
			+ $this->order_tax;

		$total = $subtotal + $this->order_fees;
		//if surcharge is set add that as well
		if ( isset( $this->order_surcharge ) ) {
			$total = $total + $this->order_surcharge;
		}
		// set object properties
		$this->order_total = $total;

		// We fire just a single plugin event here and pass the entire order object
		// so the plugins can override whatever they need to
        $order_obj = $this->get_order_obj();
		J2Store::plugin ()->event ( "CalculateOrderTotals", array( &$order_obj ) );

	}

	/**
	 * Calculates the product total (aka subtotal)
	 * using the array of items in the order object
	 *
	 * @return unknown_type
	 */
	function getOrderProductTotals ()
	{
		$params = J2Store::config ();
		$subtotal = 0.00;
		$subtotal_ex_tax = 0.00;

		/**
		 * Calculate subtotals for items.
		 * This is done first so that discount logic can use the values.
		 */

		foreach ( $this->getItems () as $item ) {
			J2Store::plugin ()->event ( 'BeforeCalculateItemSubtotal', array( &$item, $this ) );
			// Prices
			$base_price = $item->orderitem_price + $item->orderitem_option_price;
			J2Store::plugin ()->event ( 'AfterCalculateBasePriceInProductTotal', array( &$item, $this, &$base_price) );
			$line_price = $base_price * $item->orderitem_quantity;

			$line_subtotal = 0;
			$line_subtotal_tax = 0;

			/**
			 * No tax to calculate
			 */
			if ( !isset ( $item->orderitem_taxprofile_id ) || $item->orderitem_taxprofile_id < 1 ) {

				// Subtotal is the undiscounted price
				$this->subtotal += $line_price;
				$this->subtotal_ex_tax += $line_price;

				/**
				 * Prices include tax
				 *
				 * To prevent rounding issues we need to work with the inclusive price where possible
				 * otherwise we'll see errors such as when working with a 9.99 inc price, 20% VAT which would
				 * be 8.325 leading to totals being 1p off
				 *
				 * Pre tax coupons come off the price the customer thinks they are paying - tax is calculated
				 * afterwards.
				 *
				 * e.g. $100 bike with $10 coupon = customer pays $90 and tax worked backwards from that
				 */
			} elseif ( $item->orderitem_taxprofile_id && $params->get ( 'config_including_tax', 0 ) ) {

				// Get base tax rates

				$shop_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getBaseTaxRates ( $line_price, $item->orderitem_taxprofile_id, 1 );

				// Get item tax rates
				$item_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 1 );

				/**
				 * ADJUST TAX - Calculations when base tax is not equal to the item tax
				 */
				if ( $item_taxrates->taxtotal !== $shop_taxrates->taxtotal ) {

					// Work out a new base price without the shop's base tax

					// Now we have a new item price (excluding TAX)
					$line_subtotal = $line_price - $shop_taxrates->taxtotal;

					// Now add modified taxes
					$modified_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $line_subtotal, $item->orderitem_taxprofile_id, 0 );
					$line_subtotal_tax = $modified_taxrates->taxtotal;

					/**
					 * Regular tax calculation (customer inside base and the tax class is unmodified
					 */
				} else {

					// Calc tax normally
					$line_subtotal_tax = $item_taxrates->taxtotal;
					$line_subtotal = $line_price - $item_taxrates->taxtotal;
				}

			} else {

				/**
				 * Prices exclude tax
				 *
				 * This calculation is simpler - work with the base, untaxed price.
				 */

				$item_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 0 );
				// Base tax for line before discount - we will store this in the order data
				$line_subtotal_tax = $item_taxrates->taxtotal;
				$line_subtotal = $line_price;
			}

			// Add to main subtotal
			$this->subtotal += $line_subtotal + $line_subtotal_tax;
			$this->subtotal_ex_tax += $line_subtotal;
		}

		/**
		 * Calculate actual totals for items
		 */
		foreach ( $this->getItems () as $hash => $item ) {
			J2Store::plugin ()->event ( 'BeforeCalculateActualItemTotal', array( &$item, $this ) );
			// Prices
			$base_price = $item->orderitem_price + $item->orderitem_option_price;
			J2Store::plugin ()->event ( 'AfterCalculateBasePriceInProductTotal', array( &$item, $this, &$base_price) );
			$line_price = $base_price * $item->orderitem_quantity;

			// Tax data
			$taxes = array();
			$discounted_taxes = array();

			/**
			 * No tax to calculate
			 */
			if ( !isset ( $item->orderitem_taxprofile_id ) || $item->orderitem_taxprofile_id < 1 ) {

				// Discounted Price (price with any pre-tax discounts applied)
				$discounted_price = $this->get_discounted_price ( $item, $base_price, true );
				$discounted_tax_amount = 0;
				$tax_amount = 0;
				$line_subtotal_tax = 0;
				$line_subtotal = $line_price;
				$line_tax = 0;
				$line_total = $discounted_price * $item->orderitem_quantity;
				/**
				 * Prices include tax
				 */
			} elseif ( $item->orderitem_taxprofile_id && $params->get ( 'config_including_tax', 0 ) ) {

				// Get base tax rates

				$shop_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getBaseTaxRates ( $line_price, $item->orderitem_taxprofile_id, 1 );

				// Get item tax rates
				$item_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 1 );

				/**
				 * ADJUST TAX - Calculations when base tax is not equal to the item tax
				 */
				if ( $item_taxrates->taxtotal !== $shop_taxrates->taxtotal ) {

					// Work out a new base price without the shop's base tax

					// Now we have a new item price (excluding TAX)
					$line_subtotal = $line_price - $shop_taxrates->taxtotal;

					// Now add modified taxes
					$modified_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $line_subtotal, $item->orderitem_taxprofile_id, 0 );
					$line_subtotal_tax = $modified_taxrates->taxtotal;

					// Adjusted price (this is the price including the new tax rate)
					$adjusted_price = ( $line_subtotal + $line_subtotal_tax ) / $item->orderitem_quantity;

					// Apply discounts
					$discounted_price = $this->get_discounted_price ( $item, $adjusted_price, true );
					$discounted_taxes = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $discounted_price * $item->orderitem_quantity, $item->orderitem_taxprofile_id, 1 );
					$line_tax = $discounted_taxes->taxtotal;
					$line_total = ( $discounted_price * $item->orderitem_quantity ) - $line_tax;

					/**
					 * Regular tax calculation (customer inside base and the tax class is unmodified
					 */
				} else {

					// Work out a new base price without the item tax
					// Now we have a new item price (excluding TAX)
					$line_subtotal = $line_price - $item_taxrates->taxtotal;
					$line_subtotal_tax = $item_taxrates->taxtotal;

					// Calc prices and tax (discounted)
					$discounted_price = $this->get_discounted_price ( $item, $base_price, true );
					$discounted_taxes = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $discounted_price * $item->orderitem_quantity, $item->orderitem_taxprofile_id, 1 );
					$line_tax = $discounted_taxes->taxtotal;
					$line_total = ( $discounted_price * $item->orderitem_quantity ) - $line_tax;
				}

				foreach ( $discounted_taxes->taxes as $taxrate_id => $tax_rate ) {
					if ( !isset ( $this->_taxrates [ $taxrate_id ] ) ) {
						$this->_taxrates [ $taxrate_id ] [ 'name' ] = $tax_rate [ 'name' ];
						$this->_taxrates [ $taxrate_id ] [ 'rate' ] = $tax_rate [ 'rate' ];
						$this->_taxrates [ $taxrate_id ] [ 'total' ] = ( $tax_rate [ 'amount' ] );
					} else {
						$this->_taxrates [ $taxrate_id ] [ 'name' ] = $tax_rate [ 'name' ];
						$this->_taxrates [ $taxrate_id ] [ 'rate' ] = $tax_rate [ 'rate' ];
						$this->_taxrates [ $taxrate_id ] [ 'total' ] += ( $tax_rate [ 'amount' ] );
					}
				}
				$item->orderitem_per_item_tax = $discounted_taxes->taxtotal / $item->orderitem_quantity;

				/**
				 * Prices exclude tax
				 */
			} else {


				// Work out a new base price without the shop's base tax
				$item_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 0 );

				// Now we have the item price (excluding TAX)
				$line_subtotal = $line_price;
				$line_subtotal_tax = $item_taxrates->taxtotal;

				// Now calc product rates
				$discounted_price = $this->get_discounted_price ( $item, $base_price, true );
				$discounted_taxes = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates ( $discounted_price * $item->orderitem_quantity, $item->orderitem_taxprofile_id, 0 );
				$discounted_tax_amount = $discounted_taxes->taxtotal;
				$line_tax = $discounted_tax_amount;
				$line_total = $discounted_price * $item->orderitem_quantity;

				// Tax rows - merge the totals we just got
				foreach ( $discounted_taxes->taxes as $taxrate_id => $tax_rate ) {
					if ( !isset ( $this->_taxrates [ $taxrate_id ] ) ) {
						$this->_taxrates [ $taxrate_id ] [ 'name' ] = $tax_rate [ 'name' ];
						$this->_taxrates [ $taxrate_id ] [ 'rate' ] = $tax_rate [ 'rate' ];
						$this->_taxrates [ $taxrate_id ] [ 'total' ] = ( $tax_rate [ 'amount' ] );
					} else {
						$this->_taxrates [ $taxrate_id ] [ 'name' ] = $tax_rate [ 'name' ];
						$this->_taxrates [ $taxrate_id ] [ 'rate' ] = $tax_rate [ 'rate' ];
						$this->_taxrates [ $taxrate_id ] [ 'total' ] += ( $tax_rate [ 'amount' ] );
					}
				}
				$item->orderitem_per_item_tax = $discounted_taxes->taxtotal / $item->orderitem_quantity;
			}

			// Cart contents total is based on discounted prices and is used for the final total calculation
			$this->cart_contents_total += $line_total;
			/* 	var_dump ( $line_total );
				var_dump ( $line_tax );
				var_dump ( $line_subtotal );
				var_dump ( $line_subtotal_tax ); */
			// Store costs + taxes for lines
			$this->cart_contents [ $hash ] [ 'line_total' ] = $line_total;
			$this->cart_contents [ $hash ] [ 'line_tax' ] = $line_tax;
			$this->cart_contents [ $hash ] [ 'line_subtotal' ] = $line_subtotal;
			$this->cart_contents [ $hash ] [ 'line_subtotal_tax' ] = $line_subtotal_tax;

			// Store rates ID and costs - Since 2.2
			$this->cart_contents [ $hash ] [ 'line_tax_data' ] = array(
				'total' => $discounted_taxes,
				'subtotal' => $taxes
			);

			$item->orderitem_finalprice = $line_subtotal + $line_subtotal_tax;
			$item->orderitem_finalprice_with_tax = $line_subtotal + $line_subtotal_tax;
			$item->orderitem_finalprice_without_tax = $line_subtotal;
			$item->orderitem_tax = $line_tax;
		}
		// vat exempted customer ? remove the taxes
		$customer = F0FTable::getAnInstance ( 'Customer', 'J2StoreTable' );
		if ( $customer->is_vat_exempt () ) {
			$this->removeOrderTaxes ();
		}

		// set object properties
		$this->order_subtotal = $this->subtotal;
		$this->order_subtotal_ex_tax = $this->subtotal_ex_tax;

		//allow plugins to modify the output.
		J2Store::plugin ()->event ( "CalculateProductTotals", array(
			$this
		) );
	}


	function getSubtotal ()
	{
		return $this->order_subtotal;
	}

	function getOrderTaxRates ()
	{
		if ( count ( $this->_ordertaxes ) < 1 && !empty( $this->order_id ) ) {
			$this->_ordertaxes = F0FModel::getTmpInstance ( 'OrderTaxes', 'J2StoreModel' )->order_id ( $this->order_id )->getList ();
		}
		return $this->_ordertaxes;
	}

	public function removeOrderTaxes ()
	{
		$items = $this->getItems ();

		foreach ( $items as $item ) {
			$item->orderitem_finalprice_with_tax = $item->orderitem_finalprice_with_tax - $item->orderitem_tax;
			$item->orderitem_per_item_tax = 0;
			$item->orderitem_tax = 0;
		}
		//reset tax rates array
		$this->_taxrates = array();
		$this->order_tax = 0;

	}

	function getOrderTaxTotals ()
	{

		if ( isset( $this->_taxrates ) && count ( $this->_ordertaxes ) < 1 ) {
			foreach ( $this->_taxrates as $tax ) {
				$ordertax = F0FTable::getAnInstance ( 'Ordertax', 'J2StoreTable' )->getClone ();
				$ordertax->ordertax_title = $tax[ 'name' ];
				$ordertax->ordertax_percent = $tax[ 'rate' ];
				$ordertax->ordertax_amount = $tax[ 'total' ];
				$this->_ordertaxes[] = $ordertax;
			}
		}

		$taxtotal = 0;
		if ( isset( $this->_ordertaxes ) && count ( $this->_ordertaxes ) ) {
			foreach ( $this->_ordertaxes as $ordertax ) {
				$taxtotal += $ordertax->ordertax_amount;
			}
		}
		$this->order_tax = $taxtotal;

		J2Store::plugin ()->event ( "CalculateTaxTotals", array( $this ) );

	}


	/**
	 * -------------------------------------------------------
	 * Discounts
	 * ------------------------------------------------------
	 */

	/**
	 * Method to set all the discounts applied for the order
	 *
	 */

	public function getOrderDiscountTotals ()
	{
		$discount_total = 0;

		$app = JFactory::getApplication ();
		$session = JFactory::getSession ();
		$coupon_model = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' );
		if ( $coupon_model->has_coupon () ) {
			$code = $coupon_model->get_coupon ();

			//always store the discount excluding tax.
			$coupon_discount = $this->get_coupon_discount_amount ( $code );
			$coupon_discount_tax = $this->get_coupon_discount_tax_amount ( $code );
			if ( $coupon_discount >= 0) {
				//$coupon_model = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' );
				$coupon = $coupon_model->getCouponByCode ( $code );
				// Since 3.2
				$discount = new stdClass ();
				$discount->discount_type = 'coupon';
				$discount->discount_entity_id = $coupon->j2store_coupon_id;
				$discount->discount_title = $coupon->coupon_code;
				$discount->discount_code = $coupon->coupon_code;
				$discount->discount_value = $coupon->value;
				$discount->discount_value_type = $coupon->value_type;
				$discount->discount_amount = $coupon_discount;
				$discount->discount_tax = $coupon_discount_tax;
				$this->addOrderDiscounts ( $discount );

				// legacy compatibility
				// backward compatibility
				$couponTable = new stdClass ();
				$couponTable->coupon_id = $coupon->j2store_coupon_id;
				$couponTable->coupon_code = $coupon->coupon_code;
				$couponTable->value = $coupon->value;
				$couponTable->value_type = $coupon->value_type;
				$couponTable->amount = $coupon_discount;
				$this->_ordercoupons [ $coupon->coupon_code ] = $couponTable;
			}
			J2Store::plugin ()->event ( "CalculateCouponTotals", array(
				$this
			) );
		}
		$voucher_model = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
		if ( $voucher_model->has_voucher () ) {
			$code = $voucher_model->get_voucher ();
			$voucher_discount = $this->get_coupon_discount_amount ( $code );
			$voucher_discount_tax = $this->get_coupon_discount_tax_amount ( $code );
			if ( $voucher_discount ) {
				//$voucher_model = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
				$voucher = $voucher_model->getVoucherByCode ( $code );

				$voucherHistoryTable = new stdClass ();
				$voucherHistoryTable->voucher_id = $voucher->j2store_voucher_id;
				$voucherHistoryTable->voucher_code = $voucher->voucher_code;
				$voucherHistoryTable->voucher_to_email = $voucher->email_to;
				$voucherHistoryTable->amount = -$voucher_discount;

				// Since 3.2
				$discount = new stdClass ();
				$discount->discount_type = 'voucher';
				$discount->discount_entity_id = $voucher->j2store_voucher_id;
				$discount->discount_title = $voucher->voucher_code;
				$discount->discount_code = $voucher->voucher_code;
				$discount->discount_amount = $voucher_discount;
				$discount->discount_tax = $voucher_discount_tax;
				$this->addOrderDiscounts ( $discount );

				$this->_ordervouchers [ $voucher->voucher_code ] = $voucherHistoryTable;
			}
			J2Store::plugin ()->event ( "CalculateVoucherTotals", array(
				$this
			) );
		}

		// allow plugins to add or modify discounts
		J2Store::plugin ()->event ( "CalculateDiscountTotals", array(
			$this
		) );

		foreach ( $this->getOrderDiscounts () as $discount ) {
			$discount_total += $discount->discount_amount;
		}
		$cart_subtotal = 0;
		$cart_total = 0;
		$fee_total = 0;
		$cart_subtotal_tax = 0;
		$cart_total_tax = 0;

		foreach ( $this->cart_contents as $item ) {
			$cart_subtotal += isset( $item[ 'line_subtotal' ] ) ? $item[ 'line_subtotal' ] : 0;
			$cart_total += isset( $item[ 'line_total' ] ) ? $item[ 'line_total' ] : 0;
			$cart_subtotal_tax += isset( $item[ 'line_subtotal_tax' ] ) ? $item[ 'line_subtotal_tax' ] : 0;
			$cart_total_tax += isset( $item[ 'line_tax' ] ) ? $item[ 'line_tax' ] : 0;
		}
		$this->order_discount = $cart_subtotal - $cart_total;
		$this->order_discount_tax = $cart_subtotal_tax - $cart_total_tax;
	}

	public function getCartContents(){
		return $this->cart_contents;
	}

	public function getCartDiscounts(){
		$cart_subtotal = 0;
		$cart_total = 0;
		$cart_subtotal_tax = 0;
		$cart_total_tax = 0;
		foreach ( $this->cart_contents as $item ) {
			$cart_subtotal += isset( $item[ 'line_subtotal' ] ) ? $item[ 'line_subtotal' ] : 0;
			$cart_total += isset( $item[ 'line_total' ] ) ? $item[ 'line_total' ] : 0;
			$cart_subtotal_tax += isset( $item[ 'line_subtotal_tax' ] ) ? $item[ 'line_subtotal_tax' ] : 0;
			$cart_total_tax += isset( $item[ 'line_tax' ] ) ? $item[ 'line_tax' ] : 0;
		}
		$order_discount = $cart_subtotal - $cart_total;
		$order_discount_tax = $cart_subtotal_tax - $cart_total_tax;
		$discount_array = array(
			'order_discount' => $order_discount,
			'order_discount_tax' => $order_discount_tax
		);
		// allow plugins to add or modify discounts
		J2Store::plugin ()->event ( "GetCartDiscounts", array(
			$discount_array,$this
		) );

		return $discount_array;
	}

	public function addOrderDiscounts ( $discount )
	{
		$this->_orderdiscounts[] = $discount;
	}

	public function getOrderDiscounts ()
	{
		if ( count ( $this->_orderdiscounts ) < 1 && !empty( $this->order_id ) ) {
			$model = F0FModel::getTmpInstance ( 'Orderdiscounts', 'J2StoreModel' );
			$model->order_id ( $this->order_id );
			$this->_orderdiscounts = $model->getList ();
		}
		return $this->_orderdiscounts;
	}


	/**
	 * Function to apply discounts to a product and get the discounted price (before tax is applied).
	 *
	 * @param mixed $values
	 * @param mixed $price
	 * @param bool $add_totals (default: false)
	 * @return float price
	 */
	public function get_discounted_price ( &$item, $price, $add_totals = false )
	{
		if ( !$price ) {
			return $price;
		}

		$app = JFactory::getApplication ();
		$params = J2Store::config ();
		$session = JFactory::getSession ();
		$coupon_model = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' );
		if ( $coupon_model->has_coupon () ) {

			$coupon_model->init ();
			if ( J2Store::platform()->isClient('administrator')  ) {
				$coupon_status = $coupon_model->is_admin_valid ( $this );
			} else {
				$coupon_status = $coupon_model->is_valid ( $this );
			}

			if ( $coupon_status && ( $coupon_model->is_valid_for_product ( $item ) || $coupon_model->is_valid_for_cart () ) ) {
				$discount_amount = $coupon_model->get_discount_amount ( $price, $item, $this, $single = true );

				//sanity check
				$discount_amount = min ( $price, $discount_amount );
				// reduce discount from the item price
				$price = max ( $price - $discount_amount, 0 );

				// Store the totals for DISPLAY in the cart
				if ( $add_totals ) {
					$total_discount = $discount_amount * $item->orderitem_quantity;
					$total_discount_tax = 0;

					if ( $item->orderitem_taxprofile_id ) {
						//Tax is enabled. So calculate tax on the discount
						$taxModel = F0FModel::getTmpInstance ( 'TaxProfiles', 'J2StoreModel' );
						$tax_rates = $taxModel->getTaxwithRates ( $discount_amount, $item->orderitem_taxprofile_id, $params->get ( 'config_including_tax', 0 ) );
						$total_discount_tax = $tax_rates->taxtotal * $item->orderitem_quantity;
						//Discount total is always without tax.
						$total_discount = ( $params->get ( 'config_including_tax', 0 ) ) ? $total_discount - $total_discount_tax : $total_discount;
					}

					$item->orderitem_discount = $total_discount;
					$item->orderitem_discount_tax = $total_discount_tax;

					$this->discount_cart += $total_discount;
					$this->discount_cart_tax += $total_discount_tax;
					$this->increase_coupon_discount_amount ( $coupon_model->get_coupon (), $total_discount, $total_discount_tax );
					// $this->increase_coupon_applied_count( $code, $values['quantity'] );
				}
			}
		}
		$voucher_model = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
		if ( $voucher_model->has_voucher () ) {
			// Because of one moment of stupidity we now have to do a separate calculation for vouchers as well. A brilliant way of implementing this would be via coupons.
			// TODO: Merge vouchers with coupons in future. Both share similar characteristics
			//$voucher_model = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
            $init_status = $voucher_model->init ();
            if ( $app->isClient('administrator') ) {
                $voucher_status = $voucher_model->is_admin_valid ( $this );
            } else {
                $voucher_status = $voucher_model->is_valid ( $this );
            }

            if ( $init_status && $voucher_status ) {
				$discount_amount = $voucher_model->get_discount_amount ( $price, $item, $this, $single = true );
				//sanity check
				$discount_amount = min ( $price, $discount_amount );
				$price = max ( $price - $discount_amount, 0 );

				// Store the totals for DISPLAY in the cart
				if ( $add_totals ) {
					$total_discount = $discount_amount * $item->orderitem_quantity;
					$total_discount_tax = 0;

					if ( $item->orderitem_taxprofile_id ) {
						$taxModel = F0FModel::getTmpInstance ( 'TaxProfiles', 'J2StoreModel' );
						$tax_rates = $taxModel->getTaxwithRates ( $discount_amount, $item->orderitem_taxprofile_id, $params->get ( 'config_including_tax', 0 ) );
						$total_discount_tax = $tax_rates->taxtotal * $item->orderitem_quantity;
						//Discount total is always without tax.
						$total_discount = ( $params->get ( 'config_including_tax', 0 ) ) ? $total_discount - $total_discount_tax : $total_discount;
					}

					$item->orderitem_discount = $total_discount;
					$item->orderitem_discount_tax = $total_discount_tax;

					$this->discount_cart += $total_discount;
					$this->discount_cart_tax += $total_discount_tax;
					$this->increase_coupon_discount_amount ( $voucher_model->get_voucher (), $total_discount, $total_discount_tax );
					// $this->increase_coupon_applied_count( $code, $values['quantity'] );
				}
			}
		}
        $order_obj = $this->get_order_obj();
		// allo plugins to modify
		J2Store::plugin ()->event ( 'GetDiscountedPrice', array(
			&$price,
			&$item,
			$add_totals,
			&$order_obj
		) );
		return $price;
	}

	/**
	 * Store how much discount each coupon grants.
	 *
	 * @access private
	 * @param string $code
	 * @param double $amount
	 * @param double $tax
	 */
	public function increase_coupon_discount_amount ( $code, $amount, $tax )
	{
		$this->coupon_discount_amounts[ $code ] = isset( $this->coupon_discount_amounts[ $code ] ) ? $this->coupon_discount_amounts[ $code ] + $amount : $amount;
		$this->coupon_discount_tax_amounts[ $code ] = isset( $this->coupon_discount_tax_amounts[ $code ] ) ? $this->coupon_discount_tax_amounts[ $code ] + $tax : $tax;
	}

	/**
	 * Get the discount amount for a used coupon
	 * @param  string $code coupon code
	 * @param  bool inc or ex tax
	 * @return float discount amount
	 */
	public function get_coupon_discount_amount ( $code, $ex_tax = true )
	{
		$discount_amount = isset( $this->coupon_discount_amounts[ $code ] ) ? $this->coupon_discount_amounts[ $code ] : 0;

		if ( !$ex_tax ) {
			$discount_amount += $this->get_coupon_discount_tax_amount ( $code );
		}
		return $discount_amount;
	}

	/**
	 * Get the discount tax amount for a used coupon (for tax inclusive prices)
	 * @param  string $code coupon code
	 * @param  bool inc or ex tax
	 * @return float discount amount
	 */
	public function get_coupon_discount_tax_amount ( $code )
	{
		return isset( $this->coupon_discount_tax_amounts[ $code ] ) ? $this->coupon_discount_tax_amounts[ $code ] : 0;
	}

	/**
	 * Gets the coupon discounts for the order.
	 * @deprecated in 3.2
	 * Replaced with a common method to get all the discounts.
	 * @return object list of coupons applied.
	 */

	public function getOrderCoupons ()
	{

		if ( count ( $this->_ordercoupons ) < 1 && !empty( $this->order_id ) ) {
			//$this->_ordercoupons = F0FModel::getTmpInstance('Ordercoupons', 'J2StoreModel')->order_id($this->order_id)->getList();
			$model = F0FModel::getTmpInstance ( 'Orderdiscounts', 'J2StoreModel' );
			$model->order_id ( $this->order_id );
			$model->discount_type ( 'coupon' );
			$coupons = $model->getList ();
			foreach ( $coupons as &$coupon ) {
				$coupon->coupon_code = $coupon->discount_code;
				$coupon->amount = $coupon->discount_amount;
			}
			$this->_ordercoupons = $coupons;
		}
		return $this->_ordercoupons;
	}

	/**
	 * Gets voucher discounts for the order.
	 * @deprecated in 3.2
	 * Replaced with a common method to get all the discounts.
	 * @return object list of coupons applied.
	 */

	public function getOrderVouchers ()
	{
		if ( count ( $this->_ordervouchers ) < 1 && !empty( $this->order_id ) ) {
			//backward compatibility
			$model = F0FModel::getTmpInstance ( 'Orderdiscounts', 'J2StoreModel' );
			$model->order_id ( $this->order_id );
			$model->discount_type ( 'voucher' );
			$vouchers = $model->getList ();
			foreach ( $vouchers as &$voucher ) {
				$voucher->voucher_code = $voucher->discount_code;
				$voucher->amount = $voucher->discount_amount;
			}
			//$this->_ordervouchers = F0FModel::getTmpInstance('Voucherhistories', 'J2StoreModel')->order_id($this->order_id)->getList();
			$this->_ordervouchers = $vouchers;
		}
		return $this->_ordervouchers;
	}

	public function has_free_shipping ()
	{
		return $this->free_shipping;
	}

	public function allow_free_shipping ()
	{
		$this->free_shipping = true;
	}

	public function getOrderShippingTotals ()
	{

		$app = JFactory::getApplication ();
		$order_shipping = 0.00;
		$order_shipping_tax = 0.00;

		$session = JFactory::getSession ();

		$items = $this->getItems ();

		if ( !is_array ( $items ) ) {
			$this->order_shipping = $order_shipping;
			$this->order_shipping_tax = $order_shipping_tax;
			return;
		}

		$showShipping = false;
		if ( $isShippingEnabled = $this->isShippingEnabled () ) {
			$this->is_shippable = 1;
			$showShipping = true;
		}
		//assign a single selected method if it had been selected
		$force = $session->get ( 'force_calculate_shipping', 0, 'j2store' );
		$session->clear ( 'force_calculate_shipping', 'j2store' );
		$shipping_values = $session->get ( 'shipping_values', array(), 'j2store' );
		$view = $app->input->getString ( 'view', '' );
		//run the shipping only in the cart views. Do not run automatically in other views.
		if ( ( $showShipping && count ( $shipping_values )
				&& ( $view == 'cart' || $view == 'carts' || $view == 'checkout' || $view == 'checkouts' ) ) || $force
		) {

			$shipping_totals = array();

			//get existing values
			$shipping_values = $session->get ( 'shipping_values', array(), 'j2store' );
			$rates = F0FModel::getTmpInstance ( 'Shippings', 'J2StoreModel' )->getShippingRates ( $this );
			$session->set ( 'shipping_methods', $rates, 'j2store' );
			$auto_apply_shipping_rate = J2Store::config ()->get('auto_apply_shipping_rate',0);
			//auto_apply_shipping_rate
			if( $auto_apply_shipping_rate == 1 && empty( $shipping_values ) && !empty( $rates ) && !empty( $items ) ){
				// call estimate shipping
				// set default shipping

				$shipping_values = array();
				$shipping_values[ 'shipping_price' ] = isset( $rates[0][ 'price' ] ) ? $rates[0][ 'price' ] : 0;
				$shipping_values[ 'shipping_extra' ] = isset( $rates[0][ 'extra' ] ) ? $rates[0][ 'extra' ] : 0;
				$shipping_values[ 'shipping_code' ] = isset( $rates[0][ 'code' ] ) ? $rates[0][ 'code' ] : '';
				$shipping_values[ 'shipping_name' ] = isset( $rates[0][ 'name' ] ) ? $rates[0][ 'name' ] : '';
				$shipping_values[ 'shipping_tax' ] = isset( $rates[0][ 'tax' ] ) ? $rates[0][ 'tax' ] : 0;
				$shipping_values[ 'shipping_plugin' ] = isset( $rates[0][ 'element' ] ) ? $rates[0][ 'element' ] : '';
			}
			$is_same = false;
			foreach ( $rates as $rate ) {

				if ( isset( $shipping_values[ 'shipping_name' ] ) &&
					( trim ( $shipping_values[ 'shipping_name' ] ) == trim ( $rate[ 'name' ] ) )
				) {
					$shipping_values[ 'shipping_price' ] = isset( $rate[ 'price' ] ) ? $rate[ 'price' ] : 0;
					$shipping_values[ 'shipping_extra' ] = isset( $rate[ 'extra' ] ) ? $rate[ 'extra' ] : 0;
					$shipping_values[ 'shipping_code' ] = isset( $rate[ 'code' ] ) ? $rate[ 'code' ] : '';
					$shipping_values[ 'shipping_name' ] = isset( $rate[ 'name' ] ) ? $rate[ 'name' ] : '';
					$shipping_values[ 'shipping_tax' ] = isset( $rate[ 'tax' ] ) ? $rate[ 'tax' ] : 0;
					$shipping_values[ 'shipping_plugin' ] = isset( $rate[ 'element' ] ) ? $rate[ 'element' ] : '';
					$session->set ( 'shipping_method', $shipping_values[ 'shipping_plugin' ], 'j2store' );
					$session->set ( 'shipping_values', $shipping_values, 'j2store' );
					$is_same = true;
				}
			}
			if ( $is_same === false ) {
				//sometimes the previously selected method may not apply. In those cases, we will have remove the selected shipping.
				$session->set ( 'shipping_values', array(), 'j2store' );
				if( $auto_apply_shipping_rate == 1 && !empty( $rates ) && !empty( $items ) ){
					// call estimate shipping
					// set default shipping

					$shipping_values = array();
					$shipping_values[ 'shipping_price' ] = isset( $rates[0][ 'price' ] ) ? $rates[0][ 'price' ] : 0;
					$shipping_values[ 'shipping_extra' ] = isset( $rates[0][ 'extra' ] ) ? $rates[0][ 'extra' ] : 0;
					$shipping_values[ 'shipping_code' ] = isset( $rates[0][ 'code' ] ) ? $rates[0][ 'code' ] : '';
					$shipping_values[ 'shipping_name' ] = isset( $rates[0][ 'name' ] ) ? $rates[0][ 'name' ] : '';
					$shipping_values[ 'shipping_tax' ] = isset( $rates[0][ 'tax' ] ) ? $rates[0][ 'tax' ] : 0;
					$shipping_values[ 'shipping_plugin' ] = isset( $rates[0][ 'element' ] ) ? $rates[0][ 'element' ] : '';
					$session->set ( 'shipping_method', $shipping_values[ 'shipping_plugin' ], 'j2store' );
					$session->set ( 'shipping_values', $shipping_values, 'j2store' );
				}
			}
		}

		if ( $session->has ( 'shipping_values', 'j2store' ) && $showShipping ) {
			$shipping = $session->get ( 'shipping_values', array(), 'j2store' );
			if ( count ( $shipping ) && isset( $shipping[ 'shipping_name' ] ) ) {
				$this->setOrderShippingRate ( $shipping );
				$order_shipping = $this->_shipping_totals->ordershipping_price + $this->_shipping_totals->ordershipping_extra;
				$order_shipping_tax = $this->_shipping_totals->ordershipping_tax;
			}
		}
		$this->order_shipping = $order_shipping;
		$this->order_shipping_tax = $order_shipping_tax;
        $order_obj = $this->get_order_obj();
		J2Store::plugin ()->event ( "CalculateShippingTotals", array( &$order_obj ) );
	}

	function getOrderInformation ()
	{
		if ( !isset( $this->_orderinfo ) && !empty( $this->order_id ) ) {
			$this->_orderinfo = F0FTable::getInstance ( 'Orderinfo', 'J2StoreTable' );
			$this->_orderinfo->load ( array( 'order_id' => $this->order_id ) );
		}
		return $this->_orderinfo;
	}

	function setOrderInformation ()
	{

		$user = JFactory::getUser ();
		$session = JFactory::getSession ();
		$address_model = F0FModel::getTmpInstance ( 'Addresses', 'J2StoreModel' );

		//set shipping address
		if ( $user->id && $session->has ( 'shipping_address_id', 'j2store' ) ) {
			$shipping_address = $address_model->getAddressById ( $session->get ( 'shipping_address_id', '', 'j2store' ) );
		} elseif ( $session->has ( 'guest', 'j2store' ) ) {
			$guest = $session->get ( 'guest', array(), 'j2store' );
			$shipping_address = isset( $guest[ 'shipping' ] ) ? $guest[ 'shipping' ] : array();
		} else {
			$shipping_address = array();
		}


		$billing_address = array();
		if ( $user->id && $session->has ( 'billing_address_id', 'j2store' ) ) {
			$billing_address = $address_model->getAddressById ( $session->get ( 'billing_address_id', '', 'j2store' ) );
		} elseif ( $session->has ( 'guest', 'j2store' ) ) {
			$guest = $session->get ( 'guest', array(), 'j2store' );
			$billing_address = isset( $guest[ 'billing' ] ) ? $guest[ 'billing' ] : array();
		}

		$orderinfo = array();
		if ( $billing_address ) {
			foreach ( $billing_address as $key => $value ) {
				$orderinfo[ 'billing_' . $key ] = $value;
			}

			//custom fields
			$orderinfo[ 'all_billing' ] = $this->processCustomFields ( 'billing', $billing_address );
		}

		if ( $shipping_address ) {
			foreach ( $shipping_address as $key => $value ) {
				$orderinfo[ 'shipping_' . $key ] = $value;
			}

			$orderinfo[ 'all_shipping' ] = $this->processCustomFields ( 'shipping', $shipping_address );
		}

		if ( $session->has ( 'payment_values', 'j2store' ) ) {
			$pay_values = $session->get ( 'payment_values', array(), 'j2store' );
			$orderinfo[ 'all_payment' ] = $this->processCustomFields ( 'payment', $pay_values );
		}


		if ( $user->id ) {
			$user_email = $user->email;
		} else {
			$user_email = isset( $billing_address[ 'email' ] ) ? $billing_address[ 'email' ] : '';
		}

		$this->user_email = $user_email;

		$orderinfoTable = F0FTable::getAnInstance ( 'OrderInfo', 'J2StoreTable' );
		$orderinfoTable->bind ( $orderinfo );
		$this->_orderinfo = $orderinfoTable;

		J2Store::plugin ()->event ( "PrepareOrderInformation", array( $this ) );

	}

	/**
	 * Method to add fee.
	 *
	 * @param string $name
	 * @param float $amount
	 * @param bool $taxable (default: false)
	 * @param string $tax_class (default: '')
	 */
	public function add_fee ( $name, $amount, $taxable = false, $tax_class = '', $fee_type = '' )
	{

		//sanitize title
		$filter = new JFilterInput();

		$new_fee_id = $filter->clean ( $name );

		// Only add each fee once
		foreach ( $this->fees as $fee ) {
			if ( $fee->id == $new_fee_id ) {
				return;
			}
		}

		$new_fee = new stdClass();
		$new_fee->id = $new_fee_id;
		$new_fee->name = $name;
		$new_fee->amount = (float)$amount;
		$new_fee->tax_class_id = $tax_class;
		$new_fee->taxable = $taxable ? true : false ;
		$new_fee->tax = 0;
		$new_fee->tax_data = array();
		$new_fee->fee_type = $fee_type;
		$this->fees[] = $new_fee;
	}

	/**
	 * Method to get fees.
	 *
	 * @access public
	 * @return array
	 */
	public function get_fees ()
	{

		if ( !isset( $this->fees ) && !empty( $this->order_id ) ) {
			$this->fees = F0FModel::getTmpInstance ( 'Orderfees', 'J2StoreModel' )->order_id ( $this->order_id )->getList ();
		}
		return array_filter ( (array)$this->fees );
	}

	/**
	 * Method to calculate fee / additional costs to order.
	 * This is where the plugin event is triggered.
	 */

	public function getOrderFeeTotals ()
	{
		// Reset fees before calculation
		$this->order_fees = 0;
		$this->fees = array();
		// Fire an action where developers can add their fees
		J2Store::plugin ()->event ( 'CalculateFees', array( $this ) );
		// If fees were added, total them and calculate tax
		if ( !empty( $this->fees ) ) {
			foreach ( $this->fees as $fee_key => $fee ) {
                $this->order_fees += $fee->amount;
				if ( $fee->taxable && !empty( $fee->tax_class_id ) ) {
					// Get tax rates
					$taxModel = F0FModel::getTmpInstance ( 'TaxProfiles', 'J2StoreModel' );
                    $taxrates = $taxModel->getTaxwithRates ( $fee->amount, $fee->tax_class_id );
					if ( !empty( $taxrates->taxes ) ) {
						// Set the tax total for this fee
						$this->fees[ $fee_key ]->tax = $taxrates->taxtotal;
						$this->fees[ $fee_key ]->tax_data = $taxrates->taxes;

						foreach ( $taxrates->taxes as $taxrate_id => $tax ) {
							if ( !isset( $this->_taxrates[ $taxrate_id ] ) ) {
								$this->_taxrates[ $taxrate_id ][ 'name' ] = $tax[ 'name' ];
								$this->_taxrates[ $taxrate_id ][ 'rate' ] = $tax[ 'rate' ];
								$this->_taxrates[ $taxrate_id ][ 'total' ] = ( $tax[ 'amount' ] );
							} else {
								$this->_taxrates[ $taxrate_id ][ 'name' ] = $tax[ 'name' ];
								$this->_taxrates[ $taxrate_id ][ 'rate' ] = $tax[ 'rate' ];
								$this->_taxrates[ $taxrate_id ][ 'total' ] += ( $tax[ 'amount' ] );
							}

						}
					}
				}
			}
		}
	}

	/**
	 * Method to calculate fee / additional costs to admin order.
	 * This is where the plugin event is triggered.
	 */
	public function getAdminOrderFeeTotals ()
	{
		// Reset fees before calculation
		$this->order_fees = 0;
		$this->fees = array();
		// Fire an action where developers can add their fees
		J2Store::plugin ()->event ( 'CalculateFees', array( $this ) );
        if(J2Store::platform()->isClient('administrator')) {
            if (version_compare(JVERSION, '3.99.99', 'lt')) {
                //Remove Fee
                $this->removeOrderFees();
            }
        }else{
            //Remove Fee
            $this->removeOrderFees();
        }
		// If fees were added, total them and calculate tax
		if ( !empty( $this->fees ) ) {
			$session = JFactory::getSession ();
			$orderinfo = $this->getOrderInformation ();
			// shipping session
			$session->set ( 'shipping_country_id', $orderinfo->shipping_country_id,'j2store');
			$session->set ( 'shipping_zone_id', $orderinfo->shipping_zone_id,'j2store');
			$session->set ( 'shipping_postcode', $orderinfo->shipping_zip,'j2store');
			// billing session
			$session->set ( 'billing_country_id', $orderinfo->billing_country_id,'j2store');
			$session->set ( 'billing_zone_id', $orderinfo->billing_zone_id,'j2store');
			$session->set ( 'billing_postcode', $orderinfo->billing_zip,'j2store');
			foreach ( $this->fees as $fee_key => $fee ) {
                $this->order_fees += $fee->amount;
				$this->fees[ $fee_key ]->tax_data = array();
				if ( $fee->taxable && !empty( $fee->tax_class_id ) ) {
					// Get tax rates
					$taxModel = F0FModel::getTmpInstance ( 'TaxProfiles', 'J2StoreModel' );
                    $taxrates = $taxModel->getTaxwithRates ( $fee->amount, $fee->tax_class_id );
                    if ( !empty( $taxrates->taxes ) ) {
						// Set the tax total for this fee
						$this->fees[ $fee_key ]->tax = $taxrates->taxtotal;
						$this->fees[ $fee_key ]->tax_data = $taxrates->taxes;

						foreach ( $taxrates->taxes as $taxrate_id => $tax ) {
							if ( !isset( $this->_taxrates[ $taxrate_id ] ) ) {
								$this->_taxrates[ $taxrate_id ][ 'name' ] = $tax[ 'name' ];
								$this->_taxrates[ $taxrate_id ][ 'rate' ] = $tax[ 'rate' ];
								$this->_taxrates[ $taxrate_id ][ 'total' ] = ( $tax[ 'amount' ] );
							} else {
								$this->_taxrates[ $taxrate_id ][ 'name' ] = $tax[ 'name' ];
								$this->_taxrates[ $taxrate_id ][ 'rate' ] = $tax[ 'rate' ];
								$this->_taxrates[ $taxrate_id ][ 'total' ] += ( $tax[ 'amount' ] );
							}

						}
					}
				}
				$feeTable = F0FTable::getAnInstance ( 'OrderFee', 'J2StoreTable' )->getClone();
				$feeTable->load(0);
				$feeTable->bind($this->fees[ $fee_key ]);
				$feeTable->order_id = $this->order_id;
				$feeTable->tax_data = json_encode($this->fees[ $fee_key ]->tax_data);
				$feeTable->store();
			}
		}
	}

	/**
	 * Delete Order fees
	 * */
	protected function removeOrderFees(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->delete('#__j2store_orderfees');
		$query->where('order_id = '.$db->q($this->order_id));
		try {
			return $db->setQuery($query)->execute();
		}catch (Exception $e) {
			return false;
			//do nothing. Because this is not harmful even if it fails.
		}
	}

	/**
	 * Gets the shipping rate object
	 */
	public function getOrderShippingRate ()
	{
		if ( !isset( $this->_shipping_totals ) && !empty( $this->order_id ) ) {
			$this->_shipping_totals = F0FTable::getAnInstance ( 'Ordershipping', 'J2StoreTable' );
			$this->_shipping_totals->load ( array( 'order_id' => $this->order_id ) );
		}
		return $this->_shipping_totals;
	}

	/**
	 * Sets the shipping object for the order from a shipping_rate array,
	 * a standard array created by all shipping plugins as a valid shipping rate option during checkout
	 *
	 * @param array $rate
	 */
	public function setOrderShippingRate ( $values )
	{

		$ordershipping_table = F0FTable::getAnInstance ( 'Ordershipping', 'J2StoreTable' );

		$ordershipping_table->ordershipping_price = $values[ 'shipping_price' ];
		$ordershipping_table->ordershipping_extra = $values[ 'shipping_extra' ];
		$ordershipping_table->ordershipping_tax = $values[ 'shipping_tax' ];
		$ordershipping_table->ordershipping_code = $values[ 'shipping_code' ];
		$ordershipping_table->ordershipping_name = $values[ 'shipping_name' ];
		$ordershipping_table->ordershipping_type = $values[ 'shipping_plugin' ];
		$ordershipping_table->ordershipping_total = $values[ 'shipping_price' ] + $values[ 'shipping_extra' ] + $values[ 'shipping_tax' ];
		$this->_shipping_totals = $ordershipping_table;

	}

	function isShippingEnabled ()
	{
		$items = $this->getItems ();
        $platform = J2Store::platform();
		$status = false;
		foreach ( $items as $item ) {
			$registry = $platform->getRegistry($item->orderitem_params);
			if ( $registry->get ( 'shipping' ) ) {
				$status = true;
                break;
			}
		}
		return $status;
	}

	function needShipping ( $item )
	{
		$registry = J2Store::platform()->getRegistry($item->orderitem_params);
		return $registry->get ( 'shipping', 0 );
	}

	function getTotalShippingWeight ()
	{
		$items = $this->getItems ();
		$total = 0;
		if ( count ( $items ) < 1 ) return;

		foreach ( $items as $item ) {
			if ( $this->needShipping ( $item ) ) {
				$total += $item->orderitem_weight_total;
			}
		}
		return $total;
	}

	function setAddress ( $override = 'no' )
	{
		$session = JFactory::getSession ();
		$storeaddress = J2Store::storeProfile ();
		if ( $session->has ( 'shipping_country_id', 'j2store' ) || $session->has ( 'shipping_zone_id', 'j2store' ) || $session->get ( 'shipping_postcode', '', 'j2store' ) ) {
			$this->setShippingAddress ( $session->get ( 'shipping_country_id', '', 'j2store' ), $session->get ( 'shipping_zone_id', '', 'j2store' ), $session->get ( 'shipping_postcode', '', 'j2store' ) );
		} else {
			//	$this->setShippingAddress($storeaddress->country_id, $storeaddress->zone_id, $storeaddress->store_zip);
		}

		if ( $session->has ( 'billing_country_id', 'j2store' ) || $session->has ( 'billing_zone_id', 'j2store' ) || $session->get ( 'billing_postcode', '', 'j2store' ) ) {
			$this->setBillingAddress ( $session->get ( 'billing_country_id', '', 'j2store' ), $session->get ( 'billing_zone_id', '', 'j2store' ), $session->get ( 'billing_postcode', '', 'j2store' ) );
		} else {
			$this->setBillingAddress ( $storeaddress->get ( 'country_id' ), $storeaddress->get ( 'zone_id' ), $storeaddress->get ( 'store_zip' ) );
		}
		$this->setStoreAddress ( $storeaddress->get ( 'country_id' ), $storeaddress->get ( 'zone_id' ), $storeaddress->get ( 'store_zip' ) );
		//address override
		if ( $override == 'store' ) {
			$this->setShippingAddress ( $storeaddress->get ( 'country_id' ), $storeaddress->get ( 'zone_id' ), $storeaddress->get ( 'store_zip' ) );
		}

		$this->setGeozones ();

	}

	/**
	 * Based on the object's addresses,
	 * sets the shipping and billing geozones
	 *
	 * @return unknown_type
	 */
	function setGeozones ( $geozones = null, $type = 'billing' )
	{
		if ( !empty( $geozones ) ) {
			switch ( $type ) {
				case "shipping":
				default:
					$this->_shipping_geozones = $geozones;
					break;
				case "billing":
					$this->_billing_geozones = $geozones;
					break;
			}
		} else {
			require_once ( JPATH_ADMINISTRATOR . '/components/com_j2store/library/shipping.php' );
			if ( !empty( $this->_billing_address ) ) {
				$this->_billing_geozones = $this->getGeoZones ( $this->_billing_address[ 'country_id' ], $this->_billing_address[ 'zone_id' ], '1' );
			}
			if ( !empty( $this->_shipping_address ) ) {
				$this->_shipping_geozones = $this->getGeoZones ( $this->_shipping_address[ 'country_id' ], $this->_shipping_address[ 'zone_id' ], '2', $this->_shipping_address[ 'postal_code' ] );
			}
		}
	}


	public function setShippingAddress ( $country_id, $zone_id, $postal_code )
	{
		$this->_shipping_address = array(
			'country_id' => $country_id,
			'zone_id' => $zone_id,
			'postal_code' => $postal_code
		);
	}

	public function setBillingAddress ( $country_id, $zone_id, $postal_code )
	{
		$this->_billing_address = array(
			'country_id' => $country_id,
			'zone_id' => $zone_id,
			'postal_code' => $postal_code
		);
	}

	public function setStoreAddress ( $country_id, $zone_id, $postal_code )
	{
		$this->_store_address = array(
			'country_id' => $country_id,
			'zone_id' => $zone_id,
			'postal_code' => $postal_code
		);
	}

	/**
	 * Gets the order billing address
	 * @return unknown_type
	 */
	function getBillingAddress ()
	{
		// TODO If $this->_billing_address is null, attempt to populate it with the orderinfo fields, or using the billing_address_id (if present)
		return $this->_billing_address;
	}

	/**
	 * Gets the order shipping address
	 * @return unknown_type
	 */
	function getShippingAddress ()
	{
		// TODO If $this->_shipping_address is null, attempt to populate it with the orderinfo fields, or using the shipping_address_id (if present)
		return $this->_shipping_address;
	}

	public function getGeoZones ( $country_id, $zone_id, $geozonetype = '2', $zip_code = null, $update = false )
	{
		$return = array();
		if ( empty( $zone_id ) && empty( $country_id ) ) {
			return $return;
		}

		static $geozones = null; // static array for caching results
		if ( $geozones === null )
			$geozones = array();

		if ( $zip_code === null )
			$zip_code = 0;

		if ( isset( $geozones[ $geozonetype ][ $zone_id ][ $zip_code ] ) && !$update )
			return $geozones[ $geozonetype ][ $zone_id ][ $zip_code ];


		$db = JFactory::getDbo ();
		$query = $db->getQuery ( true );
		$query->select ( 'gz.*,gzr.*' )->from ( '#__j2store_geozones AS gz' )
			->leftJoin ( '#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.j2store_geozone_id' )
			->where ( 'gzr.country_id=' . $db->q ( $country_id ) . ' AND (gzr.zone_id=0 OR gzr.zone_id=' . $db->q ( $zone_id ) . ')' );

		if ( $zip_code ) {
			//TODO add filter by postcode
		}
		$db->setQuery ( $query );
		$items = $db->loadObjectList ();

		if ( !empty( $items ) ) {
			$return = $items;
		}
		$geozones[ $geozonetype ][ $zone_id ][ $zip_code ] = $return;
		return $return;
	}

	function getGeozone ( $country_id, $zone_id, $zip_code = null, $geozone_id = null )
	{
		$return = array();

		if ( empty ( $zone_id ) && empty ( $country_id ) ) {
			return $return;
		}

		static $geozone = null; // static array for caching results
		if ( $geozone === null )
			$geozone = array();

		if ( $zip_code === null )
			$zip_code = 0;

		if ( $geozone_id == null || empty( $geozone_id ) )
			$geozone_id = 0;

		if ( !isset ( $geozone [ $country_id ] [ $zone_id ] [ $zip_code ] [ $geozone_id ] ) ) {
			$items = array();
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true );
			$query->select ( 'gz.*,gzr.*' )->from ( '#__j2store_geozones AS gz' )
				->leftJoin ( '#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.j2store_geozone_id' )
				->where ( 'gz.j2store_geozone_id=' . $db->q($geozone_id ))
				->where ( 'gzr.country_id=' . $db->q ( $country_id ) . ' AND (gzr.zone_id=0 OR gzr.zone_id=' . $db->q ( $zone_id ) . ')' );
			$db->setQuery ( $query );
			try {
				$items = $db->loadObjectList ();
			} catch ( Exception $e ) {
				//do nothing.
			}

			if ( !empty ( $items ) ) {
				$return = $items;
			}

			$geozone [ $country_id ] [ $zone_id ] [ $zip_code ] [ $geozone_id ] = $return;
		}

		return $geozone [ $country_id ] [ $zone_id ] [ $zip_code ] [ $geozone_id ];
	}

	/**
	 * Gets the order's shipping geozones
	 *
	 * @return unknown_type
	 */
	function getShippingGeoZones ()
	{
		return $this->_shipping_geozones;
	}

	public function processCustomFields ( $type, $data )
	{
        $platform = J2Store::platform();
		$selectableBase = J2Store::getSelectableBase ();
		$address = F0FTable::getAnInstance ( 'Address', 'J2StoreTable' );
		$orderinfo = F0FTable::getAnInstance ( 'Orderinfo', 'J2StoreTable' );

		$fields = $selectableBase->getFields ( $type, $address, 'address' );

		if ( is_array ( $data ) ) {
			$data = $platform->toObject ( $data );
		}

		$values = array();
		foreach ( $fields as $fieldName => $oneExtraField ) {

			if ( isset( $data->$fieldName ) ) {
				if ( !property_exists ( $orderinfo, $type . '_' . $fieldName ) && !property_exists ( $orderinfo, 'user_' . $fieldName ) && $fieldName != 'country_id' && $fieldName != 'zone_id' && $fieldName != 'option' && $fieldName != 'task' && $fieldName != 'view' ) {
                    if(isset($oneExtraField->field_type) && $oneExtraField->field_type == 'zone'){
                        $values[ $fieldName ][ 'zone_type' ] = $oneExtraField->field_options['zone_type'];
                    }
					$values[ $fieldName ][ 'label' ] = $oneExtraField->field_name;
					$values[ $fieldName ][ 'value' ] = $data->$fieldName;
				}
			}
		}

		$registry = $platform->getRegistry($values, true);
		$json = $registry->toString ( 'JSON' );
		return $json;

	}

	function saveOrder ()
	{

		$app = JFactory::getApplication ();
		$user = JFactory::getUser ();
		$lang = JFactory::getLanguage ();
		$session = JFactory::getSession ();
		$params = J2Store::config ();

		//cart id
		$this->cart_id = F0FModel::getTmpInstance ( 'Carts', 'J2StoreModel' )->getCartId ();

		//	if(!isset($this->order_id) || empty($this->order_id) || $this->is_update != 1) {
		//	$this->order_id = time().$this->cart_id;
		//	}
		//set order values
		$this->user_id = $user->id;


		$this->ip_address = $_SERVER[ 'REMOTE_ADDR' ];


		$this->customer_note = $session->get ( 'customer_note', '', 'j2store' );
		$this->customer_language = $lang->getTag ();
		$this->customer_group = implode ( ',', JAccess::getGroupsByUser ( $user->id, false ) );
		//	$this->customer_group = implode(',', JAccess::getAuthorisedViewLevels($user->id, false));


		//set a default order status.
		$default_order_state = 5;

		$this->order_state_id = $default_order_state;

		//get currency id, value and code and store it
		$currency = J2Store::currency ();
		$this->currency_id = $currency->getId ();
		$this->currency_code = $currency->getCode ();
		$this->currency_value = $currency->getValue ( $currency->getCode () );

		$this->is_including_tax = $params->get ( 'config_including_tax', 0 );

		//sanity check for user email
		if ( empty( $this->user_email ) ) {

			if ( $user->id ) {
				$user_email = $user->email;
			} else {
				$guest = $session->get ( 'guest', array(), 'j2store' );
				$billing_address = isset( $guest[ 'billing' ] ) ? $guest[ 'billing' ] : array();
				$user_email = isset( $billing_address[ 'email' ] ) ? $billing_address[ 'email' ] : '';
			}
			$this->user_email = $user_email;
		}
        $order_obj = $this->get_order_obj();
		//trigger on before save
		J2Store::plugin ()->event ( 'BeforeSaveOrder', array( &$order_obj ) );

		if ( $this->is_update == 1 ) {
			//trigger on before update
			J2Store::plugin ()->event ( 'BeforeUpdateOrder', array( &$order_obj ) );
		} else {
			//trigger on before create a new order
			J2Store::plugin ()->event ( 'BeforeCreateNewOrder', array( &$order_obj ) );
		}

		try {
			if ( $this->store () ) {

				if ( !isset( $this->order_id ) || empty( $this->order_id ) || !isset( $this->is_update ) || $this->is_update != 1 ) {
					$this->order_id = time () . $this->j2store_order_id;

					//generate invoice number
					$this->generateInvoiceNumber ();

					//generate a unique hash
					$this->token = JApplicationHelper::getHash ( $this->order_id );

					//save again so that the unique order id is saved.
					$this->store ();
				}

				//saved.
				//save all related tables as well
				$this->saveOrderItems ();

				$this->saveOrderInfo ();

				$this->saveOrderShipping ();

				$this->saveOrderFees ();

				$this->saveOrderTax ();

				$this->saveOrderDiscounts ();

				$this->saveOrderFiles ();
                $order_obj = $this->get_order_obj();
				//trigger on before save
				J2Store::plugin ()->event ( 'AfterSaveOrder', array( &$order_obj ) );

				if ( $this->is_update == 1 ) {
					$this->add_history ( JText::_ ( 'J2STORE_ORDER_UPDATED_BY_CUSTOMER' ) );
					//trigger on before update
					J2Store::plugin ()->event ( 'AfterUpdateOrder', array( &$order_obj ) );
				} else {
					$this->add_history ( JText::_ ( 'J2STORE_NEW_ORDER_CREATED' ) );
					//trigger on before update
					J2Store::plugin ()->event ( 'AfterCreateNewOrder', array( &$order_obj ) );
				}
			}

		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
			return false;
		}

		return $this;
	}

	/**
	 * Update an existing order. This normally happens during the checkout.
	 * Customer will reach final step. The order will be saved. if he then changes something before proceeding to payment
	 * Then only existing order will get updated.
	 *
	 */


	function updateOrder ()
	{

		$this->is_update = 1;
		//its an existing order. So remove already saved information.
		$this->deleteChildren ( $this->j2store_order_id, $this );
		// we need to reset certain totals
		$this->resetTotals ();
	}

	function resetTotals ()
	{
		$this->order_surcharge = 0;
	}

	function saveOrderItems ()
	{

		$items = $this->getItems ();
		foreach ( $items as $item ) {
			unset( $orderitem );
			$orderitem = F0FTable::getAnInstance ( 'Orderitem', 'J2StoreTable' )->getClone ();
			$orderitem->bind ( $item );
			$orderitem->j2store_orderitem_id = 0;
			$orderitem->order_id = $this->order_id;
			$orderitem->store ();

			//save order attributes
			if ( isset( $item->orderitemattributes ) ) {
				$this->saveOrderItemAttributes ( $item->orderitemattributes, $orderitem );
			}
		}

	}

	function saveOrderItemAttributes ( $attributes, $orderitem )
	{
		foreach ( $attributes as $attribute ) {
			unset( $orderitemattribute );
			$orderitemattribute = F0FTable::getAnInstance ( 'OrderItemAttribute', 'J2StoreTable' )->getClone ();
			$orderitemattribute->bind ( $attribute );
			$orderitemattribute->j2store_orderitemattribute_id = 0;
			$orderitemattribute->orderitem_id = $orderitem->j2store_orderitem_id;
			$orderitemattribute->store ();
		}

	}

	function saveOrderInfo ()
	{
		$orderinfo = $this->getOrderInformation ();
		$orderinfo->j2store_orderinfo_id = 0;
		$orderinfo->order_id = $this->order_id;
		$orderinfo->store ();
	}

	function saveOrderShipping ()
	{
		if ( isset( $this->_shipping_totals ) && is_object ( $this->_shipping_totals ) ) {
			$this->_shipping_totals->order_id = $this->order_id;
			$this->_shipping_totals->store ( $this->_shipping_totals );
		}
	}

	function saveOrderFees ()
	{
		$fees = $this->get_fees ();
		J2Store::plugin ()->event("OnBeforeSaveOrderFees",array($this, &$fees));

		foreach ( $fees as $fee ) {
			$fee_table = F0FTable::getInstance ( 'Orderfee', 'J2StoreTable' )->getClone ();

            if ( is_array ( $fee->tax_data ) ) {
				$fee->tax_data = json_encode ( $fee->tax_data );
			}

			$fee_table->bind ( $fee );
			$fee_table->order_id = $this->order_id;
            $fee_table->taxable = (int)$fee->taxable;
			$fee_table->store ();

		}
	}

	function saveOrderTax ()
	{
		if ( isset( $this->_ordertaxes ) && count ( $this->_ordertaxes ) ) {
			foreach ( $this->_ordertaxes as $ordertax ) {
				$ordertax->order_id = $this->order_id;
				$ordertax->store ();
			}
		}
	}

	function saveOrderDiscounts ()
	{
		if ( isset( $this->_orderdiscounts ) && count ( $this->_orderdiscounts ) ) {
			foreach ( $this->_orderdiscounts as $discount ) {
				$discount->order_id = $this->order_id;
				if ( !isset( $discount->discount_customer_email ) ) {
					$discount->discount_customer_email = $this->user_email;
				}
				$discount->user_id = $this->user_id;
				$discount_table = F0FTable::getInstance ( 'Orderdiscount', 'J2StoreTable' )->getClone ();
				$discount_table->bind ( $discount );
				$discount_table->store ();
			}
		}
	}

	function saveOrderFiles ()
	{

		$db = JFactory::getDbo ();
		$items = $this->getItems ();
		foreach ( $items as $item ) {
			//get the list of files based on
			if ( $item->product_type == 'downloadable' ) {
				unset( $orderdownloads );
				$orderdownloads = F0FTable::getAnInstance ( 'Orderdownload', 'J2StoreTable' )->getClone ();
				$orderdownloads->order_id = $this->order_id;
				$orderdownloads->product_id = $item->product_id;
				$orderdownloads->user_id = $this->user_id;
				$orderdownloads->user_email = $this->user_email;
				$orderdownloads->access_granted == $db->getNullDate ();
				$orderdownloads->access_expires == $db->getNullDate ();
				$orderdownloads->store ();
			}
			J2Store::plugin ()->event("SaveOrderFiles",array($this,$item));
		}
	}

	public function getOrderDownloads ()
	{
		if ( count ( $this->_orderdownloads ) < 1 ) {
			$model = F0FModel::getTmpInstance ( 'Orderdownloads', 'J2StoreModel' );
			$model->setState ( 'order_id', $this->order_id );
			$model->setState ( 'email', $this->user_email );
			$this->_orderdownloads = $model->getList ();
		}
		return $this->_orderdownloads;
	}


	public function getOrderHistory ()
	{

		if ( count ( $this->_orderhistory ) < 1 ) {

			$model = F0FModel::getTmpInstance ( 'Orderhistories', 'J2StoreModel' );
			$model->setState ( 'order_id', $this->order_id );
			$this->_orderhistory = $model->getList ();
		}
		return $this->_orderhistory;
	}

	public function add_history ( $note = '' )
	{
		F0FModel::getTmpInstance ( 'Orderhistories', 'J2StoreModel' )->setOrderHistory ( $this, $note );
	}

	/**
	 * The event which runs before storing (saving) data to the database
	 *
	 * @param   boolean $updateNulls Should nulls be saved as nulls (true) or just skipped over (false)?
	 *
	 * @return  boolean  True to allow saving
	 */
	protected function onBeforeStore ( $updateNulls )
	{
		// Do we have a "Created" set of fields?
		$created_on = $this->getColumnAlias ( 'created_on' );
		$created_by = $this->getColumnAlias ( 'created_by' );
		$modified_on = $this->getColumnAlias ( 'modified_on' );
		$modified_by = $this->getColumnAlias ( 'modified_by' );
		$locked_on = $this->getColumnAlias ( 'locked_on' );
		$locked_by = $this->getColumnAlias ( 'locked_by' );
		$title = $this->getColumnAlias ( 'title' );
		$slug = $this->getColumnAlias ( 'slug' );

		$hasCreatedOn = in_array ( $created_on, $this->getKnownFields () );
		$hasCreatedBy = in_array ( $created_by, $this->getKnownFields () );

		if ( $hasCreatedOn && $hasCreatedBy ) {
			$hasModifiedOn = in_array ( $modified_on, $this->getKnownFields () );
			$hasModifiedBy = in_array ( $modified_by, $this->getKnownFields () );
			$tz = JFactory::getConfig ()->get ( 'offset' );
			$date = F0FPlatform::getInstance ()->getDate ( 'now', $tz, false );
			$nullDate = $this->_db->getNullDate ();

			if ( ( $this->$created_on == $nullDate ) || empty( $this->$created_on ) ) {
				$uid = F0FPlatform::getInstance ()->getUser ()->id;

				if ( $uid ) {
					$this->$created_by = F0FPlatform::getInstance ()->getUser ()->id;
				}

				$this->$created_on = $date->toSql ();
				//$date = F0FPlatform::getInstance()->getDate('now', null, false);
				//$this->$created_on = $date->toSql();

			} elseif ( $hasModifiedOn && $hasModifiedBy ) {
				$uid = F0FPlatform::getInstance ()->getUser ()->id;

				if ( $uid ) {
					$this->$modified_by = F0FPlatform::getInstance ()->getUser ()->id;
				}

				//$date = F0FPlatform::getInstance()->getDate('now', null, false);

				$this->$modified_on = $date->toSql ();
			}
		}

		// Do we have a set of title and slug fields?
		$hasTitle = in_array ( $title, $this->getKnownFields () );
		$hasSlug = in_array ( $slug, $this->getKnownFields () );

		if ( $hasTitle && $hasSlug ) {
			if ( empty( $this->$slug ) ) {
				// Create a slug from the title
				$this->$slug = F0FStringUtils::toSlug ( $this->$title );
			} else {
				// Filter the slug for invalid characters
				$this->$slug = F0FStringUtils::toSlug ( $this->$slug );
			}

			// Make sure we don't have a duplicate slug on this table
			$db = $this->getDbo ();
			$query = $db->getQuery ( true )
				->select ( $db->qn ( $slug ) )
				->from ( $this->_tbl )
				->where ( $db->qn ( $slug ) . ' = ' . $db->q ( $this->$slug ) )
				->where ( 'NOT ' . $db->qn ( $this->_tbl_key ) . ' = ' . $db->q ( $this->{$this->_tbl_key} ) );
			$db->setQuery ( $query );
			$existingItems = $db->loadAssocList ();

			$count = 0;
			$newSlug = $this->$slug;

			while ( !empty( $existingItems ) ) {
				$count++;
				$newSlug = $this->$slug . '-' . $count;
				$query = $db->getQuery ( true )
					->select ( $db->qn ( $slug ) )
					->from ( $this->_tbl )
					->where ( $db->qn ( $slug ) . ' = ' . $db->q ( $newSlug ) )
					->where ( 'NOT ' . $db->qn ( $this->_tbl_key ) . ' = ' . $db->q ( $this->{$this->_tbl_key} ) );
				$db->setQuery ( $query );
				$existingItems = $db->loadAssocList ();
			}

			$this->$slug = $newSlug;
		}
        $order_obj = $this->get_order_obj();
		// Call the behaviors
		$result = $this->tableDispatcher->trigger ( 'onBeforeStore', array( &$order_obj, $updateNulls ) );

		if ( in_array ( false, $result, true ) ) {
			// Behavior failed, return false
			return false;
		}

		// Execute onBeforeStore<tablename> events in loaded plugins
		if ( $this->_trigger_events ) {
			$name = F0FInflector::pluralize ( $this->getKeyName () );
            $order_obj = $this->get_order_obj();
			$result = F0FPlatform::getInstance ()->runPlugins ( 'onBeforeStore' . ucfirst ( $name ), array( &$order_obj, $updateNulls ) );

			if ( in_array ( false, $result, true ) ) {
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer $oid The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete ( $oid )
	{

		$status = true;
		// Load the post record
		$item = clone $this;
		$item->load ( $oid );
		if ( $oid ) {
			//make sure that any product using this options before delete the
			$this->deleteChildren ( $oid, $item );
		}
		return $status;
	}

	private function deleteChildren ( $oid, $order )
	{

		if ( empty( $order->order_id ) ) return;
		$db = JFactory::getDbo ();
		J2Store::plugin ()->event ( 'BeforeResetOrder', array( $order ) );
		$orderItems = F0FModel::getTmpInstance ( 'Orderitems', 'J2StoreModel' )->getItemsByOrder ( $order->order_id );

		//loop all the orderitem
		foreach ( $orderItems as $item ) {
			// check orderitem row exists
			//will delete orderitem and children table orderitemattribute
			F0FTable::getAnInstance ( 'Orderitem', 'J2StoreTable' )->delete ( $item->j2store_orderitem_id );
			J2Store::plugin ()->event ( 'AfterResetOrderItem', array( $item ) );
		}
		//delete orderinfo table
		$orderinfo = F0FTable::getAnInstance ( 'Orderinfo', 'J2StoreTable' );
		if ( $orderinfo->load ( array( 'order_id' => $order->order_id ) ) ) {
			$orderinfo->delete ();
		}

		//order downloads
		$query = $db->getQuery ( true )->delete ( '#__j2store_orderdownloads' )->where ( 'order_id = ' . $db->q ( $order->order_id ) );
		$db->setQuery ( $query )->execute ();

		//order history
		if ( !isset( $order->is_update ) || $order->is_update != 1 ) {
			$query = $db->getQuery ( true )->delete ( '#__j2store_orderhistories' )->where ( 'order_id = ' . $db->q ( $order->order_id ) );
			$db->setQuery ( $query )->execute ();
		}

		//shipping
		$ordershipping = F0FTable::getAnInstance ( 'Ordershipping', 'J2StoreTable' );
		if ( $ordershipping->load ( array( 'order_id' => $order->order_id ) ) ) {
			$ordershipping->delete ();
		}

		//order taxes
		$query = $db->getQuery ( true )->delete ( '#__j2store_ordertaxes' )->where ( 'order_id = ' . $db->q ( $order->order_id ) );
		$db->setQuery ( $query )->execute ();

		//order fees
		$query = $db->getQuery ( true )->delete ( '#__j2store_orderfees' )->where ( 'order_id = ' . $db->q ( $order->order_id ) );
		$db->setQuery ( $query )->execute ();

		//order discounts
		$query = $db->getQuery ( true )->delete ( '#__j2store_orderdiscounts' )->where ( 'order_id = ' . $db->q ( $order->order_id ) );
		$db->setQuery ( $query )->execute ();

		J2Store::plugin ()->event ( 'AfterDeleteOrderChildren', array( $order ) );
		return true;
	}

	public function payment_complete ( $order_state_id = 1 )
	{

		$app = JFactory::getApplication ();

		//event before marking an order complete
		J2Store::plugin ()->event ( 'BeforePaymentComplete', array( $this ) );

		//valid order statuses.
		//3 = failed, 4 = pending, 5=new or incomplete
		$valid_order_statuses = array( 3, 4, 5, 6 );
        J2Store::plugin ()->event ( 'BeforePaymentValidStatus', array( &$valid_order_statuses, $this ) );
		$old_status = $this->order_state_id;

		if ( !empty( $this->order_id ) && $this->has_status ( $valid_order_statuses ) ) {

			$order_needs_processing = true;

			//set status to confirmed
			$this->update_status ( $order_state_id );
			if ( $old_status != 4 ) {  //Pending orders have their stock already reduced. So no need to reduce again
				$this->reduce_order_stock (); // Payment is complete so reduce stock levels
			}

			//grant permissions to file download
			$this->grant_download_permission ();

			//notify customer
			$this->notify_customer ();

			J2Store::plugin ()->event ( 'AfterPaymentComplete', array( $this ) );

		}
	}

	/**
	 * Checks the order status against a passed in status.
	 *
	 * @return bool
	 */
	public function has_status ( $status )
	{
		$result = ( ( is_array ( $status ) && in_array ( $this->get_status (), $status ) ) || $this->get_status () === $status ) ? true : false;
		J2Store::plugin ()->event ( 'OrderHasStatus', array( &$result, $this, &$status ) );
		return $result;
	}

	public function get_status ()
	{
		return !empty( $this->order_state_id ) ? $this->order_state_id : $this->order_state_id;
	}

	public function update_status ( $new_status, $force_notify_customer = false )
	{
		if ( empty ( $this->order_id ) )
			return;
		$app = JFactory::getApplication ();
		$old_status = $this->get_status ();
		// update only when the status is new
		if ( $new_status !== $old_status ) {

			//trigger event before update
			J2Store::plugin ()->event ( 'BeforeOrderstatusUpdate', array( $this, $new_status ) );

			// first update the order
			$this->order_state_id = $new_status;
			$this->store ();

			$this->add_history ( JText::sprintf ( 'J2STORE_ORDER_STATUS_CHANGED', $old_status, $new_status ) );

			//trigger event after update
			J2Store::plugin ()->event ( 'AfterOrderstatusUpdate', array( $this, $new_status ) );

			//process more triggers

			switch ( $new_status ) {

				case '1' :
					// Record the sales
					$this->record_product_sales ();

					// Increase coupon usage counts
					//$this->increase_coupon_usage_counts ();
					break;
				case '6' :
					// Increase coupon usage counts
					$this->reduce_coupon_usage_counts ();
					break;
			}
		}

		if ( $force_notify_customer ) {
			$this->notify_customer ();
		}
	}

	public function record_product_sales ()
	{
		if ( sizeof ( $this->getItems () ) > 0 ) {

			foreach ( $this->getItems () as $item ) {

				if ( $item->variant_id > 0 ) {
					$table = F0FTable::getAnInstance ( 'Variant', 'J2StoreTable' )->getClone ();
					if ( $table->load ( $item->variant_id ) ) {

						$sales = ( int )$table->sold;
						$sales += ( int )$item->orderitem_quantity;

						if ( $sales ) {
							$table->sold = $sales;
							try {
								$table->store ();
							} catch ( Exception $e ) {
								//do nothing.
							}
							unset ( $table );
						}
					}
				}
			}
		}
	}

	public function increase_coupon_usage_counts ()
	{

	}

	public function reduce_coupon_usage_counts ()
	{
		//remove coupon from the cancelled order
		if ( empty( $this->order_id ) ) return;
		$table = F0FTable::getInstance ( 'Orderdiscount', 'J2StoreTable' )->getClone ();
		if ( $table->load ( array( 'order_id' => $this->order_id ) ) ) {
			$table->delete ();
		}
	}

    public function notify_customer ($is_admin_only = false)
    {
        if ( empty ( $this->order_id ) )
            return;

        $emailHelper = J2Store::email ();
        J2Store::plugin ()->event ( 'BeforeOrderEmailNotification', array($this) );
        if(!$is_admin_only){
            // send customer emails
            $customer_emails = $emailHelper->getOrderEmails ( $this, 'customer' );
            foreach ( $customer_emails as $email ) {
                if ( !isset( $email->mailer ) && !$email->mailer instanceof JMail ) continue;
                J2Store::plugin ()->event ( 'BeforeOrderNotification', array(
                    $this,
                    &$email->mailer
                ) );
                try {
                    if ( count ( $email->mailer->getAllRecipientAddresses () ) && $email->mailer->send () ) {
                        $this->add_history ( JText::_ ( 'J2STORE_CUSTOMER_NOTIFIED_WITH_SUBJECT' ) . ' ' . $email->mailer->Subject );
                        J2Store::plugin ()->event ( 'AfterOrderNotification', array(
                            $this
                        ) );
                    }
                } catch ( Exception $e ) {
                    $this->add_history ( $e->getMessage () );
                }

            }
        }

        // send admin emails
        $admin_emails = $emailHelper->getOrderEmails ( $this, 'admin' );
        foreach ( $admin_emails as $admin_email ) {
            if ( !isset( $admin_email->mailer ) && !$admin_email->mailer instanceof JMail ) continue;

            J2Store::plugin ()->event ( 'BeforeOrderNotificationAdmin', array(
                $this,
                &$admin_email->mailer
            ) );
            try {
                if ( count ( $admin_email->mailer->getAllRecipientAddresses () ) && $admin_email->mailer->send () ) {
                    $this->add_history ( JText::_ ( 'J2STORE_ADMINISTRATORS_NOTIFIED_WITH_SUBJECT' ) . ' ' . $admin_email->mailer->Subject );
                }
            } catch ( Exception $e ) {
                $this->add_history ( $e->getMessage () );
            }
        }
    }

	public function reduce_order_stock ()
	{

		$app = JFactory::getApplication ();

		foreach ( $this->getItems () as $item ) {

			if ( $item->product_id > 0 && $item->variant_id > 0 ) {
				//get the variant
				$variant_model = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->getClone ();
				$variant = $variant_model->getItem ( $item->variant_id );
				if ( $variant && J2Store::product ()->managing_stock ( $variant ) ) {
					J2Store::plugin ()->event ( 'BeforeStockReduction', array( $this, &$item ) );

                    $add_history_status = true;
					if( isset($variant->allow_backorder) && $variant->allow_backorder >= 1){
                        $productquantity = F0FTable::getAnInstance('ProductQuantity', 'J2StoreTable')->getClone();
                        $productquantity->load(array('variant_id' => $item->variant_id));
                        if($productquantity->quantity == 0){
                            $add_history_status = false;
                        }
                    }
                    $new_stock = $variant->reduce_stock ( $item->orderitem_quantity );
                    if($add_history_status){
                        $this->add_history ( JText::sprintf ( 'J2STORE_ORDERITEM_STOCK_REDUCED', $item->orderitem_name, $new_stock + $item->orderitem_quantity, $new_stock ) );
                    }else{
                        $this->add_history ( JText::sprintf ( 'J2STORE_ORDERITEM_STOCK_BACK_ORDERED', $item->orderitem_name) );
                    }
					$this->send_stock_notifications ( $variant, $new_stock, $item->orderitem_quantity );
				}
			}

		}
	}

	/**
	 * Method to restore order stock (Called when orders are cancelled)
	 */

	public function restore_order_stock ()
	{
		$app = JFactory::getApplication ();

		foreach ( $this->getItems () as $item ) {

			if ( $item->product_id > 0 && $item->variant_id > 0 ) {
				$variant_model = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->getClone ();
				$variant = $variant_model->getItem ( $item->variant_id );
				if ( $variant && J2Store::product ()->managing_stock ( $variant ) ) {
					$old_stock = $variant->quantity;
					J2Store::plugin ()->event ( 'BeforeStockRestore', array( $this, &$item ) );
					$new_quantity = $variant->increase_stock ( $item->orderitem_quantity );

					$this->add_history ( JText::sprintf ( 'J2STORE_ORDERITEM_STOCK_INCREASED', $item->orderitem_name, $old_stock, $new_quantity ) );
					$this->send_stock_notifications ( $variant, $new_quantity, $item->orderitem_quantity );
				}
			}
		}
	}

	public function send_stock_notifications ( $variant, $new_stock, $qty_ordered )
	{

		$app = JFactory::getApplication ();
		// Backorders
		if ( $new_stock < 0 ) {
			J2Store::plugin ()->event ( 'ProductOnBackorder', array( $variant, $this->order_id, $qty_ordered ) );
		}

		// stock status notifications
		$notification_sent = false;

		if ( $new_stock <= 0 ) {
			J2Store::plugin ()->event ( 'NotifyNoStock', array( $variant ) );
			$notification_sent = true;
		}

		J2Store::product ()->getQuantityRestriction ( $variant );
		if ( !$notification_sent && $variant->notify_qty >= $new_stock ) {
			J2Store::plugin ()->event ( 'NotifyLowStock', array( $variant, $new_stock ) );
			$notification_sent = true;
		}
	}

	public function empty_cart ()
	{
		if ( !isset( $this->order_id ) || empty( $this->order_id ) ) return;

		$cart = F0FTable::getAnInstance ( 'Cart', 'J2StoreTable' )->getClone();
		if ( $cart->load ( $this->cart_id ) ) {
			$cartobject = $cart;
			J2Store::plugin ()->event ( 'BeforeEmptyCart', array( $cartobject ) );
			$cart->delete ();
			J2Store::plugin ()->event ( 'AfterEmptyCart', array( $cartobject ) );
		}
	}

	public function generateInvoiceNumber ()
	{
		if ( empty( $this->order_id ) ) return;

		$db = JFactory::getDbo ();
		$status = true;
		$store = J2Store::storeProfile ();
		$store_invoice_prefix = $store->get ( 'invoice_prefix' );
		if ( !isset( $store_invoice_prefix ) || empty( $store_invoice_prefix ) ) {
			//backward compatibility. If no prefix is set, retain the invoice number is the table primary key.
			$status = false;
		}

		if ( $status ) {
			//get the last row
			$query = $db->getQuery ( true )->select ( 'MAX(invoice_number) AS invoice_number' )
				->from ( '#__j2store_orders' )->where ( 'invoice_prefix=' . $db->q ( $store->get ( 'invoice_prefix' ) ) );
			$db->setQuery ( $query );
			$row = $db->loadObject ();
			if ( isset( $row->invoice_number ) && $row->invoice_number ) {
				$invoice_number = $row->invoice_number + 1;
			} else {
				$invoice_number = 1;
			}
			$this->invoice_number = $invoice_number;
			$this->invoice_prefix = $store->get ( 'invoice_prefix' );
			$order = $this;
			J2Store::plugin ()->event ( 'AfterGenerateInvoiceNumber', array(&$order) );
		}
	}

	public function getInvoiceNumber ()
	{
		if ( empty( $this->order_id ) ) return;
		if ( isset( $this->invoice_number ) && $this->invoice_number > 0 ) {
			$invoice_number = $this->invoice_prefix . $this->invoice_number;
		} else {
			$invoice_number = $this->j2store_order_id;
		}
		$order = $this;
		J2Store::plugin ()->event ( 'AfterGetInvoiceNumber', array(&$invoice_number,$order) );
		return $invoice_number;
	}

	public function has_downloadable_item ()
	{

		if ( empty ( $this->order_id ) )
			return false;

		$has_item = false;
		$items = $this->getItems ();
		foreach ( $items as $item ) {
			// check if product exists
			$product = F0FTable::getInstance ( 'Product', 'J2StoreTable' )->getClone ();
			$product->load ( $item->product_id );

			if ( $product->is_valid_product () && $product->is_downloadable () && $product->has_file () ) {
				$has_item = true;
			}
		}

		return $has_item;
	}

	public function grant_download_permission ()
	{
		if ( empty( $this->order_id ) ) return;
		F0FModel::getTmpInstance ( 'Orderdownloads', 'J2StoreModel' )->setDownloads ( $this, true );
		J2Store::plugin ()->event ( 'GrantDownloadPermission', array( $this ) );
		return true;
	}

	public function reset_download_expiry ()
	{
		if ( empty( $this->order_id ) ) return;
		F0FModel::getTmpInstance ( 'Orderdownloads', 'J2StoreModel' )->resetDownloads ( $this, true );
		J2Store::plugin ()->event ( 'ResetDownloadExpiry', array( $this ) );
		return true;
	}

    public function reset_download_limit ()
    {
        if ( empty( $this->order_id ) ) return;
        F0FModel::getTmpInstance ( 'Orderdownloads', 'J2StoreModel' )->resetDownloadLimit ( $this, true );
        J2Store::plugin ()->event ( 'ResetDownloadLimit', array( $this ) );
        return true;
    }

	public function get_customer_language()
	{
		if ( empty( $this->order_id ) ) return;

        //$lang = JFactory::getLanguage();
		$lang_data =JLanguageHelper::getMetadata( $this->customer_language );

		if ( isset( $lang_data[ 'name' ] ) ) {
			$customer_language = $lang_data[ 'name' ];
		} else {
			$customer_language = $this->customer_language;
		}
		return $customer_language;
	}

	public function get_payment_method ()
	{
		// order payment type may not be available immediately at this point of time in the order object.
		// so it has to be received from the session.
		$payment_method = '';
		$session = JFactory::getSession ();
		if ( $session->has ( 'payment_method', 'j2store' ) ) {
			$payment_method = $session->get ( 'payment_method', '', 'j2store' );
		} else {
			$payment_values = $session->get ( 'payment_values', array(), 'j2store' );
			$payment_method = isset( $payment_values[ 'payment_plugin' ] ) ? $payment_values[ 'payment_plugin' ] : '';
		}
		return $payment_method;
	}

	/**
	 * Get line item name .
	 * @param $item - order item object
     * @param string $receiver_type
	 * @return string - item name
	 *
	*/
	function get_formatted_lineitem_name ( $item, $receiver_type = '*' )
	{
		$html = '<span class="cart-product-name">'.$item->orderitem_name.'</span><br />';
		if ( isset( $item->orderitemattributes ) ) {
			$html .= '<span class="cart-item-options">';
			foreach ( $item->orderitemattributes as $attribute ) {
				$attribute_value = '';
				if ( $attribute->orderitemattribute_type == 'file' ) {
					unset( $table );
					$table = F0FTable::getInstance ( 'Upload', 'J2StoreTable' )->getClone ();
					if ( $table->load ( array( 'mangled_name' => $attribute->orderitemattribute_value ) ) ) {
						$attribute_value = $table->original_name;
					}

				} else {
					$attribute_value = JText::_ ( $attribute->orderitemattribute_value );
				}
				$html .= $this->get_formatted_lineitem_attribute_value($attribute, $attribute_value);
				//$html .= '<small>'.JText::_( $attribute->orderitemattribute_name ).' : '.$attribute_value.'</small><br />';
				if(J2Store::platform()->isClient('administrator') && $receiver_type == 'admin' && $attribute->orderitemattribute_type=='file' && JFactory::getApplication()->input->getString('task')!='printOrder'){
					$html .= '<a target="_blank" class="btn btn-primary"';
					$url = JUri::base()."index.php?option=com_j2store&view=orders&task=download&ftoken=".$attribute->orderitemattribute_value;
					$html .= 'href="'.$url.'"';
					$html .= '<i class="icon icon-download"></i>';
					$html .= JText::_('J2STORE_DOWNLOAD');
					$html .= '</a>';
					$html .= '<br />';
				}
			}
			$html .= '</span>';
		}
		J2Store::plugin ()->event ( 'LineItemName', array($item,&$html,$receiver_type) );
		return $html;
	}

	public function get_formatted_lineitem_attribute_value($attribute, $attribute_value)
	{
        $utility = J2Store::utilities();
		$search = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 );

		$replace = array( 'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine' );

		$attribute_class = str_replace( $search, $replace, $attribute_value );
        $attribute_class = md5($attribute_class);
		$html = '<small>';
		$html .= '<span class="item-option item-option-name ' . $attribute_class . '">';
		$html .= JText::_( $attribute->orderitemattribute_name );
		$html .= ' : ';
		$html .= '<span class="item-option item-option-value ' . $attribute_class . '">' . nl2br($utility->text_sanitize($attribute_value)) . '</span>';
		$html .= '</span>';

		$html .= '</small>';
		$html .= '<br />';
		J2Store::plugin()->event( 'GetFormattedLineItemAttributeValue', array( $attribute, $attribute_value, &$html ) );

		return $html;

	}

	
	/**
	 * --------------------------------------------------
	 * Formatted totals 
	 * --------------------------------------------------
	 */
	
	/**
	 * Gets line item unit price (excl. or incl. tax). Used only for the cart page
	 * @param object $item Line item object
	 * @param string $including_tax
	 * @return float Unit price
	 */
	
	function get_formatted_lineitem_price($item, $including_tax=false) {
		$product_helper = J2Store::product();
		if($including_tax) {
			//including tax
			$price = $product_helper->get_price_including_tax(($item->orderitem_price + $item->orderitem_option_price), $item->orderitem_taxprofile_id);
		} else {
			$price =  $product_helper->get_price_excluding_tax(($item->orderitem_price + $item->orderitem_option_price), $item->orderitem_taxprofile_id);
			//$price = $item->orderitem_price + $item->orderitem_option_price;
		}
		J2Store::plugin()->event('LineItemPrice', array(&$price, $item));
		return $price;
	}
	
	/**
	 * Method to get the line item subtotal (item price * quantity)
	 * @param object $item
	 * @param string $including_tax
	 * @return float Line item subtotal
	 */
	
	function get_formatted_lineitem_total($item, $including_tax=false) {
        
        if($including_tax) {
            $total_price_html = $item->orderitem_finalprice_with_tax ;
        } else {
            $total_price_html = $item->orderitem_finalprice_without_tax ; 
        }
        //allow plugins to modify the output
        J2Store::plugin()->event('LineItemTotal', array($this, &$total_price_html, $item ));

        return $total_price_html;
    }
	
	function get_formatted_subtotal($including_tax = false, $items=array()) {
		
		if($including_tax) {
			$order_subtotal = $this->order_subtotal;
		}else {
			$order_subtotal = $this->order_subtotal_ex_tax;
		}
		//allow plugins to modify the output.
		J2Store::plugin()->event('GetFormattedSubTotal', array($this, &$order_subtotal, $items ));

		return $order_subtotal ;
	}

	function get_order_obj(){
	    return $this;
    }

	function get_formatted_grandtotal() {
	    $order_obj = $this->get_order_obj();
		J2Store::plugin()->event('GetFormattedGrandTotal', array(&$order_obj));
		return $this->order_total;
	}
	
	
	/**
	 * Method to get formatted fees. Including or excluding tax
	 * @param object $fee
	 * @param string $including_tax
	 * @return float fee amount
	 */
	
	function get_formatted_fees($fee, $including_tax = false) {
		if($including_tax) {
			return $fee->amount + $fee->tax;
		}else {
			return $fee->amount;
		}
	}
	
	
	public function get_formatted_discount($discount, $including_tax=false) {			
		if($including_tax) {
			$amount = $discount->discount_amount + $discount->discount_tax;
		}else {
			$amount = $discount->discount_amount;
		}
		return abs($amount) * -1;
	}
	
	
	/**
	 * Returns a formatted price for an order. This is different from the one called by the cart
	 * Since 3.2
	 * @param object $item
	 * @param boolean $including_tax
	 * @return number
	 */
	
	public function get_formatted_order_lineitem_price($item, $including_tax=false) {
		if($including_tax) {
			$price = $item->orderitem_finalprice_with_tax / $item->orderitem_quantity;
		}else {
			$price = $item->orderitem_finalprice_without_tax / $item->orderitem_quantity;
		}
		//allow plugins to modify
		J2Store::plugin()->event('GetFormattedOrderLineItemPrice', array(&$price, $item, $this));
		return $price;										
	}
	
	/**
	 * Get formatted totals for an order
	 * Since 3.2
	 * @return array Totals as an associative array
	 */	
	
	public function get_formatted_order_totals() {
		
		$total_rows = array();
		$currency = J2Store::currency();
		$params = J2Store::config();
		$items = $this->getItems();
		
		$taxes = $this->getOrderTaxrates();
		$shipping = $this->getOrderShippingRate();
		
		//subtotal
		$total_rows['subtotal'] = array(
			'label' => JText::_('J2STORE_CART_SUBTOTAL'),
			'value'	=> $currency->format($this->get_formatted_subtotal($params->get('checkout_price_display_options', 1), $items), $this->currency_code, $this->currency_value )
		);
		
		//shipping
		
		if(!empty($shipping->ordershipping_name)) {
			
			$total_rows['shipping'] = array(
					'label' => JText::_(stripslashes($shipping->ordershipping_name)),
					'value'	=> $currency->format($this->order_shipping, $this->currency_code, $this->currency_value)
			);
		}
		
		//shipping tax
		if($this->order_shipping_tax > 0) {
				
			$total_rows['shipping_tax'] = array(
					'label' => JText::_('J2STORE_ORDER_SHIPPING_TAX'),
					'value'	=> $currency->format($this->order_shipping_tax, $this->currency_code, $this->currency_value)
			);
		}
		
		//fees
		foreach ( $this->get_fees() as $fee ) {
			
			$total_rows['fee_'.F0FInflector::underscore($fee->name)] = array(
					'label' => JText::_($fee->name),
					'value'	=> $currency->format($this->get_formatted_fees($fee, $params->get('checkout_price_display_options', 1)), $this->currency_code, $this->currency_value)
			);
		}
		
		//surcharge @depreicated.

		if($this->order_surcharge > 0) {
		
			$total_rows['surcharge'] = array(
					'label' => JText::_('J2STORE_CART_SURCHARGE'),
					'value'	=> $currency->format($this->order_surcharge, $this->currency_code, $this->currency_value)
			);
		}
		
		//discount
		foreach($this->getOrderDiscounts() as $discount) {
			if($discount->discount_amount > 0 ) {
				$link = '';
				if($discount->discount_type == 'coupon') {
					$label = JText::sprintf('J2STORE_COUPON_TITLE', $discount->discount_title);
					$link = '<a class="j2store-remove remove-icon" href="javascript:void(0)" onClick="jQuery(\'#j2store-cart-form #j2store-cart-task\').val(\'removeCoupon\'); jQuery(\'#j2store-cart-form\').submit();" >X</a>';
				}elseif($discount->discount_type == 'voucher') {				
					$label = JText::sprintf('J2STORE_VOUCHER_TITLE', $discount->discount_title);
					$link = '<a class="j2store-remove remove-icon" href="javascript:void(0)" onClick="jQuery(\'#j2store-cart-form #j2store-cart-task\').val(\'removeVoucher\'); jQuery(\'#j2store-cart-form\').submit();" >X</a>';
				}else {
					$label = JText::sprintf('J2STORE_DISCOUNT_TITLE', $discount->discount_title);
				}
				$label .=J2Store::plugin()->eventWithHtml('AfterDisplayDiscountTitle', array($this, $discount));
				
				
				$value = $currency->format($this->get_formatted_discount($discount, $params->get('checkout_price_display_options', 1)), $this->currency_code, $this->currency_value);
				$value .= J2Store::plugin()->eventWithHtml('AfterDisplayDiscountAmount', array($this, $discount));
				
				$total_rows[$discount->discount_code.F0FInflector::underscore($discount->discount_title)] = array(
						'label' => $label,
						'link' => $link,					
						'value'	=> $value
				);
			}
		}
		
		//taxes
		if(isset($taxes) && count($taxes) ) {			
			$label = '';
			foreach($taxes as $key=> $tax) {
				$label = '';
				if($params->get('checkout_price_display_options', 1)) {
					$label .= JText::sprintf('J2STORE_CART_TAX_INCLUDED_TITLE', JText::_($tax->ordertax_title), floatval($tax->ordertax_percent).'%');
				}else{
					$label .= JText::sprintf('J2STORE_CART_TAX_EXCLUDED_TITLE', JText::_($tax->ordertax_title), floatval($tax->ordertax_percent).'%');
				}
				
				$value = $currency->format($tax->ordertax_amount, $this->currency_code, $this->currency_value);
				$total_rows['tax_'.F0FInflector::underscore($tax->ordertax_title).$key] = array(
						'label' => $label,
						'value'	=> $value
				);
				
			}
		}
		
		//refund
		if($this->order_refund){
			$total_rows['refund'] = array(
					'label' => JText::_('J2STORE_CART_REFUND'),
					'value' => $currency->format($this->order_refund,$this->currency_code,$this->currency_value)
			);
		}
		
		$total_rows['grandtotal'] = array(
				'label' => JText::_('J2STORE_CART_GRANDTOTAL'),
				'value'	=> $currency->format($this->get_formatted_grandtotal(), $this->currency_code, $this->currency_value)
		);
		
		//allow plugins to modify
		J2Store::plugin()->event('GetFormattedOrderTotals', array($this, &$total_rows));
		return $total_rows;
	}	
	
	/**
	 * Method to validate stock in an order. Called only before placing the order.
	 * @return boolean True if successful | False if a condition does not match
	 */
	
	public function validate_order_stock() {
	
		$product_helper = J2Store::product ();
		$utilities = J2Store::utilities();
	
		$items = $this->getItems();
		if(count($items) < 1) return true;
	
		$quantity_in_cart = $this->get_orderitem_stock($items);
		foreach ( $items as $item) {
			$result = J2Store::plugin ()->event ( 'ValidateOrderStock', array($item,$this) );
			if (!empty( $result ) )
			{
				if(in_array(false, $result, false)){
					return false;
				}else{
					return true;
				}
			}
			// check quantity restrictions
			if ($item->cartitem->quantity_restriction && J2Store::isPro()) {
				// get quantity restriction
				$product_helper->getQuantityRestriction ( $item->cartitem);
	
				$quantity = $quantity_in_cart [$item->variant_id];
				$min = $item->cartitem->min_sale_qty;
				$max = $item->cartitem->max_sale_qty;
	
				if ($max && $max > 0) {
					if ($quantity > $max) {
						JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_MAXIMUM_QUANTITY_REACHED", $item->orderitem_name, $utilities->stock_qty($max), $utilities->stock_qty($quantity) ) );
						return false;
					}
				}
				if ($min && $min > 0) {
					if ($quantity < $min) {
						JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_MINIMUM_QUANTITY_REQUIRED", $item->orderitem_name, $utilities->stock_qty($min), $utilities->stock_qty($quantity) ) );
						return false;
					}
				}
			}
	
			if ($product_helper->managing_stock ( $item->cartitem ) && $product_helper->backorders_allowed ( $item->cartitem ) == false) {
				$productQuantity = F0FTable::getInstance ( 'ProductQuantity', 'J2StoreTable' )->getClone ();
				$productQuantity->load ( array (
						'variant_id' => $item->variant_id
				) );
				$qty = $product_helper->get_stock_quantity($productQuantity);
				// no stock, right now?
				if ($qty < 1) {
					JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_STOCK_NOT_AVAILABLE", $item->orderitem_name) );
					return false;
				}
	
				// not enough stock ?
				if ($qty > 0 && $quantity_in_cart [$item->variant_id] > $qty) {
					JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_ITEM_STOCK_NOT_ENOUGH_STOCK", $item->orderitem_name, $utilities->stock_qty($qty) ) );
					return false;
				}
			}
		}
		return true;
	}
	
	public function get_orderitem_stock($items) {
		//sort by variant
		$quantities = array();
		foreach($items as $item) {
			if(!isset($quantities[$item->variant_id])) {
				$quantities[$item->variant_id] = 0;
			}
			$quantities[$item->variant_id] += $item->orderitem_quantity;
		}
		return $quantities;
	}
	
	/**
	 * Gets cross sells based on the items in the cart.
	 *
	 * @return array cross_sells (item ids)
	 */
	public function get_cross_sells() {
		$cross_sells = array();
		$in_cart = array();
		if ( sizeof( $this->getItems() ) > 0 ) {
			foreach ( $this->getItems() as $item ) {
				if ( $item->orderitem_quantity > 0 ) {
					$item_cross_sells = implode(',', $item->cartitem->cross_sells);
					if(count($item_cross_sells)) {
						$cross_sells = array_merge($item_cross_sells, $cross_sells );
						$in_cart[] = $item->product_id;
					}
				}
			}
		}
		$cross_sells = array_diff( $cross_sells, $in_cart );
		return $cross_sells;
	}
	
	
	//** Gateway utility methods

	public function prepare_line_items($tax_display_option='') {
		$this->reset_line_items();
		$calculated_total = 0;
		if(empty($tax_display_option)) {
			$params = J2Store::config();
			$tax_display_option = $params->get('checkout_price_display_options', 1);
		}	
		$items = $this->getItems();
		
		foreach($items as $item) {
			$line_item = $this->add_line_item(
					$item->orderitem_name, 
					$item->orderitem_quantity, 
					$this->get_line_item_subtotal($item, $tax_display_option),
					$this->get_line_item_tax($item),
					$this->get_line_item_sku($item),
					$this->get_line_item_options($item),
					$item
			);
			
			$calculated_total += $this->get_line_item_subtotal($item, $tax_display_option) * $item->orderitem_quantity;
			
			if ( ! $line_item ) {
				return false;
			}
		}
		//add fees as line item
		foreach($this->get_fees() as $fee) {
			$this->add_line_item(JText::_($fee->name), 1, $this->get_formatted_fees($fee, $tax_display_option) );
		}
		
		//add shipping as a line item too
		$handling_cost = $this->order_shipping + $this->order_shipping_tax + $this->order_surcharge;
		if($handling_cost) $this->add_line_item(JText::_('J2STORE_SHIPPING_AND_HANDLING'), 1, $handling_cost, 0, 'shipping');
		 
	}
	
	protected function add_line_item( $item_name, $quantity = 1, $amount = 0, $tax=0, $item_number = '', $options=array(), $item = array()) {
	
		if ( ! $item_name) {
			return false;
		}
		
		$line_item = array();
		$line_item['name'] = html_entity_decode( substr( $item_name, 0, 127 ), ENT_NOQUOTES, 'UTF-8' );
		$line_item['quantity'] = $quantity;
		$line_item['amount'] = $amount;
		$line_item['tax'] = $tax;
		$line_item['item_number'] = $item_number;
		$line_item['options'] = $options;
		if(!empty($item)){
			J2Store::plugin()->event('BeforeAddGatewayLineItems', array($item, &$line_item));
		}
		array_push($this->line_items, $line_item);
		return true;
	}
	
	public function get_line_items($tax_display_option='') {
		$this->prepare_line_items($tax_display_option);
		//allow plugins to modify
		J2Store::plugin()->event('GetGatewayLineItems', array($this));
		return $this->line_items;
	}
	
	public function reset_line_items() {
		$this->line_items = array();
	}
	
	public function get_line_item_sku($item) {
		
		if(empty($item->orderitem_sku)) {
			return $item->product_id;
		}
		return $item->orderitem_sku;
	}
	
	public function get_line_item_tax($item) {
		$tax = 0;
		if($item->orderitem_per_item_tax > 0) {
			$tax = $item->orderitem_per_item_tax;
		}
		return $tax;
	}
	
	public function get_line_item_options($item) {		
		$options=array();
		if (isset($item->orderitemattributes) && count($item->orderitemattributes)) {
			foreach ($item->orderitemattributes as $attribute) {
				$options[] = array(
						'name' => JText::_($attribute->orderitemattribute_name),
						'value'=> JText::_($attribute->orderitemattribute_value)
				);
		
			}
		}
		return $options;
	}
	
	public function get_line_item_subtotal( $item, $inc_tax = false) {

		if ( $inc_tax ) {
			$price = ( $item->orderitem_finalprice_with_tax ) / max( 1, $item->orderitem_quantity );
		} else {
			$price = ( $item->orderitem_finalprice_without_tax / $item->orderitem_quantity );
		}
		return $price;
	}
	
	public function get_total_discount($including_tax = false) {
		if($including_tax) {
			return $this->order_discount + $this->order_discount_tax; 
		}else {
			return $this->order_discount;
		}		
	}
	
	//admin
	
	public function saveAdminOrderBasic($data){
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$result = array();
		//get currency id, value and code and store it
		$currency = J2Store::currency();
		$config = J2Store::config();
		$created_date = isset($data['created_on']) && !empty($data['created_on'])  ? $data['created_on'] :  'now';
		$tz = JFactory::getConfig()->get('offset');
        $data['created_on'] = JFactory::getDate($created_date, $tz)->toSql(true);
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'];
		if(!isset($data['user_id']) || empty($data['user_id'])){
			$result =array('msg'  => JText::_('J2STORE_REQUIRED_USER') , 'msgType' => 'warning');
			return $result;
		}else{
			if(empty($this->j2store_order_id)){
				$this->customer_language = empty($this->customer_language) ? $lang->getTag() : $this->customer_language ;
				$this->order_state_id =  empty($this->order_state_id) ? $data['order_state_id'] : $this->order_state_id;
				$this->user_id = $data['user_id'];
				$user =  JFactory::getUser($this->user_id);
				$this->user_email =$user->email;
				$this->customer_group = implode(',', JAccess::getAuthorisedViewLevels($user->id, false));
					
				$this->currency_id = $currency->getId();
				$this->currency_code = $currency->getCode();
				$this->currency_value = $currency->getValue($currency->getCode());
				$this->is_including_tax = $config->get('config_including_tax', 0);
				$this->customer_note = $data['customer_note'];
				$this->created_on = $data['created_on'];
			}else{
				$this->is_update = true;
				$user =  JFactory::getUser($this->user_id);
				$this->user_email = $user->email;
				$this->bind($data);
			}

			//trigger on before save
            $order_obj = $this->get_order_obj();
			J2Store::plugin()->event('BeforeSaveOrder', array(&$order_obj));
			if($this->store()){
				if(!isset($this->order_id) || empty($this->order_id) || !isset($this->is_update) || $this->is_update != 1) {
					$this->order_id = time().$this->j2store_order_id;
					//generate invoice number
					$this->generateInvoiceNumber();
					//generate a unique hash
					$this->token = JApplicationHelper::getHash($this->order_id);
					//save again so that the unique order id is saved.
					$this->store();
				}
				if(isset($data['update_history']) && $data['update_history'] == 1){
					$note = JText::_('J2STORE_BACKEND_ORDER_CREATED_BY_ADMIN');
					$this->add_history($note);
				}
			}else{
				$result=array('msg' => JText::_('J2STORE_ORDER_SAVE_ERROR') ,'msgType' => 'warning');
			}
		}
		return $result;
	}
	//save order info
	public function saveAdminOrderInfo($data){
		$app = JFactory::getApplication();
		$address_type = $app->input->getString('address_type','billing');
		$params = J2Store::config();
		//if(!in_array($address_type ,array('billing','shipping'))) return  array('msg' => JText::_('J2STORE_ORDERINFO_ERROR_IN_SAVING') ,'msgType'=> 'warning' );
		$session = JFactory::getSession();
		$result = array('msg' => JText::_('J2STORE_ORDERINFO_SAVED_SUCCESSFULLY') ,'msgType'=> 'message' );
		$orderinfo = F0FTable::getInstance('Orderinfo', 'J2StoreTable');
		$orderinfo->load(array('order_id'=> $this->order_id));
		// check already exists
		$orderinfo->order_id = $this->order_id;
		
		if($app->input->getString('address','') == 'existing'){
			$address_id = $app->input->getInt('address_id');
			// get the address id
			if($address_id){
				$address   = F0FTable::getAnInstance('Address','J2StoreTable');
				$address->load($address_id);
				$address->country_name = F0FModel::getTmpInstance('Countries','J2StoreModel')->getItem($address->country_id)->country_name;
				$address->zone_name = F0FModel::getTmpInstance('Zones','J2StoreModel')->getItem($address->zone_id)->zone_name;
				$input = J2Store::platform()->fromObject($address);
				$values =array();
				$return = new JObject();
				foreach($input as $k =>$value){
					if($k == 'j2store_address_id'){
						$k = $address_type.'_'.'address_id';
					}else{
						$k = $address_type.'_'.$k;
					}
					$values[$k] = $value;
				}
				if(isset($data['save_shipping']) && $data['save_shipping']){
					$address_type_1 = 'shipping';
					foreach($input as $k =>$value){
						if($k == 'j2store_address_id'){
							$k = $address_type_1.'_'.'address_id';
						}else{
							$k = $address_type_1.'_'.$k;
						}
						$values[$k] = $value;
					}
				}
				$orderinfo->bind($values);
				if(!$orderinfo->store()){
					$result =array('msg' => JText::_('J2STORE_ORDERINFO_ERROR_IN_SAVING') ,'msgType'=>'warning');
				}
			}
		}else{
			$data['email'] = $this->user_email;
			$data['user_id'] = $this->user_id;
			
			$address   = F0FModel::getTmpInstance('Addresses','J2StoreModel');
			$address_id = $address->addAddress($address_type,$data);
			
			if($address_id){
				$address   = F0FTable::getAnInstance('Address','J2StoreTable');
				$address->load($address_id);
				
				$address->country_name = F0FModel::getTmpInstance('Countries','J2StoreModel')->getItem($address->country_id)->country_name;
				$address->zone_name = F0FModel::getTmpInstance('Zones','J2StoreModel')->getItem($address->zone_id)->zone_name;
				$input = J2Store::platform()->fromObject($address);
				$values =array();
				$return = new JObject();
				foreach($input as $k =>$value){
					if($k == 'j2store_address_id'){
						$k = $address_type.'_'.'address_id';
					}else{
						$k = $address_type.'_'.$k;
					}
					$values[$k] = $value;
				}				
				$orderinfo->bind($values);
				if(!$orderinfo->store()){
					$result =array('msg' => JText::_('J2STORE_ORDERINFO_ERROR_IN_SAVING') ,'msgType'=>'warning');
				}
			}
			
		}
	
		return $result;
	}
	
	public function getAdminTotals($taxes=FALSE) {
	
		//$this->reset();
	
		$this->order_discount = 0;		
		//set the order information
		//$this->setOrderInformation();
		
		$this->getAdminOrderProductTotals($taxes);
	
		// then calculate shipping total
		$this->getAdminOrderShippingTotals($taxes);
	

		// Trigger the fees API where developers can add fees or additional cost to order
		$this->getAdminOrderFeeTotals();
		//$this->getOrderFeeTotals();

		// discount
		$this->getOrderDiscountTotals();
		//$this->getOrderDiscountTotals();
		$this->saveAdminOrderDiscounts();
		// then calculate the tax
		$this->getAdminOrderTaxTotals();
		$this->saveAdminOrderFiles ();
		// this goes last, to be sure it gets the fully adjusted figures
		//	$this->calculateVendorTotals();
		// sum totals

		 $subtotal =
		$this->cart_contents_total
		+ $this->final_shipping//$this->order_shipping
		+ $this->final_shipping_tax//$this->order_shipping_tax
		+ $this->order_tax
		;

		$total = $subtotal+ $this->order_fees;
		//if surcharge is set add that as well
		if(isset($this->order_surcharge)) {
			$total = $total + $this->order_surcharge;
		}
	//	$this->order_subtotal = 0;
		//$this->order_subtotal_ex_tax = 0;
		// set object properties
		$this->order_total      = $total;
		
		$this->store();
		
		// We fire just a single plugin event here and pass the entire order object
		// so the plugins can override whatever they need to
        $order_obj = $this->get_order_obj();
		J2Store::plugin()->event("CalculateOrderTotals", array( &$order_obj ) );
	
	}
	
	/**
	 * Calculates the product total (aka subtotal)
	 * using the array of items in the order object
	 *
	 * @return unknown_type
	 */
	function getAdminOrderProductTotals($apply_taxes) {
		$app = JFactory::getApplication ();
		$params = J2Store::config ();
        $platform = J2Store::platform();
		$subtotal = 0.00;
		$subtotal_ex_tax = 0.00;
		$order_info = $this->getOrderInformation();
		$tax_model = F0FModel::getTmpInstance('Taxprofiles', 'J2StoreModel')->getClone();
		$tax_model->setBillingAddress ( $order_info->billing_country_id, $order_info->billing_zone_id, $order_info->billing_zip );
		$tax_model->setShippingAddress ( $order_info->shipping_country_id, $order_info->shipping_zone_id, $order_info->shipping_zip );
		/**
		 * Calculate subtotals for items.
		 * This is done first so that discount logic can use the values.
		*/
	
		foreach ( $this->getItems () as $item ) {
			
			// Prices
			$base_price = $item->orderitem_price + $item->orderitem_option_price;
			J2Store::plugin ()->event ( 'AfterCalculateBasePriceInProductTotal', array( &$item, $this, &$base_price) );
			$line_price = $base_price * $item->orderitem_quantity;
	
			$line_subtotal = 0;
			$line_subtotal_tax = 0;
	
			/**
			 * No tax to calculate
			 */
			if (! isset ( $item->orderitem_taxprofile_id ) || $item->orderitem_taxprofile_id < 1) {
	
				// Subtotal is the undiscounted price
				$this->subtotal += $line_price;
				$this->subtotal_ex_tax += $line_price;
					
				/**
				 * Prices include tax
				 *
				 * To prevent rounding issues we need to work with the inclusive price where possible
				 * otherwise we'll see errors such as when working with a 9.99 inc price, 20% VAT which would
				 * be 8.325 leading to totals being 1p off
				 *
				 * Pre tax coupons come off the price the customer thinks they are paying - tax is calculated
				 * afterwards.
				 *
				 * e.g. $100 bike with $10 coupon = customer pays $90 and tax worked backwards from that
				 */
			} elseif ($item->orderitem_taxprofile_id && $params->get ( 'config_including_tax', 0 )) {
	
				// Get base tax rates
	
				$shop_taxrates = $tax_model->getBaseTaxRates ( $line_price, $item->orderitem_taxprofile_id, 1 );
	
				// Get item tax rates
				$item_taxrates = $tax_model->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 1 );
	
				/**
				 * ADJUST TAX - Calculations when base tax is not equal to the item tax
				*/
				if ($item_taxrates->taxtotal !== $shop_taxrates->taxtotal) {
	
					// Work out a new base price without the shop's base tax
	
					// Now we have a new item price (excluding TAX)
					$line_subtotal = $line_price - $shop_taxrates->taxtotal;
	
					// Now add modified taxes
					$modified_taxrates = $tax_model->getTaxwithRates ( $line_subtotal, $item->orderitem_taxprofile_id, 0 );
					$line_subtotal_tax = $modified_taxrates->taxtotal;
	
					/**
					 * Regular tax calculation (customer inside base and the tax class is unmodified
					 */
				} else {
	
					// Calc tax normally
					$line_subtotal_tax = $item_taxrates->taxtotal;
					$line_subtotal = $line_price - $item_taxrates->taxtotal;
				}
					
			} else {
	
				/**
				 * Prices exclude tax
				 *
				 * This calculation is simpler - work with the base, untaxed price.
				 */
	
				$item_taxrates = $tax_model->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 0 );
				// Base tax for line before discount - we will store this in the order data
				$line_subtotal_tax = $item_taxrates->taxtotal;
				$line_subtotal = $line_price;
			}
	
			// Add to main subtotal
			$this->subtotal += $line_subtotal + $line_subtotal_tax;
			$this->subtotal_ex_tax += $line_subtotal;
		}
	
		/**
		 * Calculate actual totals for items
		 */
		foreach ( $this->getItems () as $hash => $item ) {
	
			// Prices
			$base_price = $item->orderitem_price + $item->orderitem_option_price;
			J2Store::plugin ()->event ( 'AfterCalculateBasePriceInProductTotal', array( &$item, $this, &$base_price) );
			$line_price = $base_price * $item->orderitem_quantity;
	
			// Tax data
			$taxes = array ();
			$discounted_taxes = array ();
			/**
			 * No tax to calculate
			*/
			if (! isset ( $item->orderitem_taxprofile_id ) || $item->orderitem_taxprofile_id < 1) {
	
				// Discounted Price (price with any pre-tax discounts applied)
				$discounted_price = $this->get_discounted_price ( $item, $base_price, true );
				$discounted_tax_amount = 0;
				$tax_amount = 0;
				$line_subtotal_tax = 0;
				$line_subtotal = $line_price;
				$line_tax = 0;
				$line_total = $discounted_price * $item->orderitem_quantity;
				/**
				 * Prices include tax
				 */
			} elseif ($item->orderitem_taxprofile_id && $params->get ( 'config_including_tax', 0 )) {
	
				// Get base tax rates
	
				$shop_taxrates = $tax_model->getBaseTaxRates ( $line_price, $item->orderitem_taxprofile_id, 1 );
	
				// Get item tax rates
				$item_taxrates = $tax_model->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 1 );
	
				/**
				 * ADJUST TAX - Calculations when base tax is not equal to the item tax
				*/
				if ($item_taxrates->taxtotal !== $shop_taxrates->taxtotal) {
	
					// Work out a new base price without the shop's base tax
	
					// Now we have a new item price (excluding TAX)
					$line_subtotal = $line_price - $shop_taxrates->taxtotal;
	
					// Now add modified taxes
					$modified_taxrates = $tax_model->getTaxwithRates ( $line_subtotal, $item->orderitem_taxprofile_id, 0 );
					$line_subtotal_tax = $modified_taxrates->taxtotal;
	
					// Adjusted price (this is the price including the new tax rate)
					$adjusted_price = ($line_subtotal + $line_subtotal_tax) / $item->orderitem_quantity;
	
					// Apply discounts
					$discounted_price = $this->get_discounted_price ( $item, $adjusted_price, true );
					$discounted_taxes = $tax_model->getTaxwithRates ( $discounted_price * $item->orderitem_quantity, $item->orderitem_taxprofile_id, 1 );
					$line_tax = $discounted_taxes->taxtotal;
					$line_total = ($discounted_price * $item->orderitem_quantity) - $line_tax;
	
					/**
					 * Regular tax calculation (customer inside base and the tax class is unmodified
					 */
				} else {
	
					// Work out a new base price without the item tax
					// Now we have a new item price (excluding TAX)
					$line_subtotal = $line_price - $item_taxrates->taxtotal;
					$line_subtotal_tax = $item_taxrates->taxtotal;
	
					// Calc prices and tax (discounted)
					$discounted_price = $this->get_discounted_price ( $item, $base_price, true );
					$discounted_taxes = $tax_model->getTaxwithRates ( $discounted_price * $item->orderitem_quantity, $item->orderitem_taxprofile_id, 1 );
					$line_tax = $discounted_taxes->taxtotal;
					$line_total = ($discounted_price * $item->orderitem_quantity) - $line_tax;
				}

				foreach ( $discounted_taxes->taxes as $taxrate_id => $tax_rate ) {
					if (! isset ( $this->_taxrates [$taxrate_id] )) {
						$this->_taxrates [$taxrate_id] ['name'] = $tax_rate ['name'];
						$this->_taxrates [$taxrate_id] ['rate'] = $tax_rate ['rate'];
						$this->_taxrates [$taxrate_id] ['total'] = ($tax_rate ['amount']);
					} else {
						$this->_taxrates [$taxrate_id] ['name'] = $tax_rate ['name'];
						$this->_taxrates [$taxrate_id] ['rate'] = $tax_rate ['rate'];
						$this->_taxrates [$taxrate_id] ['total'] += ($tax_rate ['amount']);
					}
				}
				$item->orderitem_per_item_tax = $discounted_taxes->taxtotal / $item->orderitem_quantity;
					
				/**
				 * Prices exclude tax
				 */
			} else {
	
	
				// Work out a new base price without the shop's base tax
				$item_taxrates = $tax_model->getTaxwithRates ( $line_price, $item->orderitem_taxprofile_id, 0 );
	
				// Now we have the item price (excluding TAX)
				$line_subtotal = $line_price;
				$line_subtotal_tax = $item_taxrates->taxtotal;
	
				// Now calc product rates
				$discounted_price = $this->get_discounted_price ( $item, $base_price, true );
	
				$discounted_taxes = $tax_model->getTaxwithRates ( $discounted_price * $item->orderitem_quantity, $item->orderitem_taxprofile_id, 0 );
				$discounted_tax_amount = $discounted_taxes->taxtotal;
				$line_tax = $discounted_tax_amount;
				$line_total = $discounted_price * $item->orderitem_quantity;
				// Tax rows - merge the totals we just got
				foreach ( $discounted_taxes->taxes as $taxrate_id => $tax_rate ) {
					if (! isset ( $this->_taxrates [$taxrate_id] )) {
						$this->_taxrates [$taxrate_id] ['name'] = $tax_rate ['name'];
						$this->_taxrates [$taxrate_id] ['rate'] = $tax_rate ['rate'];
						$this->_taxrates [$taxrate_id] ['total'] = ($tax_rate ['amount']);
					} else {
						$this->_taxrates [$taxrate_id] ['name'] = $tax_rate ['name'];
						$this->_taxrates [$taxrate_id] ['rate'] = $tax_rate ['rate'];
						$this->_taxrates [$taxrate_id] ['total'] += ($tax_rate ['amount']);
					}
				}
				$item->orderitem_per_item_tax = $discounted_taxes->taxtotal / $item->orderitem_quantity;
			}
	
			// Cart contents total is based on discounted prices and is used for the final total calculation
			$this->cart_contents_total += $line_total;
			/* 	var_dump ( $line_total );
			 var_dump ( $line_tax );
			var_dump ( $line_subtotal );
			var_dump ( $line_subtotal_tax ); */
			// Store costs + taxes for lines
			$this->cart_contents [$hash] ['line_total'] = $line_total;
			$this->cart_contents [$hash] ['line_tax'] = $line_tax;
			$this->cart_contents [$hash] ['line_subtotal'] = $line_subtotal;
			$this->cart_contents [$hash] ['line_subtotal_tax'] = $line_subtotal_tax;
	
			// Store rates ID and costs - Since 2.2
			$this->cart_contents [$hash] ['line_tax_data'] = array (
					'total' => $discounted_taxes,
					'subtotal' => $taxes
			);
	
			$item->orderitem_finalprice = $line_subtotal + $line_subtotal_tax;
			$item->orderitem_finalprice_with_tax = $line_subtotal + $line_subtotal_tax;
			$item->orderitem_finalprice_without_tax = $line_subtotal;
			$item->orderitem_tax = $line_tax;
			// admin order item save
			if($platform->isClient('administrator') && F0FPlatform::getInstance()->isBackend()) {
				$item->store();
			}
				
		}
		// vat exempted customer ? remove the taxes
		$customer = F0FTable::getAnInstance ( 'Customer', 'J2StoreTable' );
		if ($customer->is_vat_exempt ()) {
			$this->removeOrderTaxes ();
		}
		// set object properties
		$this->order_subtotal = !empty($this->subtotal)?$this->subtotal:0;
		$this->order_subtotal_ex_tax = !empty($this->subtotal_ex_tax) ? $this->subtotal_ex_tax:0;
	
		if($platform->isClient('administrator') && F0FPlatform::getInstance()->isBackend()) {
			$ordertaxes = F0FModel::getTmpInstance('OrderTaxes', 'J2StoreModel')->order_id($this->order_id)->getList();
			
			if(isset($this->_taxrates) && $apply_taxes) {
				$this->_ordertaxes = array();
				foreach ($ordertaxes as $order_tax){
					$ordertaxeTable = F0FTable::getAnInstance('Ordertax', 'J2StoreTable')->getClone();
					$ordertaxeTable->load($order_tax->j2store_ordertax_id);
					$ordertaxeTable->delete();
				}
				
				foreach($this->_taxrates as $tax) {
					$ordertax = F0FTable::getAnInstance('Ordertax', 'J2StoreTable')->getClone();
					$ordertax->ordertax_title = $tax['name'];
					$ordertax->ordertax_percent = $tax['rate'];
					$ordertax->ordertax_amount = $tax['total'];
					$ordertax->order_id = $this->order_id;
					$ordertax->store();
					$this->_ordertaxes[] = $ordertax;
				}
			}else{
				$this->_ordertaxes = $ordertaxes;
			}
		}
		//allow plugins to modify the output.
		J2Store::plugin ()->event ( "CalculateProductTotals", array (
		$this
		) );
	}
	
	public function getAdminOrderShippingTotals($taxes){
		$session = JFactory::getSession();				
		$shipping_values = $session->get('shipping_values', array(), 'j2store');		
		$session->clear('shipping_values', 'j2store');
		$config = J2Store::config();
        $voucher_apply_to_shipping = $config->get('backend_voucher_to_shipping',1);
		if(isset($shipping_values['shipping_name'])) {			
			$this->setAdminOrderShippingRate($shipping_values);
			$this->order_shipping = $this->_shipping_totals->ordershipping_price + $this->_shipping_totals->ordershipping_extra;
            $this->order_shipping_tax  = $this->_shipping_totals->ordershipping_tax;

			$this->final_shipping_tax = 0;
            $this->final_shipping = 0;
            if($voucher_apply_to_shipping){
                $this->final_shipping_tax = $this->get_admin_discounted_price($this->order_shipping_tax);
                $this->final_shipping = $this->get_admin_discounted_price($this->order_shipping);
            }else{
                $this->final_shipping_tax = $this->order_shipping_tax;
                $this->final_shipping = $this->order_shipping;
            }

		}else{
			$ordershipping_table = F0FTable::getAnInstance('Ordershipping', 'J2StoreTable');
			$ordershipping_table->load(array(
				'order_id'=>$this->order_id
			));
			$this->order_shipping = $ordershipping_table->ordershipping_price + $ordershipping_table->ordershipping_extra;
			$this->final_shipping_tax = 0;
			$this->final_shipping = 0;
			if($voucher_apply_to_shipping){
				$this->final_shipping_tax = $this->get_admin_discounted_price($ordershipping_table->ordershipping_tax);
				$this->final_shipping = $this->get_admin_discounted_price($this->order_shipping);
			}else{
                $this->final_shipping_tax = $ordershipping_table->ordershipping_tax;
                $this->final_shipping = $this->order_shipping;
            }

		}



	}

	/**
	 * Function to apply discounts to a product and get the discounted price (before tax is applied).
	 *
	 * @param mixed $values
	 * @param mixed $price
	 * @param bool $add_totals (default: false)
	 * @return float price
	 */
	public function get_admin_discounted_price($price, $add_totals = false) {
		if (! $price) {
			return $price;
		}

		$app = JFactory::getApplication ();
		$voucher_model = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
		if($voucher_model->has_voucher()) {
			// Because of one moment of stupidity we now have to do a separate calculation for vouchers as well. A brilliant way of implementing this would be via coupons.
			// TODO: Merge vouchers with coupons in future. Both share similar characteristics
			//$voucher_model = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
            $init_status = $voucher_model->init ();
            if ( $app->isClient('administrator') ) {
                $voucher_status = $voucher_model->is_admin_valid ( $this );
            } else {
                $voucher_status = $voucher_model->is_valid ( $this );
            }

            if ( $init_status && $voucher_status ) {
				$discount_amount = $voucher_model->get_admin_discount_amount ( $price );

				//sanity check
				$discount_amount = min( $price, $discount_amount );
				$price = max ( $price - $discount_amount, 0 );
				$this->increase_coupon_discount_amount ( $voucher_model->get_voucher(), $discount_amount, 0 );
			}
		}
		return $price;
	}
	
	function getAdminOrderTaxTotals() {
		/*  */
		$items = $this->getItems();
		foreach($items as $item) {
			if($item->orderitem_taxprofile_id) {
				$tax = 0;
				$tax = $item->orderitem_per_item_tax * $item->orderitem_quantity;
				$tax  = isset($item->orderitem_discount_tax) ?  $tax + $item->orderitem_discount_tax : $tax;
				$item->orderitem_tax = $tax;
				//we need to re-set this because the discount tax alters the tax totals.
				$item->orderitem_finalprice_with_tax = ($item->orderitem_finalprice +$item->orderitem_tax);
			}
		}

		$taxtotal = 0;
		$this->_ordertaxes = array();
		if(isset($this->_taxrates) && count($this->_taxrates)) {
			$this->removeOrderTaxesRows($this->order_id);
			foreach($this->_taxrates as $_taxrate) {
				$taxtotal += $_taxrate['total'];
				$ordertax = F0FTable::getAnInstance ( 'Ordertax', 'J2StoreTable' )->getClone ();
				$ordertax->order_id = $this->order_id;
				$ordertax->ordertax_title = $_taxrate[ 'name' ];
				$ordertax->ordertax_percent = $_taxrate[ 'rate' ];
				$ordertax->ordertax_amount = $_taxrate[ 'total' ];
				$ordertax->store();
				$this->_ordertaxes[] = $ordertax;
			}
		}
		$this->order_tax = $taxtotal;

		/*$taxtotal = 0;
		if(isset($this->_ordertaxes) && count($this->_ordertaxes)) {
			foreach($this->_ordertaxes as $ordertax) {
				$taxtotal += $ordertax->ordertax_amount;
			}
		}
		$this->order_tax = $taxtotal;*/

		//J2Store::plugin()->event("CalculateTaxTotals", array( $this) );

	}

	/**
	 * Remove / Delete order taxes
	 * */
	protected function removeOrderTaxesRows($order_id){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->delete('#__j2store_ordertaxes');
		$query->where('order_id = '.$db->q($order_id));
		try {
			return $db->setQuery($query)->execute();
		}catch (Exception $e) {
			return false;
			//do nothing. Because this is not harmful even if it fails.
		}
	}

	public function setAdminOrderShippingRate( $values)
	{
	
		$ordershipping_table = F0FTable::getAnInstance('Ordershipping', 'J2StoreTable');
		$ordershipping_table->load(array(
				'order_id'=>$this->order_id
		));
		$ordershipping_table->ordershipping_price      = $values['shipping_price'];
		$ordershipping_table->ordershipping_extra      = $values['shipping_extra'];
		$ordershipping_table->ordershipping_tax        = $values['shipping_tax'];
		$ordershipping_table->ordershipping_code       = $values['shipping_code'];
		$ordershipping_table->ordershipping_name       = $values['shipping_name'];
		$ordershipping_table->ordershipping_type	   = $values['shipping_plugin'];
		$ordershipping_table->ordershipping_total	   = $values['shipping_price']+$values['shipping_extra']+$values['shipping_tax'];
		$ordershipping_table->order_id = $this->order_id;
		$ordershipping_table->store();
		$this->_shipping_totals = $ordershipping_table;
	}
	function saveAdminOrderDiscounts() {
		if(isset($this->_orderdiscounts) && count($this->_orderdiscounts)) {
			foreach ($this->_orderdiscounts as $discount) {
				//$discount = F0FTable::getAnInstance('Ordershipping', 'J2StoreTable');
	
				$discount->order_id = $this->order_id;
				if(!isset($discount->discount_customer_email)) {
					$discount->discount_customer_email = $this->user_email;
				}
				$discount->user_id = $this->user_id;
				if($discount->discount_amount > 0){
					$discount_table = F0FTable::getInstance('Orderdiscount', 'J2StoreTable')->getClone();
					$discount_table->load(array(
							'order_id' => $this->order_id,
							'discount_type' => $discount->discount_type
					));
					$discount_table->bind($discount);
					$discount_table->store();
				}
	
			}
		}
	}


	function saveAdminOrderFiles() {

		$db = JFactory::getDbo();
		$items = $this->getItems();
		foreach($items as $item) {
			//get the list of files based on
			if($item->product_type == 'downloadable') {
				//print_r ( $item );exit;
				unset($orderdownloads);
				$orderdownloads = F0FTable::getAnInstance('Orderdownload', 'J2StoreTable')->getClone();
				$orderdownloads->load(
					array(
						'order_id' => $this->order_id,
						'product_id' => $item->product_id,
						'user_email' => $this->user_email
					)
				);
				$orderdownloads->order_id = $this->order_id;
				$orderdownloads->product_id = $item->product_id;
				$orderdownloads->user_id = $this->user_id;
				$orderdownloads->user_email = $this->user_email;
				$orderdownloads->access_granted == $db->getNullDate();
				$orderdownloads->access_expires == $db->getNullDate();
				$orderdownloads->store();
				F0FModel::getTmpInstance('Orderdownloads', 'J2StoreModel')->setDownloads($this, true);
			}
		}
	}

	/**
	 * Reset Same Order object
	 * */
	function resetSameOrder(){
		$this->_taxrates = array();
		$this->_shop_taxrates = array();
		$this->_taxrate_amounts = array();
		$this->_taxclasses = array();
		$this->_taxclass_amounts = array();
		$this->_ordertaxes = array();
		$this->_coupons = array();
		$this->_ordercoupons = array();
		$this->_ordervouchers = array();
		$this->_orderinfo = null;
		$this->_orderdownloads = array();
		$this->_orderhistory = array();
		$this->_orderdiscounts = array();
		$this->fees = null;
		$this->subtotal = 0;
		$this->subtotal_ex_tax = 0;
		$this->tax_total = 0;
		$this->taxes = 0;
		$this->shipping_taxes = 0;
		$this->discount_cart = 0;
		$this->discount_cart_tax = 0;
		$this->free_shipping = false;
		$this->cart_contents_total = 0;
		$this->cart_contents = array();
		$this->order_total = 0;
		$this->coupon_discount_amounts = array();
		$this->coupon_discount_tax_amounts = array();
	}
}
