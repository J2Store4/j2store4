<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2023 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die('Restricted access');

class J2License
{
    public static $instance = null;

    public function __construct($properties = null)
    {
    }

    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }
    protected function sendRequest($api_params)
    {
        $api_url = 'https://www.j2store.org/edd-api';
        $license_data = array();
        if (empty($api_url) || empty($api_params)) {
            return $license_data;
        }
        $api_url = $api_url . '?' . http_build_query($api_params);
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
            ));
            $response = curl_exec($curl);
            $license_data = json_decode($response, true);
            curl_close($curl);
        } catch (\Exception $e) {
        }
        return $license_data;
    }

    public function checkLicense($params = array())
    {
        $baseURL = str_replace('/administrator', '', JURI::base());
        // Data to send in our API request
        $api_params = array(
            'edd_action' => 'check_license',
            'license' => '',
            'item_id' => 0,
            'item_name' => '',
            'url' => $baseURL,
            'environment' => '',
            'element' => ''
        );
        $api_params = array_merge($api_params, $params);
        $response = $this->sendRequest($api_params);
        return is_array($response) && isset($response['license']) && $response['license'] == 'valid';
    }

    function getVersion($params = array())
    {
        $api_params = array(
            'edd_action' => 'get_version',
            'license' => '',
            'item_name' => '',
            'item_id' => 0,
            'version' => '',
            'slug' => '',
            'author' => '',
            'url' => '',
            'beta' => '',
            'element' => ''
        );
        $api_params = array_merge($api_params, $params);
        return $this->sendRequest( $api_params);
    }

    public function activateLicense($params)
    {
        // Data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => '',
            'item_id' => 0,
            'item_name' => '',
            'url' => '',
            'environment' => 'production',
            'element' => ''
        );
        $api_params = array_merge($api_params, $params);
        return $this->sendRequest($api_params);
    }

    public function deActivateLicense($params)
    {
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => '',
            'item_id' => 0,
            'item_name' => '',
            'url' => '',
            'environment' => 'production',
            'element' => ''
        );
        $api_params = array_merge($api_params, $params);
        return $this->sendRequest($api_params);
    }
}
