<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StorePopup
{
    public static function popup($url, $text, $options = array())
    {
        $class = (!empty($options['class'])) ? $options['class'] : '';
        $html = "<a  data-fancybox data-type=\"iframe\"  data-iframe='{\"css\":{\"height\":\"100vh\"}}' href=\"$url\"  href=\"javascript:;\" >\n";
        $html .= "<span class=\"" . $class . "\" >\n";
        $html .= "$text\n";
        $html .= "</span>\n";
        $html .= "</a>\n";
        return $html;
    }

    public static function getBrowser()
    {
        if (preg_match('/(?i)msie [2-9]/', $_SERVER['HTTP_USER_AGENT'])) {
            return 'ie';
        } else {
            return 'good';
        }
    }

    /**
     * Method to apply onclose after close popup  page reload
     */
    public static function onclose()
    {
        $document = J2Store::platform()->application()->getDocument();
        $js = "(function($) {
            $(document).ready(function() {  
              $('.fancybox').fancybox({
              afterClose: function() {
              window.location.reload();
              }
                });
            });
        })(j2store.jQuery);
    ";

        $document->addScriptDeclaration($js);

    }

    /**
     * Method to apply onclose update
     * @param string $url
     * @param string $text
     * @param array $options
     * @return string html
     */
    public static function popupAdvanced($url, $text, $options = array())
    {

        if (isset($options['refresh']) && ($options['refresh'] == true)) {
            self::onclose();
        }
        $id = (isset($options['id']) && !empty($options['id'])) ? $options['id'] : '';
        if (J2Store::platform()->isClient('site')) {
            $class = "zoom";
            $html = "<a  data-fancybox  class=\"$id\" data-type=\"iframe\" data-iframe='{\"css\":{\"height\":\"100vh\"}}' href=\"$url\" href=\"javascript:;\" >\n";
        } else {
            $class = (!empty($options['class'])) ? $options['class'] : '';
            $html = "<a data-fancybox class=\"$id\"  data-type=\"iframe\"  data-iframe='{\"css\":{\"height\":\"100vh\"}}'  href=\"$url\" href=\"javascript:;\">\n";
        }
        $html .= "<span class=\"" . $class . "\" id=\"" . $id . "\" >\n";
        $html .= "$text\n";
        $html .= "</span>\n";
        $html .= "</a>\n";

        return $html;
    }

}
