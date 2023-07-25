<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if(!class_exists('J2Store')) {
	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
}

class J2StoreRouterHelper
{
	static function getAndPop(&$query, $key, $default = null)
	{
		if(isset($query[$key]))
		{
			$value = $query[$key];
			unset($query[$key]);
			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Finds a menu whose query parameters match those in $qoptions
	 * @param array $qoptions The query parameters to look for
	 * @param array $params The menu parameters to look for
	 * @return null|object Null if not found, or the menu item if we did find it
	 */
	static public function findMenu($qoptions = array(), $params = null)
	{
		static $joomla16 = null;

		if(is_null($joomla16)) {
			$joomla16 = version_compare(JVERSION,'1.6.0','ge');
		}

		// Convert $qoptions to an object
		if(empty($qoptions) || !is_array($qoptions)) $qoptions = array();

		$menus =JMenu::getInstance('site');
		$menuitem = $menus->getActive();
		// First check the current menu item (fastest shortcut!)
		if(is_object($menuitem)) {
			if(self::checkMenu($menuitem, $qoptions, $params)) {
				return $menuitem;
			}
		}


		//print_r($menus->getItems(array('language'), $languages) );
		foreach($menus->getMenu() as $item)
		{
			if($joomla16) {
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			} elseif($item->published)
			{
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			}
		}


		return null;
	}

	/**
	 * Checks if a menu item conforms to the query options and parameters specified
	 *
	 * @param object $menu A menu item
	 * @param array $qoptions The query options to look for
	 * @param array $params The menu parameters to look for
	 * @return bool
	 */
	static public function checkMenu($menu, $qoptions, $params = null)
	{
		$query = $menu->query;
		foreach($qoptions as $key => $value)
		{
			if(is_null($value)) continue;
			if(!isset($query[$key])) return false;
			if($query[$key] != $value) return false;
		}

		if(!is_null($params))
		{
			$menus =JMenu::getInstance('site');
			$check =  $menu->params instanceof JRegistry ? $menu->params : $menus->getParams($menu->id);

			foreach($params as $key => $value)
			{
				if(is_null($value)) continue;
				if( $check->get($key) != $value ) return false;
			}
		}

		$lang = J2Store::platform()->application()->getLanguage() ;
        $langu = (isset($qoptions['lang']) && $qoptions['lang']) ? $qoptions['lang'] : $lang->getTag();

		if($langu == $menu->language) {
			return true;
		}elseif($menu->language == '*') {
			return true;
		}else {
			return false;
		}

		return true;
	}

	static public function preconditionSegments($segments)
	{
		$newSegments = array();
		if(!empty($segments)) foreach($segments as $segment)
		{
			if(strstr($segment,':'))
			{
				$segment = str_replace(':','-',$segment);
			}
			if(is_array($segment)) {
				$newSegments[] = implode('-', $segment);
			} else {
				$newSegments[] = $segment;
			}
		}
		return $newSegments;
	}

	static public function findMenuOrders($qoptions) {

		$menus =JMenu::getInstance('site');
		$menu_id = null;
		foreach($menus->getMenu() as $item)
		{
			if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && $item->query['view']=='orders') {
				if(self::checkMenuOrders($item, $qoptions)) {
					$menu_id =$item->id;
				}
			}

		}
		return $menu_id;
	}

    static public function findMenuMyprofile($qoptions) {

        $menus = JMenu::getInstance('site');
        $menu_id = null;
        $menu = null;
        $user = JFactory::getUser();
        $user_access_level = $user->getAuthorisedViewLevels();
        foreach($menus->getMenu() as $item)
        {
            if(isset($item->query['option']) && $item->query['option'] == 'com_j2store'
                && isset($item->query['view']) && $item->query['view']=='myprofile' && in_array($item->access,$user_access_level)) {
                if(self::checkMenuOrders($item, $qoptions)) {
                    $menu =$item;
                    break;
                }
            }

        }
        return $menu;
    }


	public static function checkMenuOrders($menu, $qoptions) {
		$lang = J2Store::platform()->application()->getLanguage() ;
        $langu = (isset($qoptions['lang']) && $qoptions['lang']) ? $qoptions['lang'] : $lang->getTag();
		if($langu == $menu->language) {
			return true;
		}elseif($menu->language == '*') {
			return true;
		}else{
			return false;
		}
	}

	static public function findCheckoutMenu($qoptions){
        $menus = JMenu::getInstance('site');
        $menu_id = null;
        $menu = null;
        foreach($menus->getMenu() as $item)
        {
            if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && in_array($item->query['view'],array('checkouts','checkout')) && (!isset($item->query['layout']) || $item->query['layout'] != 'postpayment')) {
                unset( $qoptions['task'] );
                if(self::checkCartMenu($item, $qoptions)) {
                    $menu = $item;
                    break;
                }
            }

        }
        return $menu;
    }

    static public function findThankyouPageMenu($qoptions){
        $menus = JMenu::getInstance('site');
        $menu_id = null;
        $menu = null;
        foreach($menus->getMenu() as $item)
        {
            if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && in_array($item->query['view'],array('checkouts','checkout')) && isset($item->query['layout']) && isset($item->query['task']) && $item->query['layout'] == 'postpayment' && $item->query['task'] == 'confirmPayment') {
                unset( $qoptions['task'] );
                if(self::checkMenuOrders($item, $qoptions)) {
                    $menu = $item;
                    break;
                }
            }

        }
        return $menu;
    }

	static public function findMenuCarts($qoptions) {

		$menus =JMenu::getInstance('site');
		$menu_id = null;
		$menu = null;
		foreach($menus->getMenu() as $item)
		{
			if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && $item->query['view']=='carts') {
				unset( $qoptions['task'] );
				if(self::checkCartMenu($item, $qoptions)) {
					$menu = $item;
					break;
				}
			}

		}
		return $menu;
	}

    public static function checkCartMenu($menu,$qoptions) {
        $lang =  J2Store::platform()->application()->getLanguage();
        if(isset($qoptions['lang']) && $qoptions['lang'] == $menu->language){

            return true;
        }elseif($lang->getTag() == $menu->language) {
            //seems we have a match
            return true;
        }elseif($menu->language == '*') {
            return true;
        } else {
            return false;
        }
    }

    public static function findProductMenu($qoptions) {

        $menus =JMenu::getInstance('site');
        $menu = null;
        $other_tasks = array('compare','wishlist');
        $list_menu = $menus->getMenu();

        if(isset($qoptions['Itemid']) && !empty($qoptions['Itemid']) && isset($list_menu[$qoptions['Itemid']])){
            $selected_menu =  $list_menu[$qoptions['Itemid']];
            if(self::checkMenuProducts($selected_menu, $qoptions)) {
                $menu =$selected_menu;
            }

        }
        if(is_null($menu)){
            foreach($list_menu as $item)
            {
                if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && in_array ( $item->query['view'], array('products') )) {
                    if (isset($item->query['task']) && !empty($item->query['task']) && in_array($item->query['task'] , $other_tasks) && ($item->query['task'] == $qoptions['task']) ){
                        $menu =$item;
                        break;
                    }
                    if(self::checkMenuProducts($item, $qoptions)) {
                        $menu =$item;
                        //break on first found menu
                        break;
                    }
                }

            }
        }
        J2Store::plugin()->event('AfterFindProductMenu',array($menus,&$menu));
        return $menu;

    }

	public static function findProductTagsMenu($qoptions) {

		$menus =JMenu::getInstance('site');
		$menu = null;
		foreach($menus->getMenu() as $item)
		{
			if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' && isset($item->query['view']) && in_array ( $item->query['view'], array('producttags') )) {
				if(self::checkMenuProductTags($item, $qoptions)) {
					$menu =$item;
					//break on first found menu
					break;
				}
			}

		}
		return $menu;

	}

	public static function checkMenuProducts($menu, $qoptions) {
		$lang = J2Store::platform()->application()->getLanguage();

		//first check the category
		if(isset($qoptions['id'])) {
			if($qoptions['view'] == 'products'){
                $langu = (isset($qoptions['lang']) && $qoptions['lang']) ? $qoptions['lang'] : $lang->getTag();
				$cat_id = self::getProductCategory($qoptions['id'],$langu);
				$categories = array();
				if(isset($menu->query['catid'])) {
					$categories = $menu->query['catid'];
				}

				if(in_array($cat_id, $categories)) {
                    if(isset($qoptions['lang']) && $qoptions['lang'] == $menu->language){
                        return true;
                    }elseif($lang->getTag() == $menu->language) {
                        //seems we have a match
						return true;
					}elseif($menu->language == '*') {
						return true;
					} else {
						return false;
					}
				}
			}
		}
		return false;

	}

	public static function checkMenuProductTags($menu, $qoptions) {
		    $lang = J2Store::platform()->application()->getLanguage() ;

			//get tag alias
			if($qoptions['view'] == 'producttags'){

				//do we have a parent tag.
				//$parent_tag = isset($qoptions['parent_tag']) ? $qoptions['parent_tag'] : '';
                $langu = (isset($qoptions['lang']) && $qoptions['lang']) ? $qoptions['lang'] : $lang->getTag();

                $product_tags = self::getProductTags($qoptions['id'],$langu);
                $menu_tag = '';
                if(isset($menu->query['tag'])) {
                    $menu_tag = $menu->query['tag'];
                }
                if(in_array($menu_tag, $product_tags)) {
                    if($langu == $menu->language){
                        return true;
                    }elseif($menu->language == '*') {
                        return true;
                    } else {
                        return false;
                    }
                }
			}

		return false;

	}

	public static function getProductTags($id,$lang = ''){
        //first load the product to get the id.
        $product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
        $tags = array();
        if($product->load($id)) {
            if ($product->product_source == 'com_content') {

                $article_id = J2Store::article()->getAssociatedArticle($product->product_source_id,$lang);
                $tags = self::getArticleTags($article_id);
            }
        }
		return $tags;
	}

	public static function getArticleTags($id){
	    if(empty($id)){
	        return array();
        }
        $db = JFactory::getDbo ();
        $query = $db->getQuery (true);
        $query->select('#__tags.alias')->from ( '#__contentitem_tag_map' )
            ->leftJoin('#__tags ON #__tags.id = #__contentitem_tag_map.tag_id')
            ->where ( '#__contentitem_tag_map.content_item_id ='.$db->q ( $id ) )
            ->where ( '#__contentitem_tag_map.type_alias ='.$db->q ( 'com_content.article' ) );
        $db->setQuery ( $query );
        $tags = array();
        $tag_list = $db->loadRowList();
        foreach ($tag_list as $tag){
            $tags[] = $tag[0];
        }

        return $tags;
	}

    public static function getProductCategory($id,$lang='') {

        //first load the product to get the id.
        $product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();

        if($product->load($id)) {
            if($product->product_source == 'com_content') {
                $product->product_source_id = J2Store::article()->getAssociatedArticle($product->product_source_id,$lang);
                $article = J2Store::article()->getArticle($product->product_source_id);

                return $article->catid;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

	public static function getLanguageId($tag){
		$db = JFactory::getDbo ();
		$query = $db->getQuery (true);
		$query->select('lang_id')->from ( '#__languages' )->where ( 'lang_code ='.$db->q ( $tag ) );
		$db->setQuery ( $query );
		return $db->loadResult ();
	}

	public static function getItemAlias($id , $lang = '') {
		//first load the product to get the id.
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();

		if($product->load($id)) {
			if($product->product_source == 'com_content') {
				$article_helper = J2Store::article();
				$article = $article_helper->getArticle($product->product_source_id);
				$content_alias = $article->alias;
				if(!empty( $lang )){
                    $multilang = JLanguageMultilang::isEnabled();
				    if($multilang){
                        $article_id = $article_helper->getAssociatedArticle($product->product_source_id,$lang);
                        $article = $article_helper->getArticle($article_id);
                        $content_alias = $article->alias;
                    }
                    if ( $article_helper->isFalangInstalled() ){
						$config = J2Store::config();
						// get alternate content from falang tables
						if ( $config->get('enable_falang_support',0) ){
							$lang_id = self::getLanguageId($lang);
							$falan_alias = $article_helper->loadFalangAliasById($product->product_source_id,$lang_id) ;
							if(!empty( $falan_alias )){
								$content_alias = $falan_alias;
							}

						}
					}
				}
				return $content_alias;
			}else {
				return '';
			}
		}else {
			return '';
		}
	}

	/**
	 * Method to get the Product ID by article alias
	 * @param $segment mixed Either ID or a string containing the ID
	 * @return mixed Product ID or false on finding none
     */
	public static function getArticleByAlias($segment, $categories = array()) {
		$explode_results = explode(':', $segment);
		$article = new stdClass();
		if(isset($explode_results[0]) && is_numeric($explode_results[0])) {
			// The url is generated by version prior to 3.2.6.
			// We already have the product id. So it is sufficient to return the product id.
			// but for safar side. Let us query by alias again
			if(isset($explode_results[1]) && !empty($explode_results[1])) {
				$segment = $explode_results[1];
			}else {
				return $explode_results[0];
			}
		}
		//new router. Load article by alias
		$article = J2Store::article()->getArticleByAlias($segment, $categories);

		if(isset($article->id)) {
			$product = F0FTable::getAnInstance('Product', 'J2StoreTable');
			$product->get_product_by_source('com_content', $article->id);
			if($product->j2store_product_id) {
				return $product->j2store_product_id;
			}else {
				return false;
			}
		}else {
			return false;
		}
	}

	public static function getTagAliasByItem($id){
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable');

		if($product->load($id)) {
			if($product->product_source == 'com_content') {
				$db = JFactory::getDbo ();
				$query = $db->getQuery (true);
				$query->select ( 'tag.alias' )->from('#__contentitem_tag_map AS c_tag')
					->join ( 'LEFT', '#__tags AS tag ON c_tag.tag_id = tag.id'  )
					->where ( 'c_tag.content_item_id ='.$db->q($product->product_source_id) );
				//$article = J2Store::article()->getArticle($product->product_source_id);
				$db->setQuery ( $query );
				return $db->loadResult ();
			}else {
				return '';
			}
		}else {
			return '';
		}
	}

	public static function getFilterTagAlias($tag_id){
		$db = JFactory::getDbo ();
		$query = $db->getQuery (true);
		$query->select ( 'alias' )->from ( '#__tags' )->where ( 'id ='.$db->q($tag_id) );
		$db->setQuery ( $query );
		return $db->loadResult ();
	}

	public static function getTagByAlias($alias){
		$db = JFactory::getDbo ();
		$query = $db->getQuery (true);
		$query->select ( 'id' )->from ( '#__tags' )->where ( 'alias ='.$db->q($alias) );
		$db->setQuery ( $query );
		return $db->loadResult ();
	}

}