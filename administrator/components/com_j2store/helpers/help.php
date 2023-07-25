<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * J2Store help texts and videos.
 */

class J2Help {

	public static $instance = null;
	
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
	
	public function watch_video_tutorials() {
		$video_url = J2Store::buildHelpLink('support/video-tutorials.html', 'dashboard');
		$html = '<div class="video-tutorial panel panel-solid-info">
				<p class="panel-body">'.JText::_('J2STORE_VIDEO_TUTORIALS_HELP_TEXT').'
						 				<a class="btn btn-success" target="_blank" href="'.$video_url.'">
						 					'.JText::_('J2STORE_WATCH').'</a>
						 			</p>
						 		</div>';
		return $html;
	}

	public function free_topbar() {
		$html = '';
		if ( J2Store::isPro() ) {
			return $html;
		}
		$free_topbar_url = J2Store::buildHelpLink('/j2store-pro-features.html', 'dashboard');
		$html = '<div class="video-tutorial free-topbar panel panel-solid-info">
				<p class="panel-body">'.JText::_('J2STORE_FREE_TOPBAR_HELP_TEXT').'
						 				<a class="btn btn-success" target="_blank" href="'.$free_topbar_url.'">
						 					'.JText::_('J2STORE_UPGRADE_PRO').'</a>
						 			</p>
						 		</div>';
		return $html;
	}
	
	
	public function alert($type, $title, $message) {
		
		$html = '';
		
		//check if this alert to be shown.
		$params = J2Store::config();
		if($params->get($type, 0)) return $html;
        if(version_compare(JVERSION,'3.99.99','lt')){
            $class = 'alert alert-block';
        }else{
            $class = 'alert alert-info';
        }
		//message not hidden
		$url = JRoute::_ ( 'index.php?option=com_j2store&view=cpanels&task=notifications&message_type=' . $type . '&' . JSession::getFormToken () . '=1' );		
		$html .= '<div class="user-notifications ' . $type . ' '.$class.'">';
		$html .= '<h3>' . $title . '</h3>';
		$html .= '<p>' . $message . '</p>';
		$html .= '<br />';
		$html .= '<a class="btn btn-danger" href="' . $url . '">' . JText::_ ( 'J2STORE_GOT_IT_AND_HIDE' ) . '</a>';
		$html .= '</div>';
		return $html;
	}

	public function alert_with_static_message($type, $title, $message) {

		$html = '';
		//message not hidden
		$html .= '<div class="user-notifications alert alert-info alert-' . $type . ' ">';
		$html .= '<h3>' . $title . '</h3>';
		$html .= '<p><strong>' . $message . '</strong></p>';
		$html .= '<br />';
		$html .= '</div>';
		return $html;
	}
	
}	