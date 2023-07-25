<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

require_once(JPATH_ADMINISTRATOR .'/components/com_j2store/helpers/j2html.php');

class JFormFieldGeozones extends JFormFieldList
{
	protected $type = 'geozones';
	function getInput(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('j2store_geozone_id as value, geozone_name as text')->from('#__j2store_geozones');
        $db->setQuery($query);
        $array = $db->loadObjectList();
        $options[] = JHtml::_( 'select.option', 0, JText::_('J2STORE_SRATE_SELECT_GEOZONE'));
        foreach( $array as $data) {
            $options[] = JHtml::_( 'select.option', $data->value, $data->text);
        }
        if(!is_array($options)) $options = array();
       if (version_compare(JVERSION, '3.99.99', 'lt')) {
            return J2Html::select()->clearState()
                ->type('genericlist')
                ->name($this->name)
                ->value($this->value)
                ->attribs(array('multiple'=>true))
                ->setPlaceholders(
                    array('*'=>JText::_('J2STORE_SELECT_ALL'))
                )
                ->hasOne('Geozones')
                ->setRelations( array(
                        'fields' => array (
                            'key' => 'j2store_geozone_id',
                            'name' => array('geozone_name')
                        )
                    )
                )->getHtml();
        }else{
            $displayData = array(
                'class' => 'input-small',
                'name' => $this->name,
                'value' => $this->value  ,
                'options' => $options,
                'autofocus' => '',
                'onchange' => '',
                'dataAttribute' => '',
                'readonly' => '',
                'disabled' => false,
                'hint' => '',
                'required' => false,
                'id' => '',
                'multiple'=> true
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/list-fancy-select.php';
            $media_render = self::getRenderer('joomla.form.field.list-fancy-select', $path);
            return $media_render->render($displayData);
        }
    }
}

