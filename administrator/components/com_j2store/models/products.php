<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';
class J2StoreModelProducts extends F0FModel {

    protected  $_rawData = null;
    protected $default_behaviors = array('filters', 'default');
    protected $_productlist = array();
    protected $productpagination = null;
    protected $productpagetotal = null;

    protected $_sflist = array();
    protected $_sfpagination = null;
    protected $_sfpagetotal = null;
    protected $_sfalllist = null;


    //events
    /**
     * The event to trigger after deleting the data.
     * @var    string
     */
    protected $event_after_delete = 'onJ2StoreProductAfterDelete';

    /**
     * The event to trigger after saving the data.
     * @var    string
     */
    protected $event_after_save = 'onJ2StoreProductAfterSave';

    /**
     * The event to trigger before deleting the data.
     * @var    string
     */
    protected $event_before_delete = 'onJ2StoreProductBeforeDelete';

    /**
     * The event to trigger before saving the data.
     * @var    string
     */
    protected $event_before_save = 'onJ2StoreProductBeforeSave';

    /**
     * The event to trigger after changing the published state of the data.
     * @var    string
     */
    protected $event_change_state = 'onJ2StoreProductChangeState';


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


    protected function onAfterGetItem(&$record) {
        if(!empty($record->product_type)) {
            $this->addBehavior($record->product_type);
        }else {
            $this->addBehavior('simple');
        }
        return parent::onAfterGetItem($record);
    }

    protected function onBeforeSave(&$data, &$table) {
        // validate the checkbox fields before save
        $checkbox_fields = array('use_store_config_notify_qty' , 'use_store_config_max_sale_qty' , 'use_store_config_min_sale_qty') ;
        foreach ($checkbox_fields as $field) {
            if ( !isset( $data[$field] ) ) {
                $data[$field] = 0; // force zero since the field is unchecked
            }
        }
        if(!empty($table->product_type)) {
            $this->addBehavior($table->product_type);
        }elseif(isset($data['product_type']) && $data['product_type']) {
            $this->addBehavior($data['product_type']);
        }else {
            $this->addBehavior('simple');
        }
        return parent::onBeforeSave($data, $table);
    }

    protected function onAfterSave(&$table) {
        if(!empty($table->product_type)) {
            $this->addBehavior($table->product_type);
        }else {
            $this->addBehavior('simple');
        }

        JPluginHelper::importPlugin('j2store');
        return parent::onAfterSave($table);
    }

    protected function onBeforeDelete(&$id, &$table) {
        // the table here will be empty. So we have to load the product first before we check the product type
        $product_type = '';
        if($id && (int) $id > 0) {
            $product = F0FTable::getInstance('Product', 'J2StoreTable')->getClone();
            if ($product->load($id)) {
                $product_type = $product->product_type;
            }
        }
        if(!empty($product_type)) {
            $this->addBehavior($product_type);
        }else {
            $this->addBehavior('simple');
        }
        return parent::onBeforeDelete($id, $table);
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
            // Oops, an exception occured!
            $this->setError($e->getMessage());
        }

        try
        {
            // Call the behaviors
            $result = $this->modelDispatcher->trigger('onAfterGetProduct', array(&$ref_model, &$product));

        }
        catch (Exception $e)
        {
            // Oops, an exception occured!
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
            // Oops, an exception occured!
            $this->setError($e->getMessage());
            return false;
        }
        //plugin trigger
        $result['afterDisplayPrice'] = J2Store::plugin()->eventWithHtml('AfterUpdateProduct', array($data));
        J2Store::plugin()->event('UpdateProductResponse',array(&$result,$ref_model,$product));
        return $result;

    }

    public function getProductTypes() {

        $types = array(
            'simple'=>JText::_('J2STORE_PRODUCT_TYPE_SIMPLE'),
            'variable'=>JText::_('J2STORE_PRODUCT_TYPE_VARIABLE'),
            'configurable'=>JText::_('J2STORE_PRODUCT_TYPE_CONFIGURABLE'),
            'downloadable'=>JText::_('J2STORE_PRODUCT_TYPE_DOWNLOADABLE')
        );

        //allow plugins to add product types
        J2Store::plugin()->event('GetProductTypes', array(&$types));
        return $types;
    }

    function getGlobalOptions($product_type){

        //$items = F0FModel::getTmpInstance('Options','J2StoreModel')->enabled(1)
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')->from("#__j2store_options");

        //based on the product type
        if(isset($product_type) && $product_type =='variable'){
            $query->where("type IN ('select' , 'radio')");
        }

        $db->setQuery($query);
        $items = $db->loadObjectList();
        $result = array();
        foreach($items as $item){
            $result[$item->j2store_option_id] = $item->option_name;
        }
        return $result;
    }


    /**
     * Method to get array
     * @param unknown_type $view
     * @param unknown_type $key
     * @param unknown_type $value
     * @param unknown_type $value1
     * @return multitype:string NULL
     */
    function getSelectArrayOptions($view,$key,$value,$value1=''){
        $items =F0FModel::getTmpInstance(ucfirst($view),'J2storeModel')->enabled(1)->getList();
        $result = array();
        $result[''] = JText::_('J2STORE_SELECT_OPTION');
        foreach($items as $item){
            $result[$item->$key] = $item->$value;
            if(isset($value1) && !empty($value1)){
                $result[$item->$key] = $item->$value . $item->$value1;
            }
        }
        return $result;
    }

    /**
     * Method to get Related Products
     * @param unknown_type $product_id
     */
    function getRelationalProducts($product_id ,$q){

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('p.j2store_product_id')->from("#__j2store_products as p");
        $query->select('a.title as product_name');
        $query->leftJoin('#__content as a ON a.id = p.product_source_id');
        $query->where('a.title LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false ));
        $query->where('j2store_product_id !=' .$db->q($product_id));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }

    function getProductPrices($product_id){
        $variant = F0FTable::getInstance('Variants','J2StoreTable');
        $variant->load(array('product_id'=>$product_id));
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('p.*')->from("#__j2store_product_prices as p");
        $query->where('p.variant_id =' .$db->q($variant->j2store_variant_id));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;
    }


    /**
     * Method to delete product file
     * @param int $file_id
     * @param int $variant_id
     * @return boolean
     */
    function deleteProductFile($file_id,$product_id){
        $result = true;
        if(isset($file_id)){
            $pfile = F0FTable::getAnInstance('Productfile' ,'J2StoreTable');
            if($pfile->load($file_id)){
                if($pfile->product_id == $product_id){
                    if(!$pfile->delete($file_id)) $result = false;
                }
            }
        }
        return $result;
    }


    function getFilesData()
    {
        $files = array();
        $folder = $this->getCategoryFolder();
        if(empty($folder)) return $files;
        $potentialPrefix = substr($folder, 0, 5);
        $potentialPrefix = strtolower($potentialPrefix);
        $useS3 = $potentialPrefix == 's3://';

        JLoader::import('joomla.filesystem.folder');
        $path = JPATH_ROOT.'/'.$folder;
        $files = JFolder::files(JPath::clean($folder));

        return $files;
    }

    function getFolders()
    {
        $folders = array();
        $folder = $this->getCategoryFolder();
        if(empty($folder)) return $folders;

        $potentialPrefix = substr($folder, 0, 5);
        $potentialPrefix = strtolower($potentialPrefix);
        $useS3 = $potentialPrefix == 's3://';

        JLoader::import('joomla.filesystem.folder');
        $path = JPATH_ROOT.'/'.$folder;
        $folders = JFolder::folders(JPath::clean($folder));

        return $folders;
    }


    function getCategoryFolder()
    {
        $params = J2Store::config();
        static $folder = null;
        if(empty($folder))
        {

            $category = $params->get('attachmentfolderpath');
            if(empty($category)) {
                $folder = '';
            } else {
                $folder = $category;

                JLoader::import('joomla.filesystem.folder');
                if(!JFolder::exists($folder))
                {
                    $folder = JPATH_ROOT.'/'.$folder;
                    if(!JFolder::exists($folder))
                    {
                        $folder = '';
                    }
                }
            }

            if(empty($folder)) return $folder;

            $subfolder = $this->getState('folder','');
            if(!empty($subfolder))
            {
                // Clean and check subfolder
                $subfolder = JPath::clean($subfolder);
                if (strpos($subfolder, '..') !== false) {
                    J2Store::platform()->raiseError(20,'Use of relative paths not permitted');
                    exit;
                }
                // Find the parent path to our subfolder
                $parent = JPath::clean( @realpath($folder.'/'.$subfolder.'/..') );
                $parent = trim( str_replace(JPath::clean($folder), '', $parent) , '/\\' );
                $folder = JPath::clean ( $folder . '/' . $subfolder );

                // Calculate the full path to the subfolder
                $this->setState ( 'parent', $parent );
                $this->setState ( 'folder', $subfolder );
            } else
            {
                $this->setState('parent',null);
                $this->setState('folder','');
            }
        }
        return $folder;

    }


    /**
     * Method to generate variants
     * @return array Result array
     */

    function generateVariants() {

        $results = array();
        $app = JFactory::getApplication();

        //get the product ID from POST
        $product_id = $this->input->getInt('product_id', 0);

        //generate variant combinations
        $variants = $this->getVariants($product_id);
        $plugin_helper = J2Store::plugin ();
        if(count($variants)) {
            //we have variants. Start creating a variant product


            //TODO: trigger a plugin event

            foreach($variants as $variant) {

                unset($variantTable );
                $variantTable = F0FTable::getAnInstance('Variants', 'J2StoreTable')->getClone();

                //first create the variant

                //this is not a master table
                $variantTable->is_master = 0;
                $variantTable->product_id = $product_id;


                //allow plugins to modify the output
                $plugin_helper->event ( 'BeforeVariantGeneration',array(&$variantTable) );
                //$app->triggerEvent('onJ2StoreBeforeVariantGeneration', array(&$variantTable));

                //store the data
                $variantTable->store();

                //allow plugins to modify the output
                $plugin_helper->event ( 'AfterVariantGeneration',array(&$variantTable) );
                //$app->triggerEvent('onJ2StoreAfterVariantGeneration', array(&$variantTable));

                //get the last stored variant id
                $variant_id = $variantTable->getId();

                $db = JFactory::getDbo();
                $columns = array('variant_id', 'product_optionvalue_ids');
                $fields = array ($variant_id, $db->q($variant));

                unset($table);
                $table = F0FTable::getInstance('ProductVariantoptionvalue', 'J2StoreTable')->getClone();
                if($table->load(array('variant_id'=>$variant_id)) ) {
                    $query = $db->getQuery(true)->update($db->qn('#__j2store_product_variant_optionvalues'))
                        ->set($db->qn('product_optionvalue_ids').' = '.$db->q($variant))
                        ->where($db->qn('variant_id').' = '.$db->q($variant_id));
                } else {
                    $query = $db->getQuery(true)->insert($db->qn('#__j2store_product_variant_optionvalues'))
                        ->columns($columns)
                        ->values(implode(',', $fields));
                }
                $db->setQuery($query)->execute();
            }
        }
        return $results;
    }

    function getVariants($product_id) {

        $return = array( );
        $traits = array( );
        $result = array( );


        if($product_id ) {

            $traits = $this->getTraits($product_id);
            if(!empty( $traits )){
                $return = $this->getCombinations($traits);
            }
            // before returning them, loop through each record and sort them
            $result = array( );
            foreach ( $return as $csv )
            {
                $values = explode( ',', $csv );
                sort( $values );
                $result[] = implode( ',', $values );
            }

        }
        return $result;
    }

    public function getTraits($product_id, $product_options=array()) {

        //load product to get product type
        $product = $this->getItem($product_id);

        $traits = array();

        if(count($product_options) < 1) {

            if($product->product_type == 'advancedvariable'){

                //first get all the product options related to this master variant
                $product_options = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel')
                    ->product_id($product_id)
                    ->limit(0)
                    ->is_variant(1)
                    ->limitstart(0)
                    ->getList();
            }else{
                //first get all the product options related to this master variant
                $product_options = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel')
                    ->product_id($product_id)
                    ->limit(0)
                    ->limitstart(0)
                    ->getList();
            }
        }
        if($product_options) {
            foreach ( $product_options as $productoption)
            {

                //based on the option id, fetch the optionvalues
                $optionvalues = F0FModel::getTmpInstance('ProductOptionValues', 'J2StoreModel')->productoption_id($productoption->j2store_productoption_id)->getList();
                if ( $optionvalues )
                {
                    $optionIDs = array( );
                    foreach ( $optionvalues as $ov )
                    {
                        $optionIDs[] = $ov->j2store_product_optionvalue_id;
                    }
                    $traits[] = $optionIDs;
                }
            }

        }
        return $traits;
    }

    public function getCombinations($traits)
    {
        $result = array();
        $max_attribute_combination=1;
        foreach ( $traits as $trait )
        {
            $max_attribute_combination=$max_attribute_combination*count($trait);
        }
        for($i=0;$i<$max_attribute_combination;$i++)
        {
            $output="";
            $quotient = $i;

            foreach ( array_reverse($traits) as $trait )
            {
                $divisor=count($trait);
                $remainder = $quotient % $divisor ;
                $quotient = $quotient / $divisor ;
                $output= $trait[$remainder].','.$output;
            }
            $result[]=trim($output,",");
        }
        return $result;
    }


    public function getCategories($cat){
        //get the db object
        $db = JFactory::getDbo();

        if($cat){
            $selected_cat = implode(',',$cat);
        }
        //get the query
        $query = $db->getQuery(true);
        // query to fetch all data
        $query->select('*');
        $query->from('#__categories');
        $query->where('extension ='.$db->quote('com_content'));
        if(isset($cat) && !empty($cat)){
            $query->where('id IN ('.$selected_cat.')');
        }
        $query->where('published =1');
        $query->order('lft ASC');
        $db->setQuery($query);
        //load objectlist and return the data
        $results = $db->loadObjectList();
        return $results;
    }


    public function getProductList($overrideLimits = false, $group = '')
    {
        if (empty($this->_productlist))
        {
            $query = $this->getProductListQuery($overrideLimits);

            if (!$overrideLimits)
            {
                $limitstart = $this->getState('limitstart');
                $limit = $this->getState('limit');
                try {
                    $this->_productlist = $this->_getList((string) $query, $limitstart, $limit, $group);
                } catch (Exception $e) {

                }
            }
            else
            {
                try {
                    $this->_productlist = $this->_getList((string) $query, 0, 0, $group);
                } catch (Exception $e) {

                }
            }

        }
        return $this->_productlist;
    }

    public function getProductPagination()
    {
        if (empty($this->productpagination))
        {
            // Import the pagination library
            JLoader::import('joomla.html.pagination');

            // Prepare pagination values
            $total = $this->getProductPageTotal();
            $limitstart = $this->getState('limitstart');
            $limit = $this->getState('limit');
            // Create the pagination object
            $this->productpagination = new JPagination($total, $limitstart, $limit);
        }

        return $this->productpagination;
    }


    /**
     * Get the number of all items
     *
     * @return  integer
     */
    public function getProductPageTotal()
    {
        if (is_null($this->productpagetotal))
        {
            $query = $this->buildCountQuery();

            if ($query === false)
            {
                $subquery = $this->getProductListQuery(false);
                $subquery->clear('order');
                $query = $this->_db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from("(" . (string) $subquery . ") AS a");
            }

            $this->_db->setQuery((string) $query);

            $this->productpagetotal = $this->_db->loadResult();
        }

        return $this->productpagetotal;
    }

    public function getProductListQuery($overrideLimits = false) {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)->select('#__j2store_products.*')->from('#__j2store_products');
        $this->_buildQueryJoins($query);
        $this->_buildWhereQuery($query);
        $this->_buildQueryOrderBy($query);
        $query->group('#__j2store_products.j2store_product_id');
        $model = $this;
        J2Store::plugin()->event('AfterProductListQuery', array(&$query, &$model));
        return $query;

    }

    public function _buildQueryJoins($query) {

        $query->select('#__j2store_variants.sku');
        $query->select('#__j2store_variants.upc');
        $query->select('#__j2store_variants.price');
        $query->select('#__j2store_variants.shipping');
        $query->select('#__j2store_variants.manage_stock');
        $query->select('#__j2store_variants.availability');
        $query->join('INNER', '#__j2store_variants ON #__j2store_products.j2store_product_id=#__j2store_variants.product_id');

        $query->select('#__j2store_productimages.thumb_image, #__j2store_productimages.main_image, #__j2store_productimages.additional_images,#__j2store_productimages.thumb_image_alt,#__j2store_productimages.main_image_alt,#__j2store_productimages.additional_images_alt');
        $query->join('LEFT OUTER', '#__j2store_productimages ON #__j2store_products.j2store_product_id=#__j2store_productimages.product_id');

        $query->select('#__j2store_taxprofiles.taxprofile_name');
        $query->join('LEFT OUTER', '#__j2store_taxprofiles ON #__j2store_products.taxprofile_id=#__j2store_taxprofiles.j2store_taxprofile_id');

        $query->select($this->_db->qn('#__j2store_productquantities').'.j2store_productquantity_id ')
            ->select($this->_db->qn('#__j2store_productquantities').'.quantity')
            ->join('LEFT OUTER','#__j2store_productquantities ON #__j2store_productquantities.variant_id = #__j2store_variants.j2store_variant_id');

        //for variable product
        $query->select('#__j2store_productprice_index.min_price');
        $query->select('#__j2store_productprice_index.max_price');
        $query->join('LEFT OUTER', '#__j2store_productprice_index ON  #__j2store_products.j2store_product_id=#__j2store_productprice_index.product_id');

    }

    public function buildCountQuery() {
        $subquery = $this->getProductListQuery(false);
        $subquery->clear('order');
        $query = $this->_db->getQuery(true)
            ->select('COUNT(*)')
            ->from("(" . (string) $subquery . ") AS a");
        return $query;
    }

    /**
     * Method to build where query based on the filters
     * @param string $query
     */
    function _buildWhereQuery(&$query){
        $db = $this->_db;
        $state = $this->getFilterValues();

        /*$query->where(
            $db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1)
        );*/

        if(!is_null($state->product_ids) && !empty($state->product_ids)) {
            $query->where(
                $db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' IN ('.$state->product_ids.')'
            );
        }

        if($state->search){
            $product_model = $this;
            $where_array_query = J2Store::plugin()->event('AfterProductListWhereQuery', array(&$product_model));
            $where_query = '';
            if(!empty($where_array_query)){
                $where_query = implode(' OR ',$where_array_query);
                $where_query .= ' OR ';
            }

            $query->where('('.
                $where_query.
                $db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
                $db->qn('#__j2store_products').'.'.$db->qn('product_source').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('upc').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('price').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
                $db->qn('#__j2store_products').'.'.$db->qn('product_type').' LIKE '.$db->q('%'.$state->search.'%').')'
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


        if($state->productid_from){
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' >= '.$db->q($state->productid_from)) ;
        }

        if($state->productid_to){
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').'<= '.$db->q($state->productid_to)) ;
        }

        if($state->manufacturer_id){
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('manufacturer_id').' = '.$db->q($state->manufacturer_id)) ;
        }

        if($state->vendor_id) {
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('vendor_id').'='.$db->q($state->vendor_id));
        }

        if($state->taxprofile_id) {
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('taxprofile_id').'='.$db->q($state->taxprofile_id));
        }

        if(!is_null($state->visible) && $state->visible != '') {
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('visibility').'='.$db->q($state->visible));
        }

        if(!is_null($state->enabled) &&  !empty($state->enabled)) {
            $query->where($db->qn('#__j2store_products').'.'.$db->qn('enabled').' IN ('.$db->q($state->enabled).')');
        }

        if(!is_null($state->sku) && !empty($state->sku)){
            $query->where($db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE ('.$db->q('%'.$state->sku.'%').')');
        }

        //check filter price from exists
        if (!is_null($state->pricefrom ) && !empty( $state->pricefrom ) )
        {
            //check the item price matches the range
            $query->having( "#__j2store_variants.price >= '" . ( int ) $state->pricefrom  . "'" );
        }

        if ((!is_null($state->priceto)) && !empty( $state->priceto ) )
        {
            //check the item price matches the range
            $query->having( "#__j2store_variants.price <= '" . ( int ) $state->priceto . "'" );
        }

        if(!is_null($state->catids) && !empty($state->catids)) {

            if(!is_array($state->catids)) {
                $catids = (array) $state->catids;
            }

            $catids = implode(',', $state->catids);

            $query->where('#__content.catid IN ('.$catids.')');
        }


        if(!is_null($state->productfilter_id) && !empty($state->productfilter_id)){
            $query->where('FIND_IN_SET ('.$db->q($state->productfilter_id).',#__j2store_products.productfilter_ids)' );
        }
    }

    /**
     * Method to build orderby query
     * @param string $query
     */
    protected function _buildQueryOrderBy(&$query){
        $db =$this->_db;
        $this->_buildSortQuery($query);
        if(!empty($this->state->filter_order) && in_array($this->state->filter_order,array('j2store_product_id','product_source','product_source_id'))) {
            if(!in_array(strtolower($this->state->filter_order_Dir),array('asc','desc'))){
                $this->state->filter_order_Dir = 'desc';
            }
            $query->order($db->qn('#__j2store_products').'.'.$db->qn($this->state->filter_order).' '.$this->state->filter_order_Dir);
            //$query->order('#__j2store_products.'.$this->state->filter_order.' '.$this->state->filter_order_Dir);
        }else{
            $query->order('#__j2store_products.created_on DESC');
        }
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
    public function getTableFields()
    {
        $tableName = $this->getTable()->getTableName();
        static $sets;

        if ( !is_array( $sets) )
        {
            $sets= array( );
        }

        if(!isset($sets[$tableName])) {

            if (version_compare(JVERSION, '3.0', 'ge'))
            {
                $sets[$tableName] = $this->getDbo()->getTableColumns($tableName, true);
            }
            else
            {
                $fieldsArray = $this->getDbo()->getTableFields($tableName, true);
                $sets[$tableName] = array_shift($fieldsArray);
            }
        }
        return $sets[$tableName];
    }


    /**
     * Method to merge config params and  Menu params
     * @return JRegistry
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

        $configparams = $platform->getRegistry($config, true);
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
        //containes sorting fields
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

    public function getSearchProduct(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $search = $this->getState('search',null,'string');
        $query->select('*')->from('#__j2store_variants');
        $query->join('LEFT', '#__j2store_products  ON #__j2store_variants.product_id = #__j2store_products.j2store_product_id');

        //$query->where($db->qn('#__j2store_variants').'.'.$db->qn('is_master').'='.$db->q(1));
        $query->where($db->qn('#__j2store_products').'.'.$db->qn('visibility').'='.$db->q(1));
        $query->where($db->qn('#__j2store_products').'.'.$db->qn('enabled').'='.$db->q(1));

        if($search){
            $query->where(
                ' ( '.
                $db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_products').'.'.$db->qn('product_source').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE '.$db->q('%'.$search.'%').' OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('upc').' LIKE '.$db->q('%'.$search.'%').' OR '.
                $db->qn('#__j2store_products').'.'.$db->qn('product_type').' LIKE '.$db->q('%'.$search.'%')
                .' ) '
            ) ;

        }
        $db->setQuery($query);
        return $db->loadObjectList();
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

        // Join over the categories.
        $query->select('c.title AS category_title, c.path AS category_route, c.access AS category_access, c.alias AS category_alias')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        if ($this->checkTable () ) {
            $query->select('mc.catid as mc_catid')->join('LEFT', '#__multicats_content_catid AS mc ON mc.item_id = a.id');
        }

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

        $query->select('CASE 
							WHEN #__j2store_products.product_type IN ("variable","flexivariable","advancedvariable","variablesubscriptionproduct") THEN
							  #__j2store_productprice_index.min_price
							ELSE
								#__j2store_variants.price
							END as min_price
				');

        $query->select('CASE 
							WHEN #__j2store_products.product_type IN ("variable","flexivariable","advancedvariable","variablesubscriptionproduct") THEN
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
        $categoryId = $this->getState('catids');

        if (is_numeric($categoryId))
        {
            $type = $this->getState('filter.category_id.include', true) ? ' ' : 'NOT ';

            // Add subcategory check
            $includeSubcategories = $this->getState('filter.subcategories', false);
            //$categoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q('[[:<:]]'.$categoryId.'[[:>:]]') ;
            if(version_compare($db->getVersion(),'7.9.9','>')){
                if ($this->checkTable () ) {
                    $categoryEquals = 'mc.catid ' . $type . ' REGEXP BINARY '. $db->q('\\b'.$categoryId.'\\b') ;
                }else{
                    $categoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q('\\b'.$categoryId.'\\b') ;
                }
            }else{
                if ($this->checkTable () ) {
                    $categoryEquals = 'mc.catid ' . $type . ' REGEXP BINARY '. $db->q('[[:<:]]'.$categoryId.'[[:>:]]') ;
                }else{
                    $categoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q('[[:<:]]'.$categoryId.'[[:>:]]') ;
                }
            }
            if ($includeSubcategories)
            {
                //TODO: include subcategories does not support multicategory
                $levels = (int) $this->getState('filter.max_category_levels', '1');

                // Create a subquery for the subcategory list
                $subQuery = $db->getQuery(true)
                    ->select('sub.id')
                    ->from('#__categories as sub')
                    ->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt')
                    ->where('this.id = ' . $db->q((int) $categoryId));

                if ($levels >= 0)
                {
                    $subQuery->where('sub.level <= (this.level + ' . $levels.')');
                }
                $db->setQuery($subQuery);
                $sub_data = $db->loadAssocList();
                $sub_cats = array();
                foreach($sub_data as $k=> $sub_cat){
                    $sub_cats [] = $sub_cat['id'];
                }
                if(count($sub_cats) > 0){
                    $sub_string = implode(',', $sub_cats) ;
                    $categoryEquals .= ' OR a.catid IN ('.$sub_string.')';
                }
                // Add the subquery to the main query
                $query->where('(' . $categoryEquals . ')');
            }
            else
            {
                $query->where($categoryEquals);
            }

        }
        elseif (is_array($categoryId) && (count($categoryId) > 0))
        {
            $categoryId = J2Store::platform()->toInteger($categoryId);
            if(version_compare($db->getVersion(),'7.9.9','>')){
                $categoryIds = '\\b'. implode('\\b|\\b', $categoryId) .'\\b';
            }else{
                $categoryIds = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $categoryId) .'[[:>:]]';
            }
            if (!empty($categoryId))
            {
                $type = $this->getState('filter.category_id.include', true) ? '' : 'NOT ';
                $levels = (int) $this->getState('filter.max_category_levels', '1');

                $includeSubcategories = $this->getState('filter.subcategories', false);
                //$categoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q($categoryIds) ;

                if ($this->checkTable () ) {
                    $categoryEquals = 'mc.catid ' . $type . ' REGEXP BINARY '. $db->q($categoryIds) ;
                }else{
                    $categoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q($categoryIds) ;
                }

                if ($includeSubcategories)
                {
                    //TODO: include subcategories does not support multicategory
                    $levels = (int) $this->getState('filter.max_category_levels', '1');

                    // Create a subquery for the subcategory list
                    $subQuery = $db->getQuery(true)
                        ->select('sub.id')
                        ->from('#__categories as sub')
                        ->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
                    $subQuery->where('this.id IN ( ' . implode(',', $categoryId).' )');

                    if ($levels >= 0)
                    {

                        $subQuery->where('sub.level <= (this.level + ' . $levels.')');
                    }
                    $db->setQuery($subQuery);
                    $sub_data = $db->loadAssocList();
                    $sub_cats = array();
                    foreach($sub_data as $k=> $sub_cat){
                        $sub_cats [] = $sub_cat['id'];
                    }
                    if(!empty($sub_cats)){
                        if(version_compare($db->getVersion(),'7.9.9','>')){
                            $regSubcats = '\\b'. implode('\\b|\\b', $sub_cats) .'\\b';
                        }else{
                            $regSubcats = '[[:<:]]'. implode('[[:>:]]|[[:<:]]', $sub_cats) .'[[:>:]]';
                        }
                        $subCategoryEquals = 'a.catid ' . $type . ' REGEXP BINARY '. $db->q($regSubcats) ;
                        // Add the subquery to the main query
                        $query->where('(' . $categoryEquals . ' OR '.$subCategoryEquals.' )');
                    }else{
                        $query->where($categoryEquals);
                    }

                    // Add the subquery to the main query
                    //$query->where('(' . $categoryEquals . ' OR a.catid IN (' . $subQuery->__toString() . '))');

                }
                else
                {
                    $query->where($categoryEquals);
                }

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

        $is_master = 1;
        J2Store::plugin()->event('IsMasterProduct',array(&$is_master));
        if($is_master){
            $query->where(
                $db->qn('#__j2store_variants').'.'.$db->qn('is_master').' = '.$db->q(1)
            );
        }

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
                $db->qn('#__j2store_products').'.'.$db->qn('created_on').' >= '.$since
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
                $db->qn('#__j2store_products').'.'.$db->qn('created_on').' <= '.$until
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
           $variant_pricerange_qry .= '(price >= '.$db->q(( int ) $state->pricefrom).') ' ;
           $variant_pricerange_qry .= ' AND ';
           $variant_pricerange_qry .= '(price <= '.$db->q(( int ) $state->priceto).') ' ;
        
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
        //$article_category_ordering = $params->get('consider_category',0);
        $articleOrderby		= $params->get('orderby_sec', 'rdate');
        $articleOrderDate	= $params->get('order_date');
        $secondary			= $this->orderbySecondary($query,$articleOrderby, $articleOrderDate);
        if(empty( $secondary )){
            $secondary = 'a.ordering';
        }
        //else{
           // $orderby = trim ( $secondary );
            //if($article_category_ordering){
                //$query->order('category_title'.' '.$this->getState('list.direction', 'ASC'));
            //}
        //}

        $this->setState('list.ordering', $secondary);

        $query->order($this->getState('list.ordering', 'a.ordering').' '.$this->getState('list.direction', 'ASC'));


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


    public function orderbySecondary($query,$orderby, $orderDate = 'created')
    {
        $queryDate = $this->getQueryDate($orderDate);
        $direction = $this->getState('category_order_direction', 'ASC');
        switch ($orderby)
        {
            case 'date' :
                $orderby = $queryDate;
                break;

            case 'rdate' :
                $this->setState('list.direction', 'DESC');
                $orderby = $queryDate;
                break;

            case 'title' :
                $orderby = 'a.title';
                break;

            case 'ralpha' :
                $this->setState('list.direction', 'DESC');
                $orderby = 'a.title';
                break;

            case 'hits' :
                $orderby = 'a.hits';
                break;

            case 'rhits' :
                $this->setState('list.direction', 'DESC');
                $orderby = 'a.hits';
                break;

            case 'order' :
                $orderby = 'a.ordering';
                break;

            case 'author' :
                $orderby = 'a.created_by';
                break;

            case 'rauthor' :
                $this->setState('list.direction', 'DESC');
                $orderby = 'a.author';
                break;

            case 'featured' :
                $orderby = 'a.featured';
                break;

            case 'cat_order':
                $query->order('c.lft '.$direction);
                $orderby = 'a.ordering';
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

    public function runPrepareEventOnDescription($description, $id, $params, $context) {

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
            'catids'			=>  $this->getState('catids',null,'array'),
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

    /**
     * check table is available
     * @param $string - table name
     * @param $force - force check
     * @return boolean
    */
    function checkTable($string='multicats_content_catid',$force=false){
        static $sets;

        if (! is_array ( $sets )) {
            $sets = array ();
        }
        if (! isset ( $sets [$string] ) || $force) {


            $db = $this->getDbo();
            $tables = $db->getTableList ();
            $prefix = $db->getPrefix ();
            if (in_array ( $prefix . $string, $tables )) {
                $sets [$string] = true;
            }else{
                $sets [$string] = false;
            }

            if(JComponentHelper::isInstalled('com_multicats')) {

                if(!JComponentHelper::isEnabled('com_multicats'))
                {
                    $sets [ $string ] = false;
                }

            }else {
                $sets [ $string ] = false;
            }

        }
        return $sets [$string];
    }
}