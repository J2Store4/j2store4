<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
class JFormFieldcronlasthit extends JFormField
{
	protected $type = 'cronlasthit';

	public function getInput() {
		$cron_hit = J2Store::config ()->get('cron_last_trigger','');

		if(empty( $cron_hit )){
			$note = JText::_('J2STORE_STORE_CRON_LAST_TRIGGER_NOT_FOUND');
		}elseif(J2Store::utilities ()->isJson ( $cron_hit )){
			$cron_hit = json_decode ( $cron_hit );
			$date =  isset( $cron_hit->date ) ? $cron_hit->date: '';
			$url = isset( $cron_hit->url ) ? $cron_hit->url:'';
			$ip = isset( $cron_hit->ip ) ? $cron_hit->ip:'';
			$note = JText::sprintf('J2STORE_STORE_CRON_LAST_TRIGGER_DETAILS',$date,$url,$ip);
		}

		$html = '';
		$html .= '<strong>'.$note.'</strong>';
		return  $html;
	}
}