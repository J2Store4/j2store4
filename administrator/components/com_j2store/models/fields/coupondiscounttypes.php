<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
/**
 * Class used for showing label core / not core
 * @author weblogicx
 *
 */
class JFormFieldCouponDiscountTypes extends F0FFormFieldList {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	protected $type = 'CouponDiscountTypes';
	public function getInput() {
		$model = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' );
		$list = $model->getCouponDiscountTypes ();
		$attr = array ();
		// Get the field options.
		// Initialize some field attributes.
		$attr ['class'] = ! empty ( $this->class ) ? $this->class : '';
		// Initialize JavaScript field attributes.
		$attr ['onchange'] = isset ( $this->onchange ) ? $this->onchange : '';
		$attr ['id'] = isset ( $this->id ) ? $this->id : '';
		
		// generate country filter list
		return J2Html::select ()->clearState ()->type ( 'genericlist' )->name ( $this->name )->attribs ( $attr )->value ( $this->value )->setPlaceHolders ( $list )->getHtml ();
	}
}
