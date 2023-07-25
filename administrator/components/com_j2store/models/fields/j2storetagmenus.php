<?php
/**
 * @package     J2Store
 * @author      Alagesan
 * @copyright   Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license     GNU GPL v3 or later
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.form.formfield');
class JFormFieldJ2StoreTagMenus extends JFormField
{

    protected $type = 'j2storetagmenus';
    /**
     * Method to get the field input markup.
     *
     * @return  string	The field input markup.
     * @since   1.6
     */
    protected function getInput()
    {
        $plugin = $this->getPlugin();
        if(isset($plugin->enabled) && $plugin->enabled){
            $app = JFactory::getApplication();
            $options = array();
            $menus = JMenu::getInstance('site');
            $menu_id = null;
            $options[''] = JText::_('J2STORE_SELECT_OPTION');
            foreach($menus->getMenu() as $item)
            {
                if($item->type== 'component'){
                    if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && $item->query['view'] == 'producttags' ){
                        $options[$item->id] = $item->title;
                    }
                }
            }
            return JHTML::_('select.genericlist', $options, $this->name, array('class'=>"input"), 'value', 'text', $this->value);
        }
        return '';
    }

    public function getLabel(){
        $plugin = $this->getPlugin();
        if(isset($plugin->enabled) && $plugin->enabled){
            return JText::_('J2STORE_CANONICAL_MENU');
        }
    }

    public function getPlugin(){
        $db = JFactory::getDBo();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__extensions')
            ->where('element='.$db->q('j2canonical'))
            ->where('type='.$db->q('plugin'))
            ->where('folder='.$db->q('system'));
        $db->setQuery($query);
        return $db->loadObject();
    }

}

