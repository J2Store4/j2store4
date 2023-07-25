<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
class JFormFieldCouponExpireText extends F0FFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Couponexpiretext';

	public function getRepeatable()
	{
		$html = array();
		$diff = $this->getExpiryDate($this->item->valid_from,$this->item->valid_to);
		$style='style="padding:5px"';
		if($diff->format("%R%a")==0)
		{
			$text = JText::sprintf('COM_J2STORE_COUPON_WILL_EXPIRE_TODAY',$diff->format("%a").' day (s) ');
			$html ='<label class="label label-info" '.$style.'>'.$text.'</label>';
		}elseif($diff->format("%R%a")<=0)
		{
			$text = JText::sprintf('COM_J2STORE_COUPON_EXPIRED_BEFORE_DAYS',$diff->format("%a").' day (s) ');
			$html ='<label class="label label-warning" '.$style.'>'.$text.'</label>';
		}else{
			$text = JText::sprintf('COM_J2STORE_COUPON_WILL_EXPIRE_WITH_DAYS',$diff->format("%a").' day (s) ');
			$html ='<label class="label label-success" '.$style.'>'.$text.'</label>';
		}
		return $html;
	}


	public function getExpiryDate($valid_from,$valid_to)
	{
		$start=date("Y-m-d");
		$today=date_create($start);
		//assing the coupon offer start date
		// Assing the coupon valid date
		$date2=date_create($valid_to);
		return date_diff($today,$date2);
	}
}
