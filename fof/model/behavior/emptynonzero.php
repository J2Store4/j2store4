<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  model
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('F0F_INCLUDED') or die;

/**
 * FrameworkOnFramework model behavior class
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class F0FModelBehaviorEmptynonzero extends F0FModelBehavior
{
	/**
	 * This event runs when we are building the query used to fetch a record
	 * list in a model
	 *
	 * @param   F0FModel        &$model  The model which calls this event
	 * @param   F0FDatabaseQuery  &$query  The query being built
	 *
	 * @return  void
	 */
	public function onBeforeBuildQuery(&$model, &$query)
	{
		$model->setState('_emptynonzero', '1');
	}
}
