<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerGeozones extends F0FController
{
    use list_view;
    public function browse()
    {
        $app = JFactory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['geozone_name'] = $app->input->getString('geozone_name','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_geozone_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_geozone_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_GEOZONE_ID'
            ),
            'geozone_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=geozone&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_geozone_id',
                'label' => 'J2STORE_GEOZONE_NAME'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

	function getZone() {

	    $app=JFactory::getApplication();
		$data = $app->input->post->get('jform',array(),'array');
		$country_id =isset($data['country_id'])?$data['country_id']:$app->input->getInt('country_id', '0');
		$zone_id = isset($data['zone_id'])?$data['zone_id']:$app->input->getInt('zone_id');
		$z_fname =isset($data['field_name'])?$data['field_name']:$app->input->getString('field_name');
		$z_id = isset($data['field_id'])?$data['field_id']:$app->input->getString('field_id');



		// based on the country id, get zones and generate a select box
		if(!empty($country_id))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('j2store_zone_id,zone_name');
			$query->from('#__j2store_zones');
			$query->where('country_id='.$db->q($country_id));
			$db->setQuery((string)$query);
			$zoneList = $db->loadObjectList();
			$options = array();
			$options[] = JHtml::_('select.option', 0,JTEXT::_('J2STORE_ALL_ZONES'));
			if ($zoneList)
			{
				foreach($zoneList as $zone)
				{
					// this is only to generate the <option> tag inside select tag da i have told n times
					$options[] = JHtml::_('select.option', $zone->j2store_zone_id,$zone->zone_name);
				}
			}
			// now we must generate the select list and echo that... wait
			//$z_fname='jform[state_id]';
			$zoneList = JHtml::_('select.genericlist', $options, $z_fname, '', 'value', 'text',$zone_id,$z_id);
			echo $zoneList;
		}
		$app->close();
	}

	/**
	 * Method to delete
	 * Geo Rule of GeoZones
	 * @params
	 */

	function removeGeozoneRule(){

		$app = JFactory::getApplication();
		$post = $app->input->getArray($_POST);
		$georule_id = $post['rule_id'];
		$georuleTable = F0FTable::getInstance('geozonerule','Table');
		$json=array();
		if(!$georuleTable->delete($georule_id)){
			$json['msg'] = $georuleTable->getError();
		}else{
			$json['msg'] = JText::_('J2STORE_GEORULE_DELETED_SUCCESSFULLY');
		}
		echo json_encode($json);
		$app->close();

	}

	/**
	 *
	 * Method to import list of countries
	 * and create into geozonerules
	 */
    function importcountry()
    {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
        $app = $platform->application();
        $geozone_id = $app->input->getInt('geozone_id');
        $cids = $app->input->get('cid', array(), 'ARRAY');
        $link = 'index.php?option=com_j2store&view=geozones';
        $msg_type = 'warning';
        $msg = JText::_('J2STORE_ERROR_IN_IMPORTING');
        if (isset($geozone_id) && $geozone_id) {

            foreach ($cids as $cid) {
                $geozoneRule = $fof_helper->loadTable('Geozonerule', 'J2StoreTable');
                $geozoneRule->geozone_id = $geozone_id;
                $geozoneRule->country_id = $cid;
                $geozoneRule->zone_id = 0;
                try {
                    $geozoneRule->store();
                    $msg = JText::_('J2STORE_IMPORTED_SUCCESSFULLY');
                } catch (Exception $e) {
                    //$msg = $e;
                }
            }
            $link = 'index.php?option=com_j2store&view=countries&layout=modal&task=elements&tmpl=component&geozone_id=' . $geozone_id;
            $msg_type = 'message';

        }
        $platform->redirect($link, $msg, $msg_type);

    }

	/**
	 * Method to import list of zones
	 */
    function importzone()
    {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
        $app = $platform->application();
        $geozone_id = $app->input->getInt('geozone_id');
        $country_id = $app->input->getInt('country_id');
        $cids = $app->input->get('cid', array(), 'ARRAY');
        $link = 'index.php?option=com_j2store&view=geozones';
        $msg_type = 'warning';
        $msg = JText::_('J2STORE_ERROR_IN_IMPORTING');
        if (isset($geozone_id) && $geozone_id && isset($country_id) && $country_id) {
            foreach ($cids as $cid) {
                $geozoneRule = $fof_helper->loadTable('Geozonerule', 'J2StoreTable');
                $geozoneRule->geozone_id = $geozone_id;
                $geozoneRule->zone_id = $cid;
                $geozoneRule->country_id = $country_id;
                $geozoneRule->store();
            }
            $link = 'index.php?option=com_j2store&view=zones&layout=modal&task=elements&tmpl=component&geozone_id=' . $geozone_id;
            $msg_type = 'message';
            $msg = JText::_('J2STORE_IMPORTED_SUCCESSFULLY');
        }
        $platform->redirect($link, $msg, $msg_type);
    }
}
