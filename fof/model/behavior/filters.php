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
class F0FModelBehaviorFilters extends F0FModelBehavior
{
	/**
	 * This event runs after we have built the query used to fetch a record
	 * list in a model. It is used to apply automatic query filters.
	 *
	 * @param   F0FModel        &$model  The model which calls this event
	 * @param   F0FDatabaseQuery  &$query  The model which calls this event
	 *
	 * @return  void
	 */
	public function onAfterBuildQuery(&$model, &$query)
	{
		$table = $model->getTable();
		$tableName = $table->getTableName();
		$tableKey = $table->getKeyName();
		$db = $model->getDBO();

		$filterzero = $model->getState('_emptynonzero', null);

		$fields = $model->getTableFields();
		$backlist = $model->blacklistFilters();

		foreach ($fields as $fieldname => $fieldtype)
		{
			if (in_array($fieldname, $backlist)) {
				continue;
			}
			$field = new stdClass;
			$field->name = $fieldname;
			$field->type = $fieldtype;
			$field->filterzero = $filterzero;

			$filterName = $field->name;
			$filterState = $model->getState($filterName, null);
            $table_alias = $model->getTableAlias() ? $model->getTableAlias() : $tableName;
			$field = F0FModelField::getField($field, array('dbo' => $db, 'table_alias' => $table_alias));

			if ((is_array($filterState) && (
					array_key_exists('value', $filterState) ||
					array_key_exists('from', $filterState) ||
					array_key_exists('to', $filterState)
				)) || is_object($filterState))
			{
				$options = new JRegistry($filterState);
			}
			else
			{
				$options = new JRegistry;
				$options->set('value', $filterState);
			}

			$methods = $field->getSearchMethods();
			$method = $options->get('method', $field->getDefaultSearchMethod());

			if (!in_array($method, $methods))
			{
				$method = 'exact';
			}

			switch ($method)
			{
				case 'between':
				case 'outside':
				case 'range' :
					$sql = $field->$method($options->get('from', null), $options->get('to'));
					break;

				case 'interval':
				case 'modulo':
					$sql = $field->$method($options->get('value', null), $options->get('interval'));
					break;

				case 'exact':
				case 'partial':
				case 'search':
				default:
					$sql = $field->$method($options->get('value', null));
					break;
			}

			if ($sql)
			{
				$query->where($sql);
			}
		}
	}
}
