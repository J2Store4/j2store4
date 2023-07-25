<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/app.php');

class plgJ2StoreApp_schemaproducts extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_schemaproducts';

    /**
     * @param $row
     * @return string|null
     * @throws Exception
     */
    function onJ2StoreGetAppView($row)
    {
        if (!$this->_isMe($row)) {
            return null;
        }
        return $this->viewList();
    }
    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    /**
     * @return string
     * @throws Exception
     */
    function viewList()
    {
        $app = J2Store::platform()->application();
        JToolBarHelper::title(JText::_('J2STORE_APP') . '-' . JText::_('PLG_J2STORE_' . strtoupper($this->_element)), 'j2store-logo');
        $vars = new \stdClass();
        $vars->id = $app->input->getInt('id', 0);
        $form = array();
        $form['action'] = "index.php?option=com_j2store&view=app&task=view&id={$vars->id}";
        $vars->form = $form;
        return $this->_getLayout('default', $vars);
    }

    function onJ2StoreViewProductList(&$items, &$view, &$params, $model)
    {
        $schema = array(
            '@context' => 'https://schema.org/',
            '@type' => "ItemList"
        );
        $i = 1;
        $currency = J2store::currency();
        foreach ($items as $item) {
            $product_url = rtrim(JUri::base(), '/') . '/' . ltrim($item->product_link, '/');
            $item_list = array(
                "@type" => "ListItem",
                //"position" => $i,
                "item" => array(
                    '@type' => "Product",
                    'name' => $item->product_name,
                    'sku' => $item->sku,
                    'url' => $product_url
                )
            );
            if (isset($item->variant->j2store_variant_id) && !empty($item->variant->j2store_variant_id)) {
                $item_list['item']['offers'] = array(
                    '@type' => 'Offer',
                    'price' => isset($item->pricing->price) ? round($item->pricing->price, 2) : 0,
                    'priceCurrency' => $currency->getCode(),
                    'url' => $product_url
                );
                $item_list['offers']['availability'] = 'https://schema.org/' . ($item->variant->availability ? 'InStock' : 'OutOfStock');

            }

            if (isset($item->main_image) && !empty($item->main_image)) {
                $main_image = rtrim(JUri::base(), '/') . '/' . ltrim($item->main_image, '/');
                $item_list['item']['image'] = $main_image;
            } elseif (isset($item->thumb_image) && !empty($item->thumb_image)) {
                $thumb_image = rtrim(JUri::base(), '/') . '/' . ltrim($item->thumb_image, '/');
                $item_list['item']['image'] = $thumb_image;
            }
            if (isset($item->brand_name) && !empty($item->brand_name)) {
                $item_list['item']['brand'] = $item->brand_name;
            }
            if (isset($item->introtext) && !empty($item->introtext)) {
                $item_list['item']['description'] = substr($item->introtext, 0, 200);
            }
            //aggregateRating
            //review
            $schema['itemListElement'][] = $item_list;
            $i++;
        }
        $doc = J2Store::platform()->application()->getDocument();
        $doc->addScriptDeclaration(json_encode($schema), 'application/ld+json');
    }

    function onJ2StoreViewProduct(&$item, &$view)
    {
        $product_url = rtrim(JUri::base(), '/') . '/' . ltrim($item->product_link, '/');
        $currency = J2store::currency();
        $item_list = array(
            '@context' => 'https://schema.org/',
            '@type' => "Product",
            'name' => $item->product_name,
            'sku' => (isset($item->variant->sku) && !empty($item->variant->sku)) ? $item->variant->sku : '',
            'url' => $product_url,
            //'position' => 1
        );
        if (isset($item->variant->j2store_variant_id) && !empty($item->variant->j2store_variant_id)) {
            $item_list['offers'] = array(
                '@type' => 'Offer',
                'price' => isset($item->pricing->price) ? round($item->pricing->price, 2) : 0,
                'priceCurrency' => $currency->getCode(),
                'url' => $product_url
            );
            $item_list['offers']['availability'] = 'https://schema.org/' . ($item->variant->availability ? 'InStock' : 'OutOfStock');

        }

        if (isset($item->main_image) && !empty($item->main_image)) {
            $main_image = rtrim(JUri::base(), '/') . '/' . ltrim($item->main_image, '/');
            $item_list['image'] = $main_image;
        } elseif (isset($item->thumb_image) && !empty($item->thumb_image)) {
            $thumb_image = rtrim(JUri::base(), '/') . '/' . ltrim($item->thumb_image, '/');
            $item_list['image'] = $thumb_image;
        }
        if (isset($item->brand_name) && !empty($item->brand_name)) {
            $item_list['brand'] = $item->brand_name;
        }
        if (isset($item->introtext) && !empty($item->introtext)) {
            $item_list['description'] = substr($item->introtext, 0, 200);
        }
        $doc = J2Store::platform()->application()->getDocument();
        $doc->addScriptDeclaration(json_encode($item_list), 'application/ld+json');
    }
}