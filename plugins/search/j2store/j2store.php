<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_content/router.php';

/**
 * Content search plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Search.content
 * @since       1.6
 */
class PlgSearchJ2Store extends JPlugin
{
	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'product' => 'Products'
		);

		return $areas;
	}

	/**
	 * Search content (articles).
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$menu_id =  $this->params->get('menuitem_id');
        $platform = J2Store::platform();
		$db = JFactory::getDbo();
		$app = $platform->application();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$tag = JFactory::getLanguage()->getTag();

		require_once JPATH_SITE . '/components/com_content/helpers/route.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';

		$searchText = $text;

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		//$sContent = $this->params->get('search_content', 1);
		$limit = $this->params->def('search_limit', 50);
		$nullDate = $db->getNullDate();

		$tz = JFactory::getConfig()->get('offset');
		$date = JFactory::getDate('now', $tz);

		//default to the sql formatted date
		$now = $date->toSql(true);

		//$date = JFactory::getDate();
		//$now = $date->toSql();
		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		switch ($phrase)
		{
			case 'exact':
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = 'a.title LIKE ' . $text;
				$wheres2[] = 'a.introtext LIKE ' . $text;
				$wheres2[] = 'a.fulltext LIKE ' . $text;
				$wheres2[] = 'a.metakey LIKE ' . $text;
				$wheres2[] = 'a.metadesc LIKE ' . $text;
				$wheres2[] = '#__j2store_variants.sku LIKE ' . $text;
				$wheres2[] = '#__j2store_variants.upc LIKE ' . $text;

				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = 'a.title LIKE ' . $word;
					$wheres2[] = 'a.introtext LIKE ' . $word;
					$wheres2[] = 'a.fulltext LIKE ' . $word;
					$wheres2[] = 'a.metakey LIKE ' . $word;
					$wheres2[] = 'a.metadesc LIKE ' . $word;
					$wheres2[] = '#__j2store_variants.sku LIKE ' . $word;
					$wheres2[] = '#__j2store_variants.upc LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
				$order = 'a.title ASC';
				break;

			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}

		$rows = array();
		$query = $db->getQuery(true);

		// Search articles.
		if ($limit > 0)
		{
			$query->clear();

			// SQLSRV changes.
			$case_when = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias', '!=', '0');
			$case_when .= ' THEN ';
			$a_id = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id . ' END as slug';

			$case_when1 = ' CASE WHEN ';
			$case_when1 .= $query->charLength('c.alias', '!=', '0');
			$case_when1 .= ' THEN ';
			$c_id = $query->castAsChar('c.id');
			$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
			$case_when1 .= ' ELSE ';
			$case_when1 .= $c_id . ' END as catslug';

			$query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created,a.id,#__j2store_variants.sku')
			      ->select($query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text')
			      ->select('c.title AS section, ' . $case_when . ',' . $case_when1 . ', ' . '\'2\' AS browsernav')

			      ->from('#__content AS a');
			$query->select('#__j2store_products.*');
			$query->join('INNER', '#__j2store_products ON #__j2store_products.product_source='.$db->q('com_content').' AND #__j2store_products.product_source_id = a.id AND #__j2store_products.enabled=1 AND #__j2store_products.visibility=1 ');
			$query->join('LEFT', '#__j2store_variants ON #__j2store_products.j2store_product_id = #__j2store_variants.product_id ');

			$query->select('#__j2store_productimages.thumb_image, #__j2store_productimages.main_image, #__j2store_productimages.additional_images');
			$query->join('LEFT OUTER', '#__j2store_productimages ON #__j2store_products.j2store_product_id=#__j2store_productimages.product_id');

			$query->join('INNER', '#__categories AS c ON c.id=a.catid')
			      ->where(
				      '(' . $where . ') AND a.state=1 AND c.published = 1 AND a.access IN (' . $groups . ') '
				      . 'AND c.access IN (' . $groups . ') '
				      . 'AND (a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ') '
				      . 'AND (a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= ' . $db->quote($now) . ')'
			      );
			// here let us join our j2store products with content
			$query->group('a.id, a.title, a.metadesc, a.metakey, a.created, a.introtext, a.fulltext, c.title, a.alias, c.alias, c.id');
			$query->order($order);


			// Filter by language.
			if ($platform->isClient('site') && JLanguageMultilang::isEnabled())
			{
				$query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
				      ->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
			}

			$db->setQuery($query, 0, $limit);
			$list = $db->loadObjectList();
			$limit -= count($list);

			if (isset($list))
			{
				foreach ($list as $key => $item)
				{
					//let us get the redirect choice
					if($this->params->get('redirect_to','j2store') =='article'){
						$list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug);
					}else{
						require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/router.php');
						$qoptions = array (
							'option' => 'com_j2store',
							'view' => 'products',
							'task' => 'view',
							'id' => $item->j2store_product_id
						);
						$pro_menu = J2StoreRouterHelper::findProductMenu ( $qoptions );
						$menu_id = isset($pro_menu->id) ? $pro_menu->id : $menu_id;
						$list[$key]->href  = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$item->j2store_product_id.'&Itemid='.$menu_id);
						$list[$key]->image = $item->main_image;
						//$list[$key]->href = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$item->j2store_product_id.'&Itemid='.$menu_id);
					}
				}
			}

			$rows[] = $list;
		}
		$results = array();
		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();

				foreach ($row as $article)
				{
					if (SearchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'metadesc', 'metakey','sku')))
					{
						$new_row[] = $article;

					}

				}

				$results = array_merge($results, (array) $new_row);
			}
		}

		return $results;
	}
}
