<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
class JFormFieldPrint extends F0FFormFieldText
{	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Print';

	public function getRepeatable()
	{

		$html ='';
		$html .='<script type="text/javascript">';
		$html .="function j2storeOpenModal(url){";

		if(JBrowser::getInstance()->getBrowser() == "msie") {
				$html .='var options = {size:{x:document.documentElement.­clientWidth-80, y: document.documentElement.­clientHeight-80}};';

		}else{
				$html .="var options = {size:{x: window.innerWidth-80, y: window.innerHeight-80}};{
					SqueezeBox.initialize();
					SqueezeBox.setOptions(options);
					SqueezeBox.setContent('iframe',url);";
		}
		$html .='</script>';


		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/browser.php');
		$url = J2Store::platform()->getMyprofileUrl(array('task' => 'printOrder','layout' => 'order','tmpl' => 'component','order_id' => $this->item->order_id));
		 if(JBrowser::getInstance()->getBrowser() == 'msie'){
			$html .='<a class="btn btn-primary btn-small" href="'.$url.'" target="_blank">';
			$html .=JText::_( "J2STORE_PRINT_INVOICE" );'test';
			$html .='</a>';
		 }else{
			$html .='<a  onclick="j2storeOpenModal('.stripslashes($url).')">';
			$html .=JText::_( "J2STORE_PRINT_INVOICE" );
			$html .='</a>';
		  }

		 return $html;

	}
}






