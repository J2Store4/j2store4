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
class JFormFieldTlschecks extends JFormFieldList {

    protected $type = 'tlschecks';

    public function getInput() {
        //
        $cron_key = J2Store::config ()->get ( 'queue_key','' );
        $url = trim(JURI::root(),'/').'/index.php?option=com_j2store&view=crons&command=paypal_tls_check&cron_secret='.$cron_key;
        $html = "<a id='check_credensial' onclick='checkTLS()' class='btn btn-primary'>".JText::_('J2STORE_PAYPAL_TLS_CREDENTIALS_CHECK')."</a>";
        $html .= "<script>function checkTLS(){
        (function($) {
            // execute Ajax request to server
		$.ajax({
			url : '".$url."',
			type : 'get',
			 cache: false,
           
             dataType: 'json',
             beforeSend: function() {
             $('#check_credensial').attr('disabled',true);
               	 $('.paypal_error').remove();
                   },
             complete: function() {
            	 $('.wait').remove();
             },
             success: function(json) {
             $('#check_credensial').attr('disabled',false);
                if(json['error']){
                    $('#check_credensial').after('<span class=\'paypal_error text-error\'><strong>'+json['error']+'</strong></span>');
                }
                if(json['success']){
                $('#check_credensial').after('<span class=\'paypal_error text-success\'><strong>All Ok</strong></span>');
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
