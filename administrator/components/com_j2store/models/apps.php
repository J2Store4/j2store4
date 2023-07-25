<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
class J2StoreModelApps extends F0FModel {

	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->getSelectQuery($query);
		$this->getWhereQuery($query);
		$query->order('app.name ASC, app.extension_id');
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("app.extension_id,app.name,app.type,app.folder,app.element,app.params,app.enabled,app.ordering, app.manifest_cache")
		->from("#__extensions as app");

	}

	protected function getWhereQuery(&$query)
	{
		$db = $this->_db;
		$query->where("app.type=".$db->q('plugin'));
		$query->where("app.element LIKE 'app_%'");
		$query->where("app.folder='j2store'");

		$search = $this->getState('search', '');
		if($search){
			$query->where(
					$db->qn('app.').'.'.$db->qn('name').' LIKE '.$db->q('%'.$search .'%')
			);

		}


	}

	public function getInserted($tablename){
		$db = JFactory::getDbo();
		$status = true;
        $application = JFactory::getApplication();
		//Force parsing of SQL file since Joomla! does that only in install mode, not in upgrades
		$sql = 'components/com_j2store/sql/install/mysql/'.$tablename.'.sql';
		$queries = JDatabaseDriver::splitSql(file_get_contents($sql));

		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '' && $query[0] != '#')
			{
				$db->setQuery($query);
				if (!$db->execute())
				{
					$application->enqueueMessage(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
					$status  = false;
				}

			}
		}

		return $status;

	}
}