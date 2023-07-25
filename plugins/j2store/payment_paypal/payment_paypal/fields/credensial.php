<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php';
class JFormFieldCredensial extends JFormFieldList {

    protected $type = 'credensial';

    public function getInput() {
        //
        $cron_key = J2Store::config ()->get ( 'queue_key','' );
        $mode = implode(',',(array)$this->element['mode']);
        $url = trim(JURI::root(),'/').'/index.php?option=com_j2store&view=crons&command=paypal_api_check&cron_secret='.$cron_key.'&mode='.$mode;
        $html = "<a id='check_credensial_".$mode."' onclick='checkPaypalCredensial".$mode."()' class='btn btn-primary'>".JText::_('J2STORE_PAYPAL_CREDENTIALS_CHECK')."</a>";
        $html .= "<script>function checkPaypalCredensial".$mode."(){
        (function($) {
            var mode = '".$mode."';
            // execute Ajax request to server
		$.ajax({
			url : '".$url."',
			type : 'get',
			 cache: false,
           
             dataType: 'json',
             beforeSend: function() {
             $('#check_credensial_'+mode).attr('disabled',true);
               	 $('.paypal_error').remove();
                   },
             complete: function() {
            	 $('.wait').remove();
             },
			// data:{\"elements\":Json.toString(str)},
             success: function(json) {
             $('#check_credensial_'+mode).attr('disabled',false);
                if(json['error']){
                    $('#check_credensial_'+mode).after('<span class=\'paypal_error text-error\'><strong>'+json['error']+'</strong></span>');
                }
                if(json['success']){
                $('#check_credensial_'+mode).after('<span class=\'paypal_error text-success\'><strong>All Ok</strong></span>');
                }
            	console.log(json);	
				return true;
			}
		});
        })(jQuery);
}</script>";
        return $html;
    }
}
