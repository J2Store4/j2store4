<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;
//F0FModel
class J2StoreModelEupdates extends F0FModel
{
	private $url = 'https://cdn.j2store.net/extensions.json';

	public function getUpdates() {

		$updates = array();
		$all_plugins = $this->folder('j2store')->getList();
		$json = $this->sendRequest($this->url);
		if(!empty($json)) {
			$registry = J2Store::platform()->getRegistry($json);
			$update_data = $registry->toArray();
            //get plugins that have updates
            foreach($all_plugins as $plugin) {
                if(isset($update_data[$plugin->element])) {
                    //load manifest cache to get the version
                    $manifest = json_decode($plugin->manifest_cache);
                    if($manifest) {
                        $version = (string) $manifest->version;
                        if(version_compare($update_data[$plugin->element], $version, 'gt')) {
                            $plugin->current_version = $version;
                            $plugin->new_version = $update_data[$plugin->element];
                            $updates[] = $plugin;
                        }
                    }
                }
            }
		}
		return $updates;
	}

	private function sendRequest($request_url) {

		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $request_url
		));
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
		return $resp;
	}
}