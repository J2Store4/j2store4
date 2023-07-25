<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined('_JEXEC') or die;

/**
 * Frontpage View class
 *
 * @since  1.5
 */
class J2StoreViewProducts extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
        $platform = J2Store::platform();
		// Parameters
		$app       = $platform->application();
		$doc       = $app->getDocument();
		$params    = $app->getParams();
		$feedEmail = $app->get('feed_email', 'author');
		$siteEmail = $app->get('mailfrom');
		$doc->link = $platform->getProductUrl();
		// Get some data from the model
		$app->input->set('limit', $app->get('feed_limit'));		
		$model = $this->getModel();
		$rows      = $model->getItemList();
		
		foreach ($rows as $row)
		{
			// Strip html from feed item title
			$title = $this->escape($row->product_name);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			// Compute the article slug
			$row->slug = $row->j2store_product_id;

			// Url link to article
			$link = $platform->getProductUrl(array('task' => 'view', 'id' => $row->j2store_product_id));
			// Get row fulltext
			$description = ($params->get('feed_summary', 0) ? $row->product_short_desc . $row->product_long_desc : $row->product_short_desc);
			$user = JFactory::getUser($row->created_by);
			$author      = $user->name;

			// Load individual item creator class
			$item           = new JFeedItem;
			$item->title    = $title;
			$item->link     = $link;
			$item->date     = $row->modified_on;
			$item->category = array();

			$item->author = $author;

			if ($feedEmail == 'site')
			{
				$item->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$item->authorEmail = $row->author_email;
			}

			// Add readmore link to description if introtext is shown, show_readmore is true and fulltext exists
			if (!$params->get('feed_summary', 0) && $params->get('feed_show_readmore', 0) && $row->fulltext)
			{
				$description .= '<p class="feed-readmore"><a target="_blank" href ="' . $item->link . '">' . JText::_('COM_CONTENT_FEED_READMORE') . '</a></p>';
			}

			// Load item description and add div
			$item->description = '<div class="feed-description">' . $description . '</div>';

			// Loads item info into rss array
			$doc->addItem($item);
		}
	}
}
