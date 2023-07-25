<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
class plgContentJ2Store extends JPlugin
{
	private $_cleaned = false;
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		J2Store::platform()->application()->getLanguage()->load ('com_j2store', JPATH_ADMINISTRATOR);
	}

    public function onContentPrepare($context, $article, $params, $page = 0)
    {
        $platform = J2Store::platform();
        //running from the backend
        if($platform->isClient('administrator')) {
            return false;
        }

        // no need to run blog category and other category view
        if(strpos($context, 'categories') !== false){
            return false;
        }
        if(strpos($context, 'productlist') !== false){
            $shortcode_matches = $this->parseShortCodes($article);

            if(isset($shortcode_matches[0]) && count($shortcode_matches[0])) {

                foreach ( $shortcode_matches as $single_match ) {
                    $article->text = str_replace($single_match[0], '', $article->text);
                }
            }
            return;
        }
        $cache_control = $this->params->get("cache_control",1);
        if($this->_cleaned == false && $cache_control) {
            $cache = JFactory::getCache();
            $cache->clean('com_content');
            $cache->clean('com_j2store');
            $this->_cleaned = true;
        }
        $j2params = J2Store::config();
        $placement = $j2params->get('addtocart_placement', 'default');
        if(strpos($context, 'com_content') !== false) {
            if($placement == 'default' || $placement == 'both') {
                if(!$this->checkPublishDate($article)){
                    return;
                }
                $this->defaultPosition($context, $article, $params, $page);
            }
        }
        if($placement == 'tag' || $placement == 'both') {
            $this->withinArticle($context, $article, $params, $page);
        }
        $this->processShortCodes($context, $article, $params, $page);
    }

    protected function defaultPosition($context, $article, $params, $page = 0) {
        //get the position
        if($context == 'com_content.category' || $context == 'com_content.featured') {
            $position = $this->params->get('category_product_block_position', 'bottom');
        } else {
            $position = $this->params->get('item_product_block_position', 'bottom');
        }

        if(isset($article->id) && $article->id && $position !='afterdisplaycontent') {
            $fof_helper = J2Store::fof();
            $product = $fof_helper->loadTable('Product', 'J2StoreTable');

            if($product->get_product_by_source('com_content', $article->id)) {
                $html = $this->getProductBlock($product, $context, $article, $params, $page);
                $image_html = $this->getProductImageHtml($product, $context, $article, $params, $page);
                if($position == 'top') {
                    $text = $image_html.$html.$article->text;
                } else {
                    $text = $article->text.$image_html.$html;
                }
                $article->text = $text;
            }
        }
    }

    protected function getProductBlock($product, $context, $article, $params, $page = 0) {
        if( ($context == 'com_content.category' || $context == 'com_content.featured') && in_array($this->params->get('category_product_options', 1), array(2,3))) {
            $html = $product->get_product_html('without_options');
        } else {
            $html = $product->get_product_html();
        }
        if($html === false) {
            $html = '';
        }
        return $html;
    }

    protected function getProductImageHtml($product, $context, $article, $params, $page = 0) {

        $image_html = '';

        if($context == 'com_content.category' || $context == 'com_content.featured') {
            $mainimage_width = $this->params->get('list_image_thumbnail_width',120);
            $additional_image_width = $this->params->get('list_product_additional_image_width',80);

            $show_image = $this->params->get('category_display_j2store_images', 1);
            $image_type = $this->params->get('category_image_type', 'thumbnail');
            $image_location = 'default';

            $this->params->get('category_enable_image_zoom',1) ? $this->params->set('item_enable_image_zoom', 1) : $this->params->set('item_enable_image_zoom', 0);

        } else {
            //set the image width
            $mainimage_width = $this->params->get('item_product_main_image_width',120);
            $additional_image_width = $this->params->get('item_product_additional_image_width',100);

            $show_image = $this->params->get('item_display_j2store_images', 1);
            $image_type = $this->params->get('item_image_type', 'thumbnail');
            $image_location = $this->params->get('item_image_placement', 'default');
        }

        if($show_image && $image_location == 'default') {
            $images = $product->get_product_images_html($image_type,$this->params);
            //custom css to adjust the j2store product images width
            $content =".j2store-mainimage .zoomImg, .j2store-product-images .j2store-mainimage img,.j2store-product-images .j2store-thumbnail-image img {width:{$mainimage_width}px} .blog .additional-image-list img ,.item-page .additional-image-list img  { width :{$additional_image_width}px;}";
            J2Store::platform()->application()->getDocument()->addStyleDeclaration($content);
            if($images !== false) {
                $image_html = $images;
            }
        }

        return $image_html;
    }

	protected function withinArticle($context, $article, $params, $page = 0) {

		// simple performance check to determine whether bot should process further
		if (strpos($article->text, '{j2store}') === false) {
			return true;
		}
		$this->processShortCodes($context, $article, $params, $page);
	}

    public function processShortCodes($context, $article, $params, $page){

        //if(!isset($article->id) || !isset($article->text)) return true;

        $newmatches = $this->parseShortCodes($article);

        if(isset($newmatches[0]) && count($newmatches[0])) {
            $fof_helper = J2Store::fof();
            $j2params = J2Store::config();
            $placement = $j2params->get('addtocart_placement', 'default');

            foreach($newmatches as $newmatch) {
                if (empty($newmatch[1])) {
                    break;
                }
                $values = explode('|', $newmatch[1]);
                //first value should always be the ID.
                if(isset($values[0])) {
                    $html = '';
                    $product = $fof_helper->loadTable('Product', 'J2StoreTable');

                    if($product->get_product_by_id($values[0])) {
                        $product_article = $this->getArticle($product->product_source_id);
                        if(!$this->checkPublishDate($product_article)){
                            $article->text = str_replace($newmatch[0], $html, $article->text);
                            return;
                        }

                        if($placement == 'tag' || $placement == 'both') {
                            // this is special. Because this is controlled by the placement switch
                            if(in_array('cart', $values)) {
                                $html .= $product->get_product_html();
                            }
                        }

                        if(in_array('cartonly', $values)) {
                            $html .= $product->get_product_cart_html();
                        }

                        if(in_array('price', $values)) {
                            $html .= $product->get_product_price_html('price');
                        }

                        if(in_array('saleprice', $values)) {
                            $html .= $product->get_product_price_html('saleprice');
                        }

                        if(in_array('regularprice', $values)) {
                            $html .= $product->get_product_price_html('regularprice');
                        }

                        if(in_array('thumbnail', $values)) {
                            $html .= $product->get_product_images_html('thumbnail');
                        }

                        if(in_array('mainimage', $values)) {
                            $html .= $product->get_product_images_html('main');
                        }

                        if(in_array('mainadditional', $values)) {
                            $html .= $product->get_product_images_html('mainadditional');
                        }

                        if(in_array('upsells', $values)) {
                            $html .= $product->get_product_upsells_html();
                        }
                        if(in_array('crosssells', $values)) {
                            $html .= $product->get_product_cross_sells_html();
                        }
                        if(in_array('manufacturer', $values) || in_array('brand', $values)) {
                            $html .= $product->get_product_brand_html();
                        }
                    }
                    if($html === false) {
                        $html = '';
                    }
                    $article->text = str_replace($newmatch[0], $html, $article->text);
                }
            }
        }
    }

    private function parseShortCodes($article) {
        $regex		= '/{j2store}(.*?){\/j2store}/';
        preg_match_all($regex, $article->text, $newmatches, PREG_SET_ORDER);
        return $newmatches;
    }

    function onContentPrepareForm($form, $data)
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        if ( $platform->isClient('site') && $this->params->get( 'allow_frontend_product_edit', 0 ) == 0 )
        {
            return true;
        }

        $contentParams   = JComponentHelper::getParams( 'com_content' );
        $message_display = $contentParams->get( 'show_article_options', 0 );
        if ( ! defined( 'F0F_INCLUDED' ) )
        {
            require_once JPATH_LIBRARIES . '/f0f/include.php';
        }

        if ( ! ( $form instanceof JForm ) )
        {
            $this->_subject->setError( 'JERROR_NOT_A_FORM' );

            return false;
        }
        J2Store::plugin()->event( 'BeforeContentPrepareForm', array( $form, $data ) );
        // Check we are manipulating a valid form.
        $formName = $form->getName();

        if ( ! in_array( $formName, array( 'com_content.article' ) ) )
        {
            return true;
        }
        // Add the form path and fields to the form.
        JForm::addFormPath( dirname( __FILE__ ) . '/forms' );
        JForm::addFieldPath( dirname( __FILE__ ) . '/fields' );
        $form->loadFile( 'j2store', false );
        if ( $platform->isClient('site') )
        {
            if (version_compare(JVERSION, '3.8.0', 'lt')) {
                // need to enable joomla old version. Just in case
                $this->appendJ2StoreFieldset();
            }
        }
        J2Store::plugin()->event( 'AfterContentPrepareForm', array( $form, $data ) );
        if ( ! $message_display )
        {
            $app->enqueueMessage( JText::_( 'J2STORE_TAB_NOT_DISPLY_IN_CONTENT' ), 'waring' );
        }
        return true;
    }

    public function appendJ2StoreFieldset(){
        $doc = J2Store::platform()->application()->getDocument();
        $html ='';
        require_once ((dirname(__FILE__).'/fields/').strtolower('j2store').'.php');
        $jFormField =  new JFormFieldJ2Store();
        $liTab = JText::_('COM_J2STORE');
        $j2html = $jFormField->getControlGroup();
        $j2html = json_encode($j2html);
        $script = "
		if(typeof(j2store) == 'undefined') {
		var j2store = {};
		}
		if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
		}

		(function($) {
		$(document).ready(function() {

		var form = $('#adminForm');
		var string ={$j2html};
			//form.find('.btn-toolbar').append('<div class=\'btn-group\' ><button class=\'btn btn-primary\' onclick=\"Joomla.submitbutton(\'article.apply\')\" type=\'button\'><span class=\'icon-ok\'></span>Apply</button></div>');
			form.find('fieldset  ul').append('<li><a data-toggle=\'tab\' href=\'#j2store\'>J2Store</a></li>');
			form.find('.tab-content').append('<div class=\'tab-pane\' id=\'j2store\'></div>');
			var elements = $(string).map(function() {
	 	 		return $('#j2store').append(this).html();
	 	 	});

			form.find('#j2store .container').removeClass('container');
			form.find('#j2store .container').addClass('j2store-container');
		});
		})(j2store.jQuery);
		";
        $doc->addScriptDeclaration($script);
        return $html;
    }

    function onContentBeforeDisplay($option, $item, $params) {
        if(!$this->checkPublishDate($item)){
            return;
        }
        $j2params = J2Store::config();
        $placement = $j2params->get('addtocart_placement', 'default');
        if($placement == 'tag') {
            return;
        }
        return $this->getProductImages('beforecontent', $option, $item, $params);
    }

    function onContentAfterDisplay($option, $item, $params) {
        if (strpos ( $option, 'com_content' ) === false)
            return;

        if(!$this->checkPublishDate($item)){
            return;
        }
        $j2params = J2Store::config();
        $placement = $j2params->get('addtocart_placement', 'default');
        if($placement == 'tag') {
            return;
        }
        //if it is a j2store product list, then we do not want to process further.
        if($option == 'com_content.category.productlist') return;

        $html = '';
        // get the position
        if ($option == 'com_content.category' || $option == 'com_content.featured') {
            $position = $this->params->get ( 'category_product_block_position', 'bottom' );
        } else {
            $position = $this->params->get ( 'item_product_block_position', 'bottom' );
        }

        if (isset ( $item->id ) && $item->id > 0 && $position == 'afterdisplaycontent') {
            $product = J2Store::fof()->loadTable( 'Product', 'J2StoreTable' );
            if ($product->get_product_by_source ( 'com_content', $item->id )) {
                $html .= $this->getProductImageHtml ( $product, $option, $item, $params );
                $html .= $this->getProductBlock ( $product, $option, $item, $params );
            }
        }
        $html .= $this->getProductImages ( 'aftercontent', $option, $item, $params );
        return $html;
    }

    public function getProductImages($event, $option, $item, $params) {
        $return = '';
        $image_location = $this->params->get('item_image_placement', 'default');
        $show_image = $this->params->get('item_display_j2store_images', 1);
        if($image_location != $event || !$show_image || (!isset($item->id) || $item->id < 1) ) return $return;

        if(strpos($option, 'com_content.article') !== false) {
            $image_type = $this->params->get('item_image_type', 'thumbnail');
            $j2params = J2Store::config();
            $placement = $j2params->get('addtocart_placement', 'default');
            if($placement == 'default' || $placement == 'both') {
                $product = J2Store::fof()->loadTable('Product', 'J2StoreTable');
                if($product->get_product_by_source('com_content', $item->id)) {

                    $images = $product->get_product_images_html($image_type, $this->params);
                    if($images !== false) {
                        $return = $images;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Before save content method
     * Method is called right before the content is saved
     *
     * @param string        The context of the content passed to the plugin (added in 1.6)
     * @param object        A JTableContent object
     * @param bool        If the content is just about to be created
     *
     * @throws Exception
     */
    function onContentBeforeSave($context, $data, $isNew)
    {
        // Check we are manipulating a valid form.
        $context_array = array ('com_content.article');
        $platform = J2Store::platform();
        if($platform->isClient('site')){
            $context_array = array ('com_content.form');
        }
        if (!in_array($context,$context_array)) {
            return true;
        }
        $app = $platform->application();
        // store in another inp variable
        $app->input->set('j2store_all_attribs',$data->attribs,'RAW');
        // get the J2Store data from attribs
        $all_attribs = json_decode($data->attribs);
        if (isset($all_attribs->j2store)) {
            unset( $all_attribs->j2store );
        }
        // reset attribs array
        $data->attribs = json_encode ( $all_attribs );
        return true;
    }

	/**
	 * After save content method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 *
	 */
    function onContentAfterSave($context, $data, $isNew)
    {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
        J2Store::plugin()->event('ContentAfterSave',array($context, $data, $isNew));
        // Check we are manipulating a valid form.
        $context_array = array ('com_content.article');
        if($platform->isClient('site')){
            $context_array = array ('com_content.form');
        }
        if (!in_array($context,$context_array)) {
            return true;
        }
        if (!defined('F0F_INCLUDED')){
            include_once JPATH_LIBRARIES . '/f0f/include.php';
        }
        $app = $platform->application();
        $task = $app->input->getString('task');
        $articleId = isset($data->id) ? $data->id : 0;
        if($articleId) {
            //see if the product is already saved.
            $alreadyExists = 0;
            $product = $fof_helper->loadTable('Product', 'J2StoreTable',array('product_source'=>'com_content', 'product_source_id'=>$articleId));
            if($product->enabled == 1) {
                $alreadyExists = 1;
            }
            //only save when treated as a product
            $all_attribs = $app->input->get('j2store_all_attribs', '','RAW');
            $attribs = json_decode($all_attribs);
            if(isset($attribs->j2store->enabled) && ($attribs->j2store->enabled == 1 || $alreadyExists)
                && isset($attribs->j2store->product_type) && !empty($attribs->j2store->product_type)) {
                // convert the joomla article attributes from json to object
                //check if it is a save as copy
                if($task == 'save2copy') {
                    if(in_array($attribs->j2store->product_type, array('variable','advancedvariable','flexivariable','variablesubscriptionproduct'))){
                        return true;
                    }
                    $db = JFactory::getDBo();
                    $query = $db->getQuery(true);
                    $query->select('#__j2store_product_filters.filter_id')->from('#__j2store_product_filters')
                        ->where('#__j2store_product_filters.product_id ='.$db->q($attribs->j2store->j2store_product_id))
                        ->order('#__j2store_product_filters.filter_id ASC');
                    $db->setQuery($query);
                    $product_filter_list = $db->loadColumn();
                    //we are copying the data. So reset the product id and the variant id
                    $attribs->j2store->j2store_product_id = null;
                    $attribs->j2store->j2store_variant_id = null;
                    $attribs->j2store->j2store_productimage_id = null;
                    $attribs->j2store->quantity->j2store_productquantity_id = null;
                    $attribs->j2store->productfilter_ids = $product_filter_list;
                    unset($attribs->j2store->item_options);
                }
                $attribs->j2store->product_source = 'com_content';
                $attribs->j2store->product_source_id = $data->id;
                $fof_helper->getModel('Products', 'J2StoreModel')->save($attribs->j2store);
            }
        }
        return true;
    }

    function onContentAfterDelete($context, $data) {
        if(strpos($context, 'com_content') !== false) {
            if (! defined ( 'F0F_INCLUDED' )) {
                include_once JPATH_LIBRARIES . '/f0f/include.php';
            }
            $articleId = isset ( $data->id ) ? $data->id : 0;
            if ($articleId) {
                $productModel = J2Store::fof()->getModel( 'Products', 'J2StoreModel' );
                $itemlist = $productModel->getProductsBySource( 'com_content', $articleId );
                foreach ( $itemlist as $item ) {
                    $productModel->setId ( $item->j2store_product_id )->delete ();
                }
            }
        }
        return true;
    }

    function onJ2StoreAfterGetProduct(&$product) {
        if(isset($product->product_source) && $product->product_source == 'com_content' ) {
            static $sets;
            if(!is_array($sets)) {
                $sets = array();
            }
            $content = $this->getArticle($product->product_source_id);
            if(isset($content->id) && $content->id) {
                //assign
                $product->source = $content;
                $product->product_name = $content->title;
                $product->product_short_desc = $content->introtext;
                $product->product_long_desc = $content->fulltext;
                $product->product_edit_url = JRoute::_('index.php?option=com_content&task=article.edit&id='.$content->id);
                $com_path = JPATH_SITE.'/components/com_content/';
                if (!class_exists('ContentRouter')) {
                    if (version_compare(JVERSION, '3.99.99', 'ge')) {
                        // require $com_path.'/src/Helper/RouteHelper.php';
                    }else if (version_compare(JVERSION, '3.99.99', 'lt')){
                        include $com_path.'router.php';

                    }
                }
                $link = 'index.php';
                if (version_compare(JVERSION, '3.99.99', 'ge')) {
                    $link = \Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute($content->id, $content->catid, $content->language);
                }else if (version_compare(JVERSION, '3.99.99', 'lt')){
                    $content->slug    = $content->id . ':' . $content->alias;
                    $cat_alias = isset($content->category_alias) ? $content->category_alias : '';
                    $content->catslug = $content->catid . ':' .$cat_alias;
                    $link = ContentHelperRoute::getArticleRoute($content->slug, $content->catslug, $content->language);
                }
                $product->product_view_url = JRoute::_($link);
                if($content->state == 1 ) {
                    $product->exists = 1;
                } else {
                    $product->exists = 0;
                }
                $sets[$product->product_source][$product->product_source_id] = $content;
            } else {
                $product->exists = 0;
            }
        }
    }

    public function onJ2StoreAfterProductListQuery(&$query, &$model) {
        $db = JFactory::getDbo();
        $query->select('#__content.title as product_name,#__content.catid');
        $query->join('LEFT OUTER', '#__content AS #__content ON #__j2store_products.product_source_id=#__content.id AND #__j2store_products.product_source='.$db->q('com_content'));
        $query->where('CASE WHEN #__j2store_products.product_source = '.$db->q('com_content') .' THEN
						#__content.state !='.$db->q(-2).'
  					ELSE
						#__j2store_products.enabled = '.$db->q(1).'
					END
				 	');
        $search = $model->getState('search','','string');
        if(!empty($search )) {
            $query->where('CASE WHEN #__j2store_products.product_source = '.$db->q('com_content') .' THEN	
			( #__content.title LIKE '.$db->q('%'.$search.'%').' OR '.$db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_products').'.'.$db->qn('product_source').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('upc').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_variants').'.'.$db->qn('price').' LIKE '.$db->q('%'.$search.'%').'OR '.
                $db->qn('#__j2store_products').'.'.$db->qn('product_type').' LIKE '.$db->q('%'.$search.'%').' ) ELSE
				#__j2store_products.enabled = '.$db->q(1).'
				END');
        }

    }

    public function onJ2StoreAfterProductListWhereQuery(&$model){
        $query = '';
        $db = JFactory::getDbo();
        $search = $model->getState('search','','string');
        if(!empty($search)){
            $query = "#__content.title LIKE ".$db->q('%'.$search.'%');
        }
        return $query;
    }

    public function onJ2StoreAfterStockProductListQuery(&$query,&$model){
        $db = JFactory::getDbo();
        $query->select('#__content.title as product_name,#__content.catid');
        $query->join('LEFT OUTER', '#__content AS #__content ON #__j2store_products.product_source_id=#__content.id AND #__j2store_products.product_source='.$db->q('com_content'));
        $query->where('CASE WHEN #__j2store_products.product_source = '.$db->q('com_content') .' THEN
						#__content.state !='.$db->q(-2).'
  					ELSE
						#__j2store_products.enabled = '.$db->q(1).'
					END
				 	');
    }

    private function getArticle($content_id) {
        static $sets;
        if (! is_array ( $sets )) {
            $sets = array ();
        }
        if (! isset ( $sets [$content_id] )) {
            $platform = J2Store::platform();
            $app = $platform->application();
            $view = $app->input->getString('view');
            if( $platform->isClient('site') && $view == 'products') {
                //required. Sometimes, users will simply unpublish the articles and this will throw a 404 error. Turn off the throwing.
                F0FPlatform::getInstance()->setErrorHandling(E_ALL, "ignore");
                JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models');
                $model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
                $model->setState('filter.published', 1);
                $params = $app->getParams();
                $model->setState('params', $params);
                $sets [$content_id] = $model->getItem($content_id);
            }else {
                $db = JFactory::getDbo ();
                $query = $db->getQuery ( true )->select ( 'a.*' )->from ( '#__content as a' )->where ( 'a.id=' . $db->q ($content_id) );
                $query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
                    ->join('LEFT', '#__categories AS c on c.id = a.catid');
                $db->setQuery ( $query );
                $sets [$content_id] = $db->loadObject ();
            }
        }
        return $sets [$content_id];
    }

    function _updateCurrency() {
        $session = J2Store::platform()->application()->getSession();
        //if auto update currency is set, then call the update function
        $store_config = J2Store::storeProfile();
        //session based check. We dont want to update currency when we load each and every item.
        if($store_config->get('config_currency_auto') && !$session->has('currency_updated', 'j2store')) {
            F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->updateCurrencies();
            $session->set('currency_updated', '1', 'j2store');
        }
    }

    function onJ2StoreAfterGetCartItems(&$items) {
        foreach($items as $key=>$item) {
            if($item->product_source == 'com_content') {
                $article = J2Store::article()->getArticle($item->product_source_id);
                if($article->state != 1) {
                    unset($items[$key]);
                }
            }
        }
    }

    function onJ2StoreAfterShippingTroubleListQuery(&$query,&$model){
        $db = JFactory::getDbo ();
        $query->join('LEFT', '#__content ON (#__j2store_products.product_source_id =#__content.id AND #__j2store_products.product_source ='.$db->q('com_content').')');
        $query->where('CASE WHEN #__j2store_products.product_source = '.$db->q('com_content') .' THEN
						#__content.state !='.$db->q(-2).'
  					ELSE
						#__j2store_products.enabled = '.$db->q(1).'
					END
				 	');
    }

    function checkPublishDate($article){
        $check_publish_date = $this->params->get('check_publish_date',0);
        if(!$check_publish_date){
            return true;
        }

        $date = JFactory::getDate('now');
        $db = JFactory::getDBo();
        // Define null and now dates
        $nullDate = $db->getNullDate();
        //default to the sql formatted date
        $nowDate = $date->toSql();
        $status = false;
        if(isset($article->publish_up) && isset($article->publish_down) &&
            ((($article->publish_up == $nullDate) || ($article->publish_up <= $nowDate)) &&
                (($article->publish_down == $nullDate) || ($article->publish_down >= $nowDate)))){

            $status = true;
        }
        return $status;
    }
}
