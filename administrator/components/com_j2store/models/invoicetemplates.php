<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelInvoicetemplates extends F0FModel
{
	/**
	 * Builds the SELECT query
	 *
	 * @param   boolean  $overrideLimits  Are we requested to override the set limits?
	 *
	 * @return  F0FDatabaseQuery
	 */
	public function buildQuery($overrideLimits = false)
	{
		$query = parent::buildQuery ($overrideLimits);
		$query->where ( '#__j2store_invoicetemplates.invoice_type = ""' );
		return $query;
	}
    protected function onBeforeSave(&$data, &$table){
        $app = J2Store::platform()->application();
        $body = $app->input->getRaw('body','');
        if(!empty($body)){
            $data['body'] = $body;
        }
        return true;
    }

}