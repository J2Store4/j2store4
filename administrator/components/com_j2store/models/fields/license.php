<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
Joomla\CMS\HTML\HTMLHelper::_('jquery.framework');

class JFormFieldLicense extends \Joomla\CMS\Form\FormField
{
    protected $type = 'License';

    protected function getInput()
    {
        $license_value = is_array($this->value) && isset($this->value['license']) && !empty($this->value['license']) ? $this->value['license'] : '';
        $status = is_array($this->value) && isset($this->value['status']) && !empty($this->value['status']) ? $this->value['status'] : 'in_active';
        $expire = is_array($this->value) && isset($this->value['expire']) && !empty($this->value['expire']) ? $this->value['expire'] : '';
        $html = '<input id="plugin_license_key" style="width:60%;appearance: none;background-clip: padding-box;
        border: 1px solid var(--template-bg-dark-20);border-radius: .25rem;color: #212529;font-size: 1rem;font-weight: 400;
        line-height: 1.5;padding: .5rem 1rem;" name="' . $this->name . '[license]" value="' . $license_value . '">';
        $html .= '<input id="plugin_license_status" type="hidden" name="' . $this->name . '[status]" value="' . $status . '">';
        $html .= '<input id="plugin_license_expire" type="hidden" name="' . $this->name . '[expire]" value="' . $expire . '">';
        $extension_id = J2Store::platform()->application()->input->get('extension_id', 0);
        $plugin = $this->getPluginData($extension_id);
        $force = false;
        if ($status == 'active' && !empty($license_value)) {
            $now = strtotime('now');
            $expire_time = strtotime($expire);
            if ($now > $expire_time) {
                $force = true;
            }
        }
        if (($status != 'active' && !empty($license_value)) || $force) {
            $html .= "<a id='activate_license' onclick='activateLicense()' class='btn btn-success' >" . JText::_('J2STORE_ACTIVATE') . "</a>";
            $html .= '<script>
        function activateLicense(){
            let license = jQuery("#plugin_license_key").val();
            let status = jQuery("#plugin_license_status").val();
            let expire = jQuery("#plugin_license_expire").val();
            let extension_id = "' . $extension_id . '"; 
            let group = "' . $plugin->folder . '";
            
            $.ajax({
			    type : \'post\',
			    url :  j2storeURL+\'index.php?option=com_ajax&format=json&group=\'+group+\'&plugin=activateLicence\',
			    data : \'license=\' + license+\'&status=\'+status+\'&expire=\'+expire+\'&id=\'+extension_id,
			    dataType : \'json\',
			    success : function(data) {
				    if(data.success == false) {
					    jQuery("#plugin_license_key").after(\'<span class="j2error">\'+data.message+\'</span>\')
    			    }else {
                        jQuery("#plugin_license_status").val("active");
                        jQuery("#plugin_license_expire").val(data.response.expires);
                        jQuery("#plugin_license_key").after(\'<span class="j2success">\'+data.message+\'</span>\')
                        jQuery(\'input[name="task"]\').val("plugin.apply");
                        setTimeout(function (){
                            jQuery("#plugin_license_key").closest("form").submit();
                        },1000)
    			    }
                }
            });
        }</script>';
        } elseif ($status == 'active') {
            $html .= "<a id='de_activate_license' class='btn btn-danger' onclick='deActivateLicense()' >" . JText::_('J2STORE_DEACTIVATE') . "</a>";
            $html .= '<script>
        function deActivateLicense(){
            let license = jQuery("#plugin_license_key").val();
            let extension_id = "' . $extension_id . '"; 
            let group = "' . $plugin->folder . '";
            
            $.ajax({
			    type : \'post\',
			    url :  j2storeURL+\'index.php?option=com_ajax&format=json&group=\'+group+\'&plugin=deActivateLicence\',
			    data : \'license=\' + license+\'&id=\'+extension_id,
			    dataType : \'json\',
			    success : function(data) {
				    if(data.success == false) {
					    jQuery("#plugin_license_key").after(\'<span class="j2error">\'+data.message+\'</span>\')
    			    }else {
                        jQuery("#plugin_license_status").val("in_active");
                        jQuery("#plugin_license_expire").val("");
                        jQuery("#plugin_license_key").after(\'<span class="j2success">\'+data.message+\'</span>\')
                        jQuery(\'input[name="task"]\').val("plugin.apply");
                        setTimeout(function (){
                            jQuery("#plugin_license_key").closest("form").submit();
                        },1000)
    			    }
                }
            });
        }</script>';
        }

        return $html;
    }

    function getPluginData($extension_id)
    {
        if ($extension_id <= 0) {
            return;
        }
        $db = \Joomla\CMS\Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("*")->from('#__extensions')->where('extension_id=' . (int)$extension_id);
        $db->setQuery($query);
        return $db->loadObject();
    }
}