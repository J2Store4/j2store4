<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
class J2StoreStrapper {
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
    public static function addJS() {
        $params = J2Store::config();
        $platform = J2Store::platform();
        $document = JFactory::getDocument();
        //$document = $platform->application()->getDocument()->getWebAssetManager();

        $platform->loadExtra('jquery.framework');
        $platform->loadExtra('bootstrap.framework');

        //JHtml::_('jquery.framework');
        //JHtml::_('bootstrap.framework');
        //load name spaced jqueryui
        //load name spacer

        $platform->addScript('j2store-namespace','/media/j2store/js/j2store.namespace.js');
        //$wa->registerAndUseScript('namespace',JURI::root().'/media/j2store/js/j2store.namespace.js');
        $ui_location = $params->get ( 'load_jquery_ui', 3 );
        $load_fancybox = $params->get ( 'load_fancybox', 1 );
        $load_timepicker = $params->get ( 'load_timepicker', 1 );

        switch ($ui_location) {

            case '0' :
                // load nothing
                break;
            case '1':
                if ($platform->isClient('site')) {
                    $platform->addScript('j2store-jquery-ui',  '/media/j2store/js/jquery-ui.min.js');
                    //$wa->registerAndUseScript( );
                }
                break;

            case '2' :
                if ($platform->isClient('administrator')) {
                    $platform->addScript('j2store-jquery-ui', '/media/j2store/js/jquery-ui.min.js');
                }
                break;

            case '3' :
            default :
                 $platform->addScript('j2store-jquery-ui',  '/media/j2store/js/jquery-ui.min.js');
                break;
        }
        switch ($load_timepicker) {

            case '0' :
                // load nothing
                break;
            case '1':
                if ($platform->isClient('site')) {
                    $platform->addScript('j2store-jquery-ui',  '/media/j2store/js/jquery-ui.min.js');
                    $platform->addScript('j2store-timepicker-script', '/media/j2store/js/jquery-ui-timepicker-addon.js');
                }
                break;

            case '2' :
                if ($platform->isClient('administrator')) {
                    $platform->addScript('j2store-timepicker-script', '/media/j2store/js/jquery-ui-timepicker-addon.js');
                    self::loadTimepickerScript();
                }
                break;

            case '3' :
            default :
                // $manager = $platform->application()->getDocument()->getWebAssetManager();
                $platform->addScript('j2store-timepicker-script', '/media/j2store/js/jquery-ui-timepicker-addon.js');
                self::loadTimepickerScript();
                break;
        }
//        echo trim(JURI::root(),'/')."/media/j2store/js/jquery.validate.min.js";
//        echo "<br>hiii";
//        echo JURI::root(true);

        if($platform->isClient('administrator')) {

            $platform->addScript('j2store-jquery-validate-script','/media/j2store/js/jquery.validate.min.js');
            $platform->addScript('j2store-admin-script','/media/j2store/js/j2store_admin.js');
            $platform->addScript('j2store-fancybox-script','/media/j2store/js/jquery.fancybox.min.js');
        }
        else {


//            if($load_timepicker) {
//                $manager = $platform->application()->getDocument()->getWebAssetManager();
//                $manager->registerAndUseScript('my-script', JUri::root(true).'media/j2store/js/jquery-ui-timepicker-addon.js');
//                self::loadTimepickerScript($document);
//            }
            $platform->addScript('j2store-jquery-zoom-script','/media/j2store/js/jquery.zoom.js');
            $platform->addScript('j2store-script','/media/j2store/js/j2store.js');
            $platform->addScript('j2store-media-script','/media/j2store/js/bootstrap-modal-conflit.js');
            if($load_fancybox) {
                $platform->addScript('j2store-fancybox-script',  '/media/j2store/js/jquery.fancybox.min.js');
                $platform->addScript('j2store-document-script',  'jQuery(document).off("click.fb-start", "[data-trigger]");');
            }
//			$document->addScript(JUri::root(true).'/media/j2store/js/jquery.zoom.js');
//			$document->addScript(JURI::root(true).'/media/j2store/js/j2store.js');
//            $document->addScript(JURI::root(true).'/media/j2store/js/bootstrap-modal-conflit.js');
//            if($load_fancybox) {
//                $document->addScript(JURI::root(true).'/media/j2store/js/jquery.fancybox.min.js');
//                $document->addScriptDeclaration('jQuery(document).off("click.fb-start", "[data-trigger]");');
//            }
        }
        J2Store::plugin ()->event ( 'AfterAddJS' );
    }

    public static function addCSS() {
        $j2storeparams = J2Store::config ();
        $platform = J2Store::platform();

        //$wa = $platform->application()->getDocument()->getWebAssetManager();

        if($platform->isClient('administrator')) {
            // always load namespaced bootstrap
            // $document->addStyleSheet(JURI::root(true).'/media/j2store/css/bootstrap.min.css');
        }

        // load full bootstrap css bundled with J2Store.
        if ($platform->isClient('site') && $j2storeparams->get ( 'load_bootstrap', 0 )) {
            $platform->addStyle('j2store-bootstrap', '/media/j2store/css/bootstrap.min.css');
            //$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/bootstrap.min.css' );
        }

        // for site side, check if the param is enabled.
        if ($platform->isClient('site') && $j2storeparams->get ( 'load_minimal_bootstrap', 0 )) {
            $platform->addStyle('j2store-minimal','/media/j2store/css/minimal-bs.css');
            //$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/-bs.css' );
        }

        // jquery UI css
        $ui_location = $j2storeparams->get ( 'load_jquery_ui', 3 );
        switch ($ui_location) {

            case '0' :
                // load nothing
                break;
            case '1' :
                if ($platform->isClient('site')) {
                    //$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery-ui-custom.css' );
                    $platform->addStyle('j2store-custom-css','/media/j2store/css/jquery-ui-custom.css');
                }
                break;

            case '2' :
                if ($platform->isClient('administrator')) {
                    //$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery-ui-custom.css' );
                    $platform->addStyle('j2store-custom-css','/media/j2store/css/jquery-ui-custom.css');
                }
                break;

            case '3' :
            default :
                $platform->addStyle('j2store-custom-css','/media/j2store/css/jquery-ui-custom.css');
                //$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery-ui-custom.css' );
                break;
        }


        if ($platform->isClient('administrator')) {
            if (version_compare(JVERSION, '3.99.99', 'ge')) {
                $platform->addStyle('j2store-admin-css', '/media/j2store/css/J4/j2store_admin.css');
                $platform->addStyle('listview-css', '/media/j2store/css/backend/listview.css');
                $platform->addStyle('editview-css', '/media/j2store/css/backend/editview.css');
//                $document->addStyleSheet(JUri::root(true).'/media/j2store/css/J4/j2store_admin.css');
//                $document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/backend/listview.css' );
          //      $document->addStyleSheet ( JURI::root ( true ). '/media/j2store/css/backend/editview.css' );
            }else if (version_compare(JVERSION, '3.99.99', 'lt')) {

                $platform->addStyle('j2store-admin-css','/media/j2store/css/j2store_admin.css');
                //$document->addStyleSheet(JUri::root(true).'/media/j2store/css/j2store_admin.css');
            }
            $platform->addStyle('j2store-fancybox-css','/media/j2store/css/jquery.fancybox.min.css');
            // $document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery.fancybox.min.css' );
        } else {
            J2Store::strapper()->addFontAwesome();
            $document =JFactory::getDocument();
            // Add related CSS to the <head>
            if ($document->getType () == 'html' && $j2storeparams->get ( 'j2store_enable_css' )) {

                $template = self::getDefaultTemplate ();

                jimport ( 'joomla.filesystem.file' );
                // j2store.css
                if (JFile::exists ( JPATH_SITE . '/templates/' . $template . '/css/j2store.css' ))
                    $platform->addStyle( 'j2store-css', '/templates/' . $template . '/css/j2store.css' );
                else
                    $platform->addStyle( 'j2store-css', '/media/j2store/css/j2store.css' );
            } else {
                $platform->addStyle ( 'j2store-css','/media/j2store/css/j2store.css' );
            }
            $load_fancybox = $j2storeparams->get ( 'load_fancybox', 1 );
            if($load_fancybox){
                $platform->addStyle ( 'j2store-fancybox-css', '/media/j2store/css/jquery.fancybox.min.css' );
            }
        }
        J2Store::plugin ()->event ( 'AfterAddCSS' );
    }

    public static function getDefaultTemplate() {

        static $tsets;

        if ( !is_array( $tsets ) )
        {
            $tsets = array( );
        }
        $id = 1;
        if(!isset($tsets[$id])) {
            $db = JFactory::getDBO();
            $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
            $db->setQuery( $query );
            $tsets[$id] = $db->loadResult();
        }
        return $tsets[$id];
    }

    public static function loadTimepickerScript() {
        static $sets;
        $platform = J2Store::platform();
        if ( !is_array( $sets ) )
        {
            $sets = array( );
        }
        $id = 1;
        if(!isset($sets[$id])) {
            $platform->addInlineScript(self::getTimePickerScript());
            //$document->addScriptDeclaration(self::getTimePickerScript());
            $sets[$id] = true;
        }
    }

    public static function getTimePickerScript($date_format='', $time_format='', $prefix='j2store', $isAdmin=false) {

        //initialise the date/time picker
        if($isAdmin) {
            $platform = J2Store::platform();
           // $document =JFactory::getDocument();
            $platform->addScript('j2store-ui-timepicker','/media/j2store/js/jquery-ui-timepicker-addon.js');
            $platform->addStyle('j2store-ui-custom','/media/j2store/css/jquery-ui-custom.css');
        }

        if(empty($date_format)) {
            $date_format = 'yy-mm-dd';
        }

        if(empty($time_format)) {
            $time_format = 'HH:mm';
        }
        $localisation = self::getDateLocalisation();

        $element_date = $prefix.'_date';
        $element_time = $prefix.'_time';
        $element_datetime = $prefix.'_datetime';

        $timepicker_script ="
			if(typeof(j2store) == 'undefined') {
				var j2store = {};
			}

	if(typeof(jQuery) != 'undefined') {
		jQuery.noConflict();
	}

	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}

	if(typeof(j2store.jQuery) != 'undefined') {

		(function($) {
			$(document).ready(function(){
				/*date, time, datetime*/

				if( $('.$element_date').length ){
					$('.$element_date').datepicker({dateFormat: '$date_format'});
				}

				if($('.$element_datetime').length){
					$('.$element_datetime').datetimepicker({
							dateFormat: '$date_format',
							timeFormat: '$time_format',
							$localisation
					});
				}

				if($('.$element_time').length){
					$('.$element_time').timepicker({timeFormat: '$time_format', $localisation});
				}

			});
		})(j2store.jQuery);
	}
	";

        return $timepicker_script;

    }

    public static function getDateLocalisation($as_array=false) {

        //add localisation

        $params = J2Store::config();
        $language = JFactory::getLanguage()->getTag();
        if($params->get('jquery_ui_localisation', 0) && strpos($language, 'en') === false) {
            $platfrom = J2Store::platform();
            //$doc = JFactory::getDocument();
            $platfrom->addScript('jquery-ui-i18n','/ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js');
           // $doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js');

            //set the language default
            $tag = explode('-', $language);
            if(isset($tag[0]) && strlen($tag[0]) == 2) {
                $script = "";
                $script .= "(function($) { $.datepicker.setDefaults($.datepicker.regional['{$tag[0]}']); })(j2store.jQuery);";
                $platfrom->addInlineScript($script);
                //$doc->addScriptDeclaration($script);
            }

        }

        //localisation
        $currentText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CURRENT_TEXT'));
        $closeText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CLOSE_TEXT'));
        $timeOnlyText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CHOOSE_TIME'));
        $timeText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_TIME'));
        $hourText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_HOUR'));
        $minuteText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_MINUTE'));
        $secondText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_SECOND'));
        $millisecondText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_MILLISECOND'));
        $timezoneText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_TIMEZONE'));

        if($as_array) {

            $localisation = array (
                'currentText' => $currentText,
                'closeText' => $closeText,
                'timeOnlyTitle' => $timeOnlyText,
                'timeText' => $timeText,
                'hourText' => $hourText,
                'minuteText' => $minuteText,
                'secondText' => $secondText,
                'millisecText' => $millisecondText,
                'timezoneText' => $timezoneText
            );

        } else {

            $localisation ="
			currentText: '$currentText',
			closeText: '$closeText',
			timeOnlyTitle: '$timeOnlyText',
			timeText: '$timeText',
			hourText: '$hourText',
			minuteText: '$minuteText',
			secondText: '$secondText',
			millisecText: '$millisecondText',
			timezoneText: '$timezoneText'
			";
        }

        return $localisation;

    }

    public static function addDateTimePicker($element, $json_options) {
        $timepicker_script = self::getDateTimePickerScript($element, $json_options) ;
        J2Store::platform()->addInlineScript($timepicker_script );
        //JFactory::getDocument ()->addScriptDeclaration ( $timepicker_script );
    }

    public static function getDateTimePickerScript($element, $json_options) {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = self::getDateLocalisation (true);
        $variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
        $variables['timeFormat'] = $option_params->get ( 'time_format', 'HH:mm' );
        if ($option_params->get ( 'hide_pastdates', 1 )) {
            $variables ['minDate'] = 0;
        }

        $variables = json_encode ( $variables );
        $timepicker_script = "
		(function($) {
			$(document).ready(function(){
				$('.$element').datetimepicker({$variables});
			});
		})(j2store.jQuery);";

        return $timepicker_script;
    }

    public static function addDatePicker($element, $json_options) {

        $datepicker_script = self::getDatePickerScript($element, $json_options) ;
        J2Store::platform()->addInlineScript($datepicker_script);
        //JFactory::getDocument ()->addScriptDeclaration ( $datepicker_script);
    }

    public static function getDatePickerScript($element, $json_options) {
        $option_params = J2Store::platform()->getRegistry($json_options);
        $variables = array();
        $variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
        if ($option_params->get ( 'hide_pastdates', 1 )) {
            $variables ['minDate'] = 0;
        }

        $variables = json_encode ( $variables );
        $datepicker_script = "
		(function($) {
			$(document).ready(function(){
				$('.$element').datepicker({$variables});
			});
		})(j2store.jQuery);";

        return $datepicker_script;
    }

    public static function sizeFormat($filesize)
    {
        if($filesize > 1073741824) {
            return number_format($filesize / 1073741824, 2)." Gb";
        } elseif($filesize >= 1048576) {
            return number_format($filesize / 1048576, 2)." Mb";
        } elseif($filesize >= 1024) {
            return number_format($filesize / 1024, 2)." Kb";
        } else {
            return $filesize." bytes";
        }
    }

    public function addFontAwesome(){
        $config = J2Store::config();
        //$document = JFactory::getDocument();
        $font_awesome_ui = $config->get('load_fontawesome_ui',1);
        if($font_awesome_ui){
            J2Store::platform()->addStyle('j2store-font-awesome-css','/media/j2store/css/font-awesome.min.css');
            //$document->addStyleSheet ( JUri::root () . 'media/j2store/css/font-awesome.min.css' );
        }
    }
}