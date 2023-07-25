<?php
/**
 * @package     J2Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2018 J2Store . All rights reserved.
 * @license     GNU GPL v3 or later
 * */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelAppStores extends F0FModel {
    protected $_sflist = array();

    public function &getItemList($overrideLimits = false, $group = ''){
        if (empty($this->_sflist))
        {
            $this->_sflist = $this->_getJ2List($this->getState('limitstart',20), $this->getState('limit',20));
        }
        return $this->_sflist;
    }

    public function getTotal()
    {
        $total = $this->_getJ2List(0,0,'total');

        return $total;
    }

    protected function _getJ2List($start,$limit,$type=''){
        $config = J2Store::config();
        $plugin_update_date = $config->get('plugin_check_date','');
        $new_date = JFactory::getDate('now')->format('Y-m-d');
        $update_date = false;
        if(empty($plugin_update_date)){
            $update_date = true;
        }else{
            $date1 = strtotime($plugin_update_date);
            $date2 = strtotime($new_date);
            $dateDiff = $date2 - $date1;
            $fullDays = floor($dateDiff/(60*60*24));
            if($fullDays){
                //$config->saveOne('plugin_check_date',$new_date);
                $update_date = true;
            }

        }
        $file_path = JPATH_ADMINISTRATOR.'/components/com_j2store/backup/plugin.json';
        if(!JFile::exists($file_path) || $update_date){
            //get from server and store in local copy
            $json_data = $this->get_external_stream('https://cdn.j2store.net/plugins.json');
            if(!empty($json_data)){
                if(JFile::exists($file_path)){
                    JFile::delete($file_path);
                }
                JFile::write($file_path, $json_data);
                $config->saveOne('plugin_check_date',$new_date);
            }

        }
        $file_data = '{}';
        if(JFile::exists($file_path)) {
            $file_data = file_get_contents($file_path);
        }


        if(!empty($file_data)){
            $file_data = json_decode($file_data,true);
        }

        if(empty($file_data)){
            $file_data = array();
        }
        // sorting ASC
        if(!empty($file_data)){
            usort($file_data, function($a, $b) {
                $a1 = $a["plugin_name"]; //get the name string value
                $b1 = $b["plugin_name"];
                $out = strcasecmp($a1,$b1);
                if($out == 0){ return 0;} //they are the same string, return 0
                if($out > 0){ return 1;} // $a1 is lower in the alphabet, return 1
                if($out < 0){ return -1;} //$a1 is higher in the alphabet, return -1
            });
        }

        $search = $this->getState('search','');
        $plugin_type = $this->getState('plugin_type','');
        $current_page = $this->getState('current_page','popular');
        if(!empty($file_data) && (!empty($search) || !empty($plugin_type) || !empty($current_page))){
            $condition_data = array();
            $db = JFactory::getDBo();
            $query = $db->getQuery(true);
            $query->select('element,manifest_cache')->from('#__extensions')
                ->where('folder='.$db->q('j2store'));
            $db->setQuery($query);
            $installed_list = $db->loadObjectList();
            $installed_data = array();
            foreach ($installed_list as $installed){
                $installed_data[] = $installed->element;
            }
            foreach ($file_data as $file_dat){
                $search_status = false;
                if(!empty($search) && (strpos(strtolower($file_dat['plugin_name']), strtolower($search)) !== false )){

                    $search_status = true;
                }elseif(empty($search)){
                    $search_status = true;
                }
                $plugin_type_status = false;
                if(!empty($plugin_type) && $file_dat['type'] == $plugin_type ){
                    $plugin_type_status = true;
                }elseif(empty($plugin_type)){
                    $plugin_type_status = true;
                }

                $current_page_status = false;
                if($current_page == 'popular' && isset($file_dat['is_popular']) && $file_dat['is_popular']){
                    $current_page_status = true;
                }elseif ($current_page == 'free' && isset($file_dat['is_free']) && $file_dat['is_free']){
                    $current_page_status = true;
                }elseif ($current_page == 'installed' && isset($file_dat['element']) && in_array($file_dat['element'],$installed_data)){
                    $current_page_status = true;
                }elseif ($current_page == 'all'){
                    $current_page_status = true;
                }
                if($plugin_type_status && $search_status && $current_page_status){
                    $condition_data[] = $file_dat;
                }
            }
            $file_data = $condition_data;
        }

        if($type == 'total'){
            return count($file_data);
        }

        $final_data = array();
        if(count($file_data)){
            $file_data = array_values($file_data);
        }
        for ($i = $start; $i < count($file_data); $i++ ){
            $final_data[] = $file_data[$i];
            $limit--;
            if($limit == 0){
                break;
            }
        }
        return $final_data;
    }

    protected function get_external_stream($path) {
        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);

        if(curl_errno($ch)){
            $content = '';
        }


        curl_close($ch);

        if(!J2Store::utilities()->isJson($content)) {
            $content = '';
        }

        return $content;


    }


}
