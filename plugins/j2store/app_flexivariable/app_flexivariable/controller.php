<?php
/**
 * --------------------------------------------------------------------------------
 * App Plugin - Flexible Variable
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2018 J2Store . All rights reserved.
 * @license     GNU/GPL V3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die ('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/appcontroller.php');

class J2StoreControllerAppFlexiVariable extends J2StoreAppController
{
    var $_element = 'app_flexivariable';

    /**
     * constructor
     */
    function __construct()
    {
        parent::__construct();
        J2Store::platform()->application()->getLanguage()->load('plg_j2store_' . $this->_element, JPATH_ADMINISTRATOR);
    }

    public function addFlexiVariant()
    {
        $app = J2Store::platform()->application();
        $fof_helper = J2Store::fof();
        $post = $app->input->getArray($_REQUEST);
        if (isset($post['varient_combin']) && !empty($post['varient_combin']) && isset($post['flexi_product_id']) && !empty($post['flexi_product_id'])) {
            $product_optionvalue_ids = array();
            foreach ($post['varient_combin'] as $variant_key => $variant_value) {
                //get Product Option 
                $product_option = $fof_helper->getModel('ProductOption', 'J2StoreModel')->getItem($variant_key);
                // save to Product OptionValues
                $product_optionvalue = $fof_helper->loadTable('Productoptionvalue', 'J2StoreTable');
                $product_optionvalue->productoption_id = $variant_key;
                $product_optionvalue->optionvalue_id = $variant_value;
                $product_optionvalue->parent_optionvalue = '';
                $product_optionvalue->product_optionvalue_price = 0;
                $product_optionvalue->product_optionvalue_prefix = '+';
                $product_optionvalue->product_optionvalue_weight = 0;
                $product_optionvalue->product_optionvalue_weight_prefix = '+';
                $product_optionvalue->product_optionvalue_sku = '';
                $product_optionvalue->product_optionvalue_default = 0;
                $product_optionvalue->ordering = 0;
                $product_optionvalue->product_optionvalue_attribs = '{}';
                $product_optionvalue->store();
                $product_optionvalue_ids[] = $product_optionvalue->j2store_product_optionvalue_id;
            }

            if (!empty($product_optionvalue_ids)) {
                //save variant table
                $variable_variant = array(
                    'product_id' => $post['flexi_product_id'],
                    'is_master' => 0,
                    'shipping' => 0,
                    'pricing_calculator' => 'standard',
                    'quantity_restriction' => 0,
                    'allow_backorder' => 0,
                    'isdefault_variant' => 0
                );
                $variantChild = $fof_helper->loadTable('Variant', 'J2StoreTable');
                $variantChild->bind($variable_variant);
                $variantChild->store();
                $variantChild->sku = $variantChild->sku . '_' . $variantChild->j2store_variant_id;
                $variantChild->store();
                if (isset($variantChild->j2store_variant_id) && $variantChild->j2store_variant_id) {
                    //save Product Quantity
                    $product_quantity = $fof_helper->loadTable('Productquantity', 'J2StoreTable');
                    $product_quantity->load(array('variant_id' => $variantChild->j2store_variant_id));
                    $product_quantity->variant_id = $variantChild->j2store_variant_id;
                    $product_quantity->quantity = 0;
                    $product_quantity->on_hold = 0;
                    $product_quantity->sold = 0;
                    $product_quantity->store();

                    //save product variant optionvalues

                    // Create and populate an object.
                    $product_variant_option_value = new \stdClass();
                    $product_variant_option_value->variant_id = $variantChild->j2store_variant_id;
                    $product_variant_option_value->product_optionvalue_ids = implode(',', $product_optionvalue_ids);
                    JFactory::getDbo()->insertObject('#__j2store_product_variant_optionvalues', $product_variant_option_value);
                }

            }
        }
        $json = array();
        $json['html'] = 'variant added';//$this->getVariantHtml($post);
        echo json_encode($json);
        $app->close();
    }

    protected function getVariantHtml($post)
    {
        $vars = new \stdClass();
        $fof_helper = J2Store::fof();
        $product = J2Store::product()->setId($post['flexi_product_id'])->getProduct();
        $fof_helper->getModel('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);
        $vars->product = $product;
        $vars->form_prefix = $post['form_prefix'];
        $vars->extension_id = $post['id'];
        $vars->reinitialize = true;
        return $this->_getLayout('variable_variant', $vars);
    }

    public function deletevariant()
    {
        $app = J2Store::platform()->application();
        $id = $app->input->get('variant_id', 0);
        return $this->deleteSingleVariant($id);
    }

    public function deleteAllVariant()
    {
        $app = J2Store::platform()->application();
        $fof_helper = J2Store::fof();
        $product_id = $app->input->get('product_id', 0);
        $variantModel = $fof_helper->getModel('Variants', 'J2StoreModel');
        $variants = $variantModel
            ->product_id($product_id)
            ->is_master(0)
            ->getList();
        foreach ($variants as $variant) {
            $this->deleteSingleVariant($variant->j2store_variant_id);
        }
        $json = array();
        $json['success'] = 1;
        echo json_encode($json);
        $app->close();
    }

    public function deleteSingleVariant($id)
    {
        $db = JFactory::getDbo();
        $fof_helper = J2Store::fof();
        //delete all related records
        try {
            //inventory
            $query = $db->getQuery(true)->delete('#__j2store_productquantities')->where($db->qn('variant_id') . ' = ' . $db->q($id));
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (\Exception $e) {
                $this->setError($e->getMessage());
                return false;
            }
            $fof_helper = J2Store::fof();
            //prices
            $productPrices = $fof_helper->getModel('ProductPrices', 'J2StoreModel')->limit(0)->limitstart(0)->variant_id($id)->getItemList();
            foreach ($productPrices as $price) {
                if ($price->variant_id == $id) {
                    $product_price_table = $fof_helper->loadTable('ProductPrice', 'J2StoreTable');
                    $product_price_table->load($price->j2store_productprice_id);
                    if ($product_price_table->j2store_productprice_id) {
                        $product_price_table->delete();
                    }
                }
            }
            //get product option values and delete it
            $query = $db->getQuery(true);
            $query->select('product_optionvalue_ids')->from('#__j2store_product_variant_optionvalues')->where($db->qn('variant_id') . ' = ' . $db->q($id));
            $db->setQuery($query);
            $product_optionvalue_ids = $db->loadResult();

            if ($product_optionvalue_ids) {
                $query = $db->getQuery(true)->delete('#__j2store_product_optionvalues')->where($db->qn('j2store_product_optionvalue_id') . ' IN (' . $product_optionvalue_ids . ')');
                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (\Exception $e) {
                    $this->setError($e->getMessage());
                    return false;
                }
            }


            //variant product option values
            $query = $db->getQuery(true)->delete('#__j2store_product_variant_optionvalues')->where($db->qn('variant_id') . ' = ' . $db->q($id));
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (\Exception $e) {
                $this->setError($e->getMessage());
                return false;
            }
            $varaint_table = $fof_helper->loadTable('Variant', 'J2StoreTable');
            if ($varaint_table->load($id)) {
                $varaint_table->delete();
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Gets the parsed layout file
     *
     * @param string $layout The name of  the layout file
     * @param object $vars Variables to assign to
     * @param string $plugin The name of the plugin
     * @param string $group The plugin's group
     * @return string
     * @access protected
     */
    function _getLayout($layout, $vars = false, $plugin = '', $group = 'j2store')
    {
        if (empty($plugin)) {
            $plugin = $this->_element;
        }
        ob_start();
        $layout = $this->_getLayoutPath($plugin, $group, $layout);
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }


    /**
     * Get the path to a layout file
     *
     * @param string $plugin The name of the plugin file
     * @param string $group The plugin's group
     * @param string $layout The name of the plugin layout file
     * @return  string  The path to the plugin layout file
     * @access protected
     */
    function _getLayoutPath($plugin, $group, $layout = 'default')
    {
        $app = J2Store::platform()->application();
        // get the template and default paths for the layout
        $templatePath = JPATH_SITE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $group . '/' . $plugin . '/' . $layout . '.php';
        $defaultPath = JPATH_SITE . '/plugins/' . $group . '/' . $plugin . '/' . $plugin . '/tmpl/' . $layout . '.php';

        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if (JFile::exists($templatePath)) {
            return $templatePath;
        } else {
            return $defaultPath;
        }
    }

}