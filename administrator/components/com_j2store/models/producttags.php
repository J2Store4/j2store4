<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';
class J2StoreModelProducttags extends F0FModel {

	protected  $_rawData = null;
	protected $default_behaviors = array('filters', 'default');
	protected $_productlist = array();
	protected $productpagination = null;
	protected $productpagetotal = null;

	protected $_sflist = array();
	protected $_sfpagination = null;
	protected $_sfpagetotal = null;
	protected $_sfalllist = null;

	function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * Returns a single item. It uses the id set with setId, or the first ID in
	 * the list of IDs for batch operations
	 *
	 * @param   integer  $id  Force a primary key ID to the model. Use null to use the id from the state.
	 *
	 * @return  F0FTable  A copy of the item's F0FTable array
	 */
	public function &getItem($id = null)
	{
		if (!is_null($id))
		{
			$this->record = null;
			$this->setId($id);
		}

		if (empty($this->record))
		{
			$table = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
			$table->load($this->id);
			$this->record = $table;
			// Do we have saved data?
			$session = JFactory::getSession();
			if ($this->_savestate)
			{
				$serialized = $session->get($this->getHash() . 'savedata', null);
				if (!empty($serialized))
				{
					$data = @unserialize($serialized);

					if ($data !== false)
					{
						$k = $table->getKeyName();

						if (!array_key_exists($k, $data))
						{
							$data[$k] = null;
						}

						if ($data[$k] != $this->id)
						{
							$session->set($this->getHash() . 'savedata', null);
						}
						else
						{
							$this->record->bind($data);
						}
					}
				}
			}
			try {
				$this->onAfterGetItem($this->record);
			}catch (Exception $e) {
				$this->setError($e->getMessage());
			}
		}

		return $this->record;
	}

	public function onProcessList(&$resultArray) {
		$app = JFactory::getApplication();
		J2Store::plugin()->importCatalogPlugins();
		foreach ($resultArray as $product) {
			$app->triggerEvent('onJ2StoreAfterGetProduct', array(&$product));
		}
	}

	public function getProduct($product) {

		if($product->product_type) {
			$result = $this->addBehavior($product->product_type);
		}else {
			$this->addBehavior('simple');
		}
        $ref_model = $this;
		try
		{
			// Call the behaviors
			$result = $this->modelDispatcher->trigger('onBeforeGetProduct', array(&$ref_model, &$product));
		}
		catch (Exception $e)
		{
			// Oops, an exception occurred!
			$this->setError($e->getMessage());
		}

		try
		{
			// Call the behaviors
			$result = $this->modelDispatcher->trigger('onAfterGetProduct', array(&$ref_model, &$product));

		}
		catch (Exception $e)
		{
			// Oops, an exception occurred!
			$this->setError($e->getMessage());
		}
		return $product;

	}

	/**
	 * Method to update product information
	 *
	 * @return array or false
	 */

	public function updateProduct() {

		$result = array();
		$app = JFactory::getApplication ();
		$product_id = $app->input->getInt('product_id', 0);
		if(!$product_id) {
			return false;
		}

		$product = J2Store::product()->setId($product_id)->getProduct();

		if($product->product_type) {
			$this->addBehavior($product->product_type);
		}else {
			$this->addBehavior('simple');
		}
        $ref_model = $this;
		try
		{
			// Call the behaviors
			$data = $this->modelDispatcher->trigger('onUpdateProduct', array(&$ref_model, &$product));
			if(count($data)) {
				$result = $data[0];
			}
		}
		catch (Exception $e)
		{
			// Oops, an exception occurred!
			$this->setError($e->getMessage());
			return false;
		}
		//plugin trigger
		$result['afterDisplayPrice'] = J2Store::plugin()->eventWithHtml('AfterUpdateProduct', array($data));
		J2Store::plugin()->event('UpdateProductResponse',array(&$result,$ref_model,$product));
		return $result;

	}

	public function getTags($tag){
		//get the db object
		$db = JFactory::getDbo();

		if($tag){
			$selected_tag = implode('\',\'',$tag);
		}
		//get the query
		$query = $db->getQuery(true);
		// query to fetch all data
		$query->select('*');
		$query->from('#__tags');
		//$query->where('extension ='.$db->quote('com_content'));
		if(isset($tag) && !empty($tag)){
			$query->where('alias IN (\''.$selected_tag.'\')');
		}
		$query->where('published =1');
		$query->order('lft ASC');
		$db->setQuery($query);
		//load objectlist and return the data
		$results = $db->loadObjectList();
		return $results;
	}

	private function getFilterValues()
	{
		return (object)array(
			'search' 			=>	$this->getState('search',null,'string'),
			'product_ids' 		=> 	$this->getState('product_ids',null,'string'),
			'product_type' 		=> 	$this->getState('product_type',null,'string'),
			'visible' 			=>  $this->getState('visible',null,'string'),
			'vendor_id' 		=>  $this->getState('vendor_id',null,'int'),
			'manufacturer_id' 	=>  $this->getState('manufacturer_id',null,'int'),
			'productid_from' 	=>  $this->getState('productid_from',null,'int'),
			'productid_to' 		=>  $this->getState('productid_to',null,'int'),
			'pricefrom' 		=>  $this->getState('pricefrom',null,'int'),
			'priceto' 			=> 	$this->getState('priceto',null,'int'),
			'since' 			=>  $this->getState('since',null,'string'),
			'until' 			=>  $this->getState('until',null,'string'),
			'taxprofile_id'     =>  $this->getState('taxprofile_id',null,'int'),
			'enabled'     		=>  $this->getState('enabled',null,'string'),
			'shippingmethod'	=>  $this->getState('shippingmethod',null,'int'),
			'sku'				=>  $this->getState('sku',null,'string'),
			'catids'			=>  $this->getState('catids',null,'array'),
			'sortby'			=>  $this->getState('sortby',null,'string'),
			'instock'			=>  $this->getState('instock',null,'int'),
			'productfilter_id'			=>  $this->getState('productfilter_id',null,'int'),
			'filter_order'	=>  $this->getState('filter_order',null,'string'),
			'filter_order_Dir'	=>  $this->getState('filter_order_Dir',null,'string'),
		);
	}

    /**
     * Method to merge config params and  Menu params
     * @return \Joomla\Registry\Registry|JRegistry
     */
	function getMergedParams(){
        $platform = J2Store::platform();
		$app = $platform->application();
		if($platform->isClient('administrator')) {
			return $platform->getRegistry('{}');
		}

		//first get the menu params
		$aparams = $app->getParams();
		$menuParams = $platform->getRegistry('{}');

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($aparams);

		//now load the configurations

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('*')->from('#__j2store_configurations');
		$db->setQuery($query);
		$results = $db->loadObjectList('config_meta_key');
		$config =array();
		foreach($results as $key=>$value){
			$config[$key] = $value->config_meta_value;
		}

		$configparams = $platform->getRegistry($config,true);
		$configparams->merge($mergedParams);
		return $configparams;
	}

	function _buildSortQuery(&$query){
		$db = $this->_db;
		$state = $this->getFilterValues();
		if($state->sortby) {
			$query->order($state->sortby);
		}
	}


	public function getSortFields()
	{
		//contains sorting fields
		//both in ascending and descending

		return array(
			''=> JText::_('J2STORE_PRODUCT_SORT_BY'),
			'pname' => JText::_('J2STORE_PRODUCT_FILTER_SORT_NAME_ASCENDING'),
			'rpname' => JText::_('J2STORE_PRODUCT_FILTER_SORT_NAME_DESCENDING'),
			'min_price' => JText::_('J2STORE_PRODUCT_FILTER_SORT_PRICE_ASCENDING'),
			'rmin_price' => JText::_('J2STORE_PRODUCT_FILTER_SORT_PRICE_DESCENDING'),
			'sku' => JText::_('J2STORE_PRODUCT_FILTER_SORT_SKU_ASCENDING'),
			'rsku' => JText::_('J2STORE_PRODUCT_FILTER_SORT_SKU_DESCENDING'),
			'brand' => JText::_('J2STORE_PRODUCT_FILTER_SORT_BRAND_ASCENDING'),
			'rbrand' => JText::_('J2STORE_PRODUCT_FILTER_SORT_BRAND_DESCENDING')
		);
	}

	/**
	 * Method to get Product filters based on the product id
	 * @param array or int $product_id
	 * @return array
	 */
	public function getProductFilters($product_id=null){
		$filters = F0FTable::getAnInstance('ProductFilter', 'J2StoreTable')->getFiltersByProduct($product_id);
		return $filters;
	}

	public function getVendors($vendor_ids=''){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->select('v.*');
		$query->from("#__j2store_vendors as v");
		$query->leftJoin('#__j2store_addresses as a ON v.address_id = a.j2store_address_id');
		if($vendor_ids){
			$query->where('v.j2store_vendor_id IN ('.$vendor_ids.') ');
		}
        $query->where('v.enabled=1');
        $query->order('v.ordering ASC');
		$db->setQuery($query);
		return  $db->loadObjectList();
	}


	/** the following section is specially created for product layouts. And it only works with Joomla articles */

	public function getSFProducts() {
		if (empty($this->_sflist))
		{
			$query = $this->getSFQuery();
			$this->getState('list.limit');
			$this->_sflist = $this->_getSFList((string) $query, $this->getStart(), $this->getState('list.limit'));
		}
		return $this->_sflist;
	}

	public function getSFAllProducts(){
		if(empty($this->_sfalllist)){
			$query = $this->getSFQuery();
			$query->clear('select')->clear('order')->clear('limit')->select('#__j2store_products.j2store_product_id');
			$this->_sfalllist = $this->_getSFList((string) $query);
		}
		return $this->_sfalllist;
	}
	/**
	 * Returns an object list
	 *
	 * @param   string   $query       The query
	 * @param   integer  $limitstart  Offset from start
	 * @param   integer  $limit       The number of records
	 * @param   string   $group       The group by clause
	 *
	 * @return  array  Array of objects
	 */
	protected function _getSFList($query, $limitstart = 0, $limit = 0, $group = '')
	{
		try {
			$this->_db->setQuery($query, $limitstart, $limit);
			$result = $this->_db->loadObjectList($group);
		} catch (Exception $e) {
			$result = array();
		}
		return $result;
	}


	public function getSFPagination()
	{

		if (empty($this->_sfpagination))
		{
			// Import the pagination library
			JLoader::import('joomla.html.pagination');
			// Prepare pagination values
			$total = $this->getSFPageTotal();
			//echo $this->getStart();
			// Create the pagination object
			$this->_sfpagination = new JPagination($total, $this->getStart(), $this->getState('list.limit'));
		}
		return $this->_sfpagination;
	}


	/**
	 * Get the number of all items
	 *
	 * @return  integer
	 */
	public function getSFPageTotal()
	{
		if (is_null($this->_sfpagetotal))
		{
			//var_dump(debug_backtrace());
			$query = $this->getSFQuery();
			$query = clone $query;
			$query->clear('select')->clear('order')->clear('limit')->select('COUNT(*)');
			/* if ($query instanceof JDatabaseQuery
			&& $query->type == 'select')
			{
				$query = clone $query;
				$query->clear('select')->clear('order')->clear('limit')->select('COUNT(*)');

				$this->_db->setQuery($query);
				$this->_sfpagetotal = (int) $this->_db->loadResult();
			} else { */

			// Otherwise fall back to inefficient way of counting all results.
			try {
				$this->_db->setQuery($query);
				$this->_db->execute();
				$this->_sfpagetotal = (int) $this->_db->getNumRows();
			}catch (Exception $e) {
				$this->_sfpagetotal = 0;
			}
			//}

		}
		return $this->_sfpagetotal;
	}

	public function getStart()
	{
		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getSFPageTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		return $start;
	}


	/**
	 * Method the master query for retrieving a list of products. This works only for product layouts and Joomla articles alone.
	 * @return JDatabaseQuery
	 */

	public function getSFQuery() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.title as product_name, a.alias, a.introtext, a.fulltext, ' .
				'a.checked_out, a.checked_out_time, ' .
				'a.catid, a.created, a.created_by, a.created_by_alias, ' .
				// Use created if modified is 0
				'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified, ' .
				'a.modified_by, uam.name as modified_by_name,' .
				// Use created if publish_up is 0
				'CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END as publish_up,' .
				'a.publish_down, a.images, a.urls, a.attribs, a.metadata, a.metakey, a.metadesc, a.access, ' .
				'a.hits, a.featured, a.language, ' . ' ' . $query->length('a.fulltext') . ' AS readmore'
			)
		);
		$query->from('#__content AS a');
		$query->select ( 'tag.tag_id' )
			->join ( 'LEFT', '#__contentitem_tag_map AS tag ON a.id= tag.content_item_id'  );
		// Join over the categories.
		$query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
			->select("ua.email AS author_email")

			->join('LEFT', '#__users AS ua ON ua.id = a.created_by')
			->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Join over the categories to get parent category titles
		$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
			->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

		// Join on voting table
		$query->select('ROUND(v.rating_sum / v.rating_count, 0) AS rating, v.rating_count as rating_count')
			->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

		// Join to check for category published state in parent categories up the tree
		$query->select('c.published, CASE WHEN badcats.id is null THEN c.published ELSE 0 END AS parents_published');
		$subquery = 'SELECT cat.id as id FROM #__categories AS cat JOIN #__categories AS parent ';
		$subquery .= 'ON cat.lft BETWEEN parent.lft AND parent.rgt ';
		$subquery .= 'WHERE parent.extension = ' . $db->quote('com_content');

		// Find any up-path categories that are not published
		// If all categories are published, badcats.id will be null, and we just use the article state
		$subquery .= ' AND parent.published != 1 GROUP BY cat.id ';
		// Select state to unpublished if up-path category is unpublished
		$publishedWhere = 'CASE WHEN badcats.id is null THEN a.state ELSE 0 END';

		$query->join('LEFT OUTER', '(' . $subquery . ') AS badcats ON badcats.id = c.id');


		$published = 1;
		// Use article state if badcats.id is null, otherwise, force 0 for unpublished
		$query->where($publishedWhere . ' = ' . $db->q((int) $published));


		$this->_sfBuildQueryJoins($query);
		$this->_sfBuildWhereQuery($query);
		$this->_sfBuildQueryOrderBy($query);
		$query->group('a.id');
		$query->group('#__j2store_products.j2store_product_id');
		J2Store::plugin ()->event ( 'AfterSFProductListQuery', array(&$query) );

		return $query;
	}

	public function _sfBuildQueryJoins(&$query) {
		$db = $this->getDbo();
		$query->select('#__j2store_products.*');
		$query->join('INNER', '#__j2store_products ON #__j2store_products.product_source='.$db->q('com_content').' AND #__j2store_products.product_source_id = a.id');
		$query->select('#__j2store_variants.price');
		$query->select('#__j2store_variants.sku');
		$query->select('#__j2store_variants.upc');
		$query->select('#__j2store_variants.manage_stock');
		$query->select('#__j2store_variants.availability');
		$query->join('INNER', '#__j2store_variants ON #__j2store_products.j2store_product_id=#__j2store_variants.product_id');

        $query->select('#__j2store_productimages.thumb_image, #__j2store_productimages.main_image, #__j2store_productimages.additional_images,#__j2store_productimages.thumb_image_alt,#__j2store_productimages.main_image_alt,#__j2store_productimages.additional_images_alt');
		$query->join('LEFT OUTER', '#__j2store_productimages ON #__j2store_products.j2store_product_id=#__j2store_productimages.product_id');

		$query->select($this->_db->qn('#__j2store_productquantities').'.j2store_productquantity_id ')
			->select($this->_db->qn('#__j2store_productquantities').'.quantity')
			->join('LEFT OUTER','#__j2store_productquantities ON #__j2store_productquantities.variant_id = #__j2store_variants.j2store_variant_id');

		//for variable product
		$query->join('LEFT OUTER', '#__j2store_productprice_index ON  #__j2store_products.j2store_product_id=#__j2store_productprice_index.product_id');

		$query->select('CASE #__j2store_products.product_type
							WHEN "variable" THEN
							  #__j2store_productprice_index.min_price
							ELSE
								#__j2store_variants.price
							END as min_price
				');

		$query->select('CASE #__j2store_products.product_type
							WHEN "variable" THEN
							  #__j2store_productprice_index.max_price
							ELSE
								#__j2store_variants.price
							END as max_price
				');

		//filters
		$query->select('#__j2store_product_filters.filter_id');
		$query->join('LEFT OUTER', '#__j2store_product_filters ON #__j2store_products.j2store_product_id=#__j2store_product_filters.product_id');

		$query->join('LEFT OUTER', '#__j2store_manufacturers ON #__j2store_products.manufacturer_id=#__j2store_manufacturers.j2store_manufacturer_id');
		$query->select('#__j2store_addresses.company as brand_name');
		$query->join('LEFT OUTER', '#__j2store_addresses ON #__j2store_manufacturers.address_id=#__j2store_addresses.j2store_address_id');
//address_id

	}

	/**
	 * Method to build where query based on the filters
	 * @param string $query
	 */
	function _sfBuildWhereQuery(&$query){
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$db = $this->getDbo();
		//$state = $this->getFilterValues();
		$state = $this->getSFFilterValues();

		// Filter by a single or group of categories
		//$categoryId = $this->getState('catids');
		$tagid = $this->getState('tagid');

		if (is_numeric($tagid))
		{
			// Add subcategory check
			$includeSubtags = $this->getState('filter.subtags', false);
			$tag_where = '(tag.tag_id ='.$db->q($tagid);
			$tag_alias = ' AND tag.type_alias ='.$db->q ( 'com_content.article' ).')';
			if($includeSubtags){
				$levels = (int) $this->getState('filter.max_tag_levels', '1');
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true)
					->select('sub.id')
					->from('#__tags as sub')
					->join('INNER', '#__tags as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
					->where('this.id = ' . $db->q((int) $tagid));

				if ($levels >= 0)
				{
					$subQuery->where('sub.level <= this.level + ' . $levels);
				}
				// Add the subquery to the main query
				$query->where('(' . $tag_where . ' OR (tag.tag_id IN (' . $subQuery->__toString() . ')'.$tag_alias.'))');
			}else{
				//tag query
				$query->where($tag_where.$tag_alias);
			}
		}

		//access
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('a.access IN (' . $groups . ')')
			->where('c.access IN (' . $groups . ')');


		// Define null and now dates
		$nullDate	= $db->quote($db->getNullDate());
		//$nowDate	= $db->quote(JFactory::getDate()->toSql());
		$tz = JFactory::getConfig()->get('offset');
		$date = JFactory::getDate('now', $tz);

		//default to the sql formatted date
		$nowDate = $db->quote( $date->toSql());
        $query	->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.' OR a.publish_up IS NULL)')
            ->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.' OR a.publish_down IS NULL)');
		// Filter by language
		if ($this->getState('filter.language'))
		{
			$lang_tag = $this->getState('lang_tag', JFactory::getLanguage()->getTag());
			$query->where('a.language in (' . $db->quote($lang_tag) . ',' . $db->quote('*') . ')');
		}


		$query->where(
			$db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1)
		);

		if($state->search){
			$query->where(
				' ( '.
				'a.'.$db->qn('title').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
				'a.'.$db->qn('introtext').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
				'a.'.$db->qn('fulltext').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
				$db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
				$db->qn('#__j2store_products').'.'.$db->qn('product_source').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
				$db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE '.$db->q('%'.$state->search.'%').'AND  a.state =1 OR '.
				$db->qn('#__j2store_variants').'.'.$db->qn('upc').' LIKE '.$db->q('%'.$state->search.'%').'AND  a.state =1 OR '.
				$db->qn('#__j2store_variants').'.'.$db->qn('price').' LIKE '.$db->q('%'.$state->search.'%').'AND  a.state =1 OR '.
				$db->qn('#__j2store_products').'.'.$db->qn('product_type').' LIKE '.$db->q('%'.$state->search.'%')
				.' ) '
			) ;

		}
		if($state->product_type) {
			$query->where(
				$db->qn('#__j2store_products').'.'.$db->qn('product_type').' LIKE '.
				$db->q('%'.$state->product_type.'%')
			);
		}
		//since
		$since = trim($state->since);
		if(empty($since) || ($since == '0000-00-00') || ($since == '0000-00-00 00:00:00')) {
			$since = '';
		} else {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $since)) {
				$since = '2001-01-01';
			}
			$jFrom = new JDate($since);
			$since = $jFrom->toUnix();
			if($since == 0) {
				$since = '';
			} else {
				$since = $jFrom->toSql();
			}
			// Filter from-to dates
			$query->where(
				$db->qn('#__j2store_products').'.'.$db->qn('created_on').' >= '.
				$db->q($since)
			);
		}

		// "Until" queries
		$until = trim($state->until);
		if(empty($until) || ($until == '0000-00-00') || ($until == '0000-00-00 00:00:00')) {
			$until = '';
		} else {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $until)) {
				$until = '2037-01-01';
			}
			$jFrom = new JDate($until);
			$until = $jFrom->toUnix();
			if($until == 0) {
				$until = '';
			} else {
				$until = $jFrom->toSql();
			}
			$query->where(
				$db->qn('#__j2store_products').'.'.$db->qn('created_on').' <= '.
				$db->q($until)
			);
		}

		if($state->manufacturer_id){
			$query->where($db->qn('#__j2store_products').'.'.$db->qn('manufacturer_id').' IN ('.$state->manufacturer_id.')') ;
		}

		if($state->vendor_id) {
			$query->where($db->qn('#__j2store_products').'.'.$db->qn('vendor_id').' IN ('.$state->vendor_id.')');
		}

		if($state->taxprofile_id) {
			$query->where($db->qn('#__j2store_products').'.'.$db->qn('taxprofile_id').'='.$db->q($state->taxprofile_id));
		}

		if(!is_null($state->visible) &&  !empty($state->visible)) {
			$query->where($db->qn('#__j2store_products').'.'.$db->qn('visibility').'='.$db->q($state->visible));
		}

		if(!is_null($state->enabled) &&  !empty($state->enabled)) {
			$query->where($db->qn('#__j2store_products').'.'.$db->qn('enabled').' IN ('.$state->enabled.')');
		}

		if(!is_null($state->sku) && !empty($state->sku)){
			$query->where($db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE ('.$db->q('%'.$state->sku.'%').')');
		}


		// filter price range
		if (!is_null($state->pricefrom ) && ($state->pricefrom >=0 || !empty($state->pricefrom )) && !is_null($state->priceto ) && !empty($state->priceto)  )
		{
			$variant_pricerange_qry = '';
			$variant_pricerange_qry .= '(price >= '.( int ) $state->pricefrom.') ' ;
			$variant_pricerange_qry .= ' AND ';
			$variant_pricerange_qry .= '(price <= '.( int ) $state->priceto.') ' ;

			$query->where( '#__j2store_products.j2store_product_id in ( select distinct product_id from #__j2store_variants where '
				. $variant_pricerange_qry .' )' );
		}

		if (! is_null ( $state->productfilter_id ) && ! empty ( $state->productfilter_id )) {
			if (! is_array ( $state->productfilter_id )) {
				$filter_ids = ( array ) $state->productfilter_id;
			} else {
				$filter_ids = $state->productfilter_id;
			}
			//get the filter condition
			$filter_condition = $session->get('list_product_filter_search_logic_rel', 'OR', 'j2store');
			if ($filter_condition == 'AND') {
				$count_ids = 0;
				$filter_all_ids = array ();
				foreach ( $filter_ids as $k => $ids ) {
                    if (!empty($ids)) {
						$arr_ids = explode ( ',', $ids );
						$filter_all_ids = array_merge ( $arr_ids, $filter_all_ids );
					}
				}
				$filter_all_ids = array_unique ( $filter_all_ids );
				$count_ids = count ( $filter_all_ids );

				if (is_array ( $filter_ids )) {
					$query->where ( '#__j2store_product_filters.product_id IN (SELECT product_id FROM #__j2store_product_filters WHERE filter_id IN (' . implode ( ',', $filter_all_ids ) . ') GROUP BY product_id HAVING COUNT(*) = ' . $count_ids . ')' );
				}
			} else {
				$query->where ( '#__j2store_product_filters.filter_id IN (' . implode ( ',', $filter_ids ) . ')' );
			}
		}

		if(count ( $state->product_types ) > 0){
			if (! is_array ( $state->product_types )) {
				$product_types = ( array ) $state->product_types;
			} else {
				$product_types = $state->product_types;
			}
			$query->where ( '#__j2store_products.product_type IN (\'' . implode ( '\',\'', $product_types ) . '\')' );
		}

		if(!is_null ( $state->show_feature_only ) && !empty( $state->show_feature_only )){
			$query->where($db->qn('a').'.'.$db->qn('featured').' = '.$db->q($state->show_feature_only));
		}
	}

	/**
	 * Method to build orderby query
	 * @param string $query
	 */
	protected function _sfBuildQueryOrderBy(&$query){

		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$this->_sfBuildSortQuery($query);

		$params = $this->getMergedParams();
		$article_category_ordering = $params->get('consider_category',0);
		$articleOrderby		= $params->get('orderby_sec', 'rdate');
		$articleOrderDate	= $params->get('order_date');
		$secondary			= $this->orderbySecondary($articleOrderby, $articleOrderDate);
		if(empty( $secondary )){
			$orderby = 'a.created ';
		}else{
			$orderby = trim ( $secondary );
			if($article_category_ordering){
				$query->order('category_title'.' '.$this->getState('list.direction', 'ASC'));
			}
		}

		$this->setState('list.ordering', $orderby);

		$query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'ASC'));

	}

	function _sfBuildSortQuery(&$query) {
		$db = $this->_db;
		$state = $this->getFilterValues ();

		if ($state->sortby) {
			$sortby = '';
			switch ($state->sortby) {
				case 'pname' :
					$sortby = 'product_name ASC';
					break;

				case 'rpname' :
					$sortby = 'product_name DESC';
					break;

				case 'min_price' :
					$sortby = 'min_price ASC';
					break;
				case 'rmin_price' :
					$sortby = 'min_price DESC';
					break;
				case 'sku' :
					$sortby = '#__j2store_variants.sku ASC';
					break;

				case 'rsku' :
					$sortby = '#__j2store_variants.sku DESC';
					break;

				case 'brand' :
					$sortby = 'brand_name ASC';
					break;

				case 'rbrand' :
					$sortby = 'brand_name DESC';
					break;

			}
			if(!empty($sortby)) {
				$query->order ( $sortby );
			}
		}
	}


	public function orderbySecondary($orderby, $orderDate = 'created')
	{
		$queryDate = $this->getQueryDate($orderDate);

		switch ($orderby)
		{
			case 'date' :
				$orderby = $queryDate;
				break;

			case 'rdate' :
				$orderby = $queryDate . ' DESC ';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits DESC';
				break;

			case 'rhits' :
				$orderby = 'a.hits';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'a.author';
				break;

			case 'rauthor' :
				$orderby = 'a.author DESC';
				break;

			case 'front' :
				$orderby = 'a.featured DESC';
				break;


			default :
				$orderby = 'a.ordering';
				break;
		}

		return $orderby;
	}


	public function getQueryDate($orderDate)
	{
		$db = JFactory::getDbo();

		switch ($orderDate)
		{
			case 'modified' :
				$queryDate = ' CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END';
				break;

			// use created if publish_up is not set
			case 'published' :
				$queryDate = ' CASE WHEN a.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.publish_up END ';
				break;

			case 'created' :
			default :
				$queryDate = ' a.created ';
				break;
		}

		return $queryDate;
	}

	function get_filter_fields() {
		return array(
			'a.ordering',
			'a.title',
			'product_name',
			'#__j2store_products.manufacturer_id',
			'#__j2store_products.vendor_id',
			'#__j2store_variants.sku'
		);
	}


	/**
	 * Rounding of the the nearest 10s /100s/1000s/ etc depending on the number of digits
	 * @param int $price - price of product
	 * @param int $digit - how many digit to round
	 * @param boolean $up - to round upward
	 * @return int
	 */
	protected function _priceRound( $price , $digit='100', $up = false )
	{
		//based o the digit have to calculate the price
		$price = ( (int) ( $price/$digit) ) * $digit;

		if( $up )
		{
			$price = $price + $digit;
		}

		return (int) $price;
	}

	public function executePlugins(&$item, $params=null, $context='com_j2store') {

		if(!isset($params) || !$params instanceof JRegistry) {
			$params = JComponentHelper::getParams('com_content');
		}

		$item->event   = new stdClass;
        $app = J2Store::platform()->application();
		//$dispatcher = JEventDispatcher::getInstance();

		// Old plugins: Ensure that text property is available
		if (!isset($item->text))
		{
			$item->text = $item->introtext;
		}

		JPluginHelper::importPlugin('content');
        $app->triggerEvent('onContentPrepare', array ('com_content.category.productlist', &$item, &$params, 0));
		// Old plugins: Use processed text as introtext
		$item->introtext = $item->text;

		$results = $app->triggerEvent('onContentAfterTitle', array('com_content.category.productlist', &$item, &$params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $app->triggerEvent('onContentBeforeDisplay', array('com_content.category.productlist', &$item, &$params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $app->triggerEvent('onContentAfterDisplay', array('com_content.category.productlist', &$item, &$params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));
	}

	public function runPrepareEventOnDescription($description, $id,$params, $context) {

		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('content');
		$item = new JObject();
		$item->id = $id;
		$item->text = $description;
        J2Store::plugin ()->event ( 'ContentPrepare',array ($context, &$item, &$params, 0) );
		//run content plugins for short and long description
		$app->triggerEvent('onContentPrepare', array ($context, &$item, &$params, 0));
		return $item->text;
	}


	/**
	 * Method to get Filter Values for SFBuildWhereQuery
	 * @return StdClass
	 */
	private function getSFFilterValues()
	{
		return (object)array(
			'search' 			=>	$this->getState('search',null,'string'),
			'product_ids' 		=> 	$this->getState('product_ids',null,'string'),
			'product_type' 		=> 	$this->getState('product_type',null,'string'),
			'visible' 			=>  $this->getState('visible',null,'string'),
			'vendor_id' 		=>  $this->getState('vendor_id',null,'string'),
			'manufacturer_id' 	=>  $this->getState('manufacturer_id',null,'string'),
			'productid_from' 	=>  $this->getState('productid_from',null,'int'),
			'productid_to' 		=>  $this->getState('productid_to',null,'int'),
			'pricefrom' 		=>  $this->getState('pricefrom',null,'int'),
			'priceto' 			=> 	$this->getState('priceto',null,'int'),
			'since' 			=>  $this->getState('since',null,'string'),
			'until' 			=>  $this->getState('until',null,'string'),
			'taxprofile_id'     =>  $this->getState('taxprofile_id',null,'int'),
			'enabled'     		=>  $this->getState('enabled',null,'string'),
			'shippingmethod'	=>  $this->getState('shippingmethod',null,'int'),
			'sku'				=>  $this->getState('sku',null,'string'),
			'tagid'			=>  $this->getState('tagid',null,'array'),
			'sortby'			=>  $this->getState('sortby',null,'string'),
			'instock'			=>  $this->getState('instock',null,'int'),
			'productfilter_id'	=>  $this->getState('productfilter_id',null,'string'),
			'product_types'		=>  $this->getState('product_types',array()),
			'show_feature_only' => $this->getState('show_feature_only',null,'int')
		);
	}

	public function getProductsBySource($source, $source_id) {
		if (empty ( $source ) || empty ( $source_id ))
			return array ();

		static $source_sets;
		if (! is_array ( $source_sets )) {
			$source_sets = array ();
		}

		if (! isset ( $source_sets [$source] [$source_id] )) {
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true )->select ( '*' )->from ( '#__j2store_products' )->where ( $db->qn ( 'product_source' ) . ' = ' . $db->q ( $source ) )->where ( $db->qn ( 'product_source_id' ) . ' = ' . $db->q ( $source_id ) );
			$db->setQuery ( $query );
			$source_sets [$source] [$source_id] = $db->loadObjectList ();
		}
		return $source_sets [$source] [$source_id];
	}


	/**
	 * Method to fetch the Brands based on the product
	 * @param mixed int|array $product_id
	 * @return array brands
	 */
	public function getManfucaturersByProduct($product_id=null) {
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('p.*')->from('#__j2store_products AS p');
		$query->select('m.*')
			->leftJoin('#__j2store_manufacturers as m ON m.j2store_manufacturer_id =p.manufacturer_id');
		if(isset($product_id)) {
			$search_product_ids ='';
			if(is_array($product_id)){
				$search_product_ids = implode(',',$product_id);
			} elseif (is_numeric($product_id)) {
				$search_product_ids = ($product_id >0)?$product_id:'';
			}

			if(!is_null($search_product_ids) && !empty($search_product_ids)) {
				$query->where('p.j2store_product_id IN ('. $search_product_ids.')');

			}
		}
		$query->where('p.manufacturer_id=m.j2store_manufacturer_id');
        $query->where('m.enabled=1');
		$query->select('a.*')
			->leftJoin('#__j2store_addresses as a ON a.j2store_address_id=m.address_id');
		$query->group('p.manufacturer_id');
		$query->order('m.ordering ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	/**
	 * Method to return list of all manufacturers
	 */
	public function getManufacturers(){
        $model = F0FModel::getTmpInstance('Manufacturers','J2StoreModel');
        $model->setState('filter_enabled',1);
        $model->setState('filter_order','ordering');
        $model->setState('filter_order_Dir','asc');
        return $model->getList();
	}

	/**
	 * Method to fetch the Brands based on the product
	 * @param mixed int|array $product_id
	 * @return array brands
	 */
	public function getVendorsByProduct($product_id=null) {
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('p.*')->from('#__j2store_products AS p');
		$query->select('v.*')
			->leftJoin('#__j2store_vendors as v ON v.j2store_vendor_id =p.vendor_id');
		if(isset($product_id)) {
			$search_product_ids ='';
			if(is_array($product_id)){
				$search_product_ids = implode(',',$product_id);
			} elseif (is_numeric($product_id)) {
				$search_product_ids = ($product_id >0)?$product_id:'';
			}

			if(!is_null($search_product_ids) && !empty($search_product_ids)) {
				$query->where('p.j2store_product_id IN ('. $search_product_ids.')');

			}
		}
		$query->where('p.vendor_id=v.j2store_vendor_id');
        $query->where('v.enabled=1');
		$query->select('a.*')
			->leftJoin('#__j2store_addresses as a ON a.j2store_address_id=v.address_id');
		$query->group('p.vendor_id');
		$query->order('v.ordering ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}


}