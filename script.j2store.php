<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

// Load FOF if not already loaded
if (!defined('F0F_INCLUDED')) {
    $paths = array(
        (defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_ROOT . '/libraries') . '/f0f/include.php',
        __DIR__ . '/fof/include.php',
    );

    foreach ($paths as $filePath) {
        if (!defined('F0F_INCLUDED') && file_exists($filePath)) {
            @include_once $filePath;
        }
    }
}

// Pre-load the installer script class from our own copy of FOF
if (!class_exists('F0FUtilsInstallscript', false)) {
    @include_once __DIR__ . '/fof/utils/installscript/installscript.php';
}

// Pre-load the database schema installer class from our own copy of FOF
if (!class_exists('F0FDatabaseInstaller', false)) {
    @include_once __DIR__ . '/fof/database/installer.php';
}

// Pre-load the update utility class from our own copy of FOF
if (!class_exists('F0FUtilsUpdate', false)) {
    @include_once __DIR__ . '/fof/utils/update/update.php';
}

// Pre-load the cache cleaner utility class from our own copy of FOF
if (!class_exists('F0FUtilsCacheCleaner', false)) {
    @include_once __DIR__ . '/fof/utils/cache/cleaner.php';
}

class Com_J2storeInstallerScript extends F0FUtilsInstallscript
{

    /**
     * The component's name
     *
     * @var   string
     */
    protected $componentName = 'com_j2store';

    /**
     * The title of the component (printed on installation and uninstallation messages)
     *
     * @var string
     */
    protected $componentTitle = 'J2Store Joomla Shopping cart';


    protected $minimumJoomlaVersion = '3.9.0';
    protected $maximumJoomlaVersion = '5.99.99';

    protected $removeFilesAllVersions = array(
        'files' => array(
            // Use pathnames relative to your site's root, e.g.
            // 'administrator/components/com_foobar/helpers/whatever.php'
            'components/com_j2store/views/products/tmpl/default.html',
            'components/com_j2store/views/products/tmpl/default.php',
            'components/com_j2store/views/products/tmpl/default_cart.php',
            'components/com_j2store/views/products/tmpl/default_filters.php',
            'components/com_j2store/views/products/tmpl/default_general.php',
            'components/com_j2store/views/products/tmpl/default_images.php',
            'components/com_j2store/views/products/tmpl/default_inventory.php',
            'components/com_j2store/views/products/tmpl/default_item.php',
            'components/com_j2store/views/products/tmpl/default_modules.php',
            'components/com_j2store/views/products/tmpl/default_price.php'
        ),
        'folders' => array(
            // Use pathnames relative to your site's root, e.g.
            // 'administrator/components/com_foobar/baz'
            'plugins/j2store/tool_localization_data',
            'plugins/j2store/tool_diagnostics'
        )
    );


    /**
     * The list of extra modules and plugins to install on component installation / update and remove on component
     * uninstallation.
     *
     * @var   array
     */
    protected $installation_queue = array(        // modules => { (folder) => { (module) => { (position), (published) } }* }*
        'modules' => array(
            'admin' => array(
                'j2store_chart' => array('j2store-module-position-3', 1),
                'j2store_stats_mini' => array('j2store-module-position-1', 1),
                'j2store_orders' => array('j2store-module-position-4', 1),
                'j2store_stats' => array('j2store-module-position-5', 1),
                'j2store_menu' => array('menu', 1)
            ),

            'site' => array(
                'mod_j2store_currency' => array('left', 0),
                'mod_j2store_cart' => array('left', 0),
                'mod_j2store_products_advanced' => array('j2store-product-module', 0)
            )
        ),
        'plugins' => array(
            'content' => array('j2store' => 1),
            'system' => array(
                'j2store' => 1,
                'j2pagecache' => 0,
                'j2canonical' => 0
            ),
            'search' => array('j2store' => 0),
            'finder' => array('j2store' => 0),
            'user' => array('j2userregister' => 0),
            'installer' => array('j2store' => 1),
            'j2store' => array(
                'shipping_free' => 0,
                'shipping_standard' => 1,
                'payment_cash' => 1,
                'payment_moneyorder' => 1,
                'payment_banktransfer' => 1,
                'payment_paypal' => 1,
                'report_products' => 1,
                'payment_sagepayform' => 1,
                'report_itemised' => 1,
                'app_localization_data' => 1,
                'app_diagnostics' => 1,
                'app_currencyupdater' => 1,
                'app_flexivariable' => 1,
                'app_schemaproducts' => 1,
                'app_bootstrap3' => 0,
                'app_bootstrap4' => 1,
                'app_bootstrap5' => 0
            )
        )
    );


    public function preflight($type, $parent)
    {

        if (parent::preflight($type, $parent)) {

            $app = JFactory::getApplication();
            $configuration = JFactory::getConfig();
            $db = JFactory::getDbo();
            //check of curl is present
            if (!function_exists('curl_init') || !is_callable('curl_init')) {

                $msg = "<p>cURL extension is not enabled in your PHP installation. Please contact your hosting service provider</p>";

                if (version_compare(JVERSION, '3.0', 'gt')) {
                    JLog::add($msg, JLog::WARNING, 'jerror');
                } else {
                    $app->enqueueMessage($msg, 'error');
                }
                return false;
            }

            if (!function_exists('json_encode')) {

                $msg = "<p>JSON extension is not enabled in your PHP installation. Please contact your hosting service provider</p>";

                if (version_compare(JVERSION, '3.0', 'gt')) {
                    JLog::add($msg, JLog::WARNING, 'jerror');
                } else {
                    $app->enqueueMessage($msg, 'error');
                }
                return false;
            }

            //get the table list
            $alltables = $db->getTableList();
            //get prefix
            $prefix = $db->getPrefix();
            //conservative method
            $xmlfile = JPATH_ADMINISTRATOR . '/components/com_j2store/manifest.xml';
            if (JFile::exists($xmlfile)) {
                $xml = JFactory::getXML($xmlfile);
                $version = (string)$xml->version;
                if (version_compare($version, '3.9.99', 'lt')) {
                    $parent->getParent()->abort('You cannot install J2Store Version 4 over the old versions directly. A migration tool should be used first to migrate your previous store data.');
                    return false;
                }
            }

            //let us check the manifest cache as well. Cannot trust joomla installer
            $query = $db->getQuery(true);
            $query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_j2store'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            $db->setQuery($query);
            $result = $db->loadResult();

            if ($result) {
                $manifest = json_decode($result);
                $version = $manifest->version;
                if (!empty($version)) {
                    // abort if the current J2Store release is older
                    /*if( version_compare( $version, '3.9.99', 'lt' ) ) {
                        $parent->getParent()->abort('You cannot install J2Store Version 4 over the old versions directly. A migration tool should be used first to migrate your previous store data.');
                        return false;
                    }*/
                    if (version_compare($version, '3.9.99', 'lt')) {
                        if (!JComponentHelper::isEnabled('com_extensioncheck')) {
                            $parent->getParent()->abort('J2Store v4 Migration components is not found. Please install J2Store v4 migration component before update the J2Store 4');
                            return false;
                        }
                        if (!in_array($prefix . 'extension_check', $alltables)) {
                            $parent->getParent()->abort('J2Store v4 Migration components is not found. Please install J2Store v4 migration component before update the J2Store 4');
                            return false;
                        }
                        $query = "SELECT * FROM #__extension_check";
                        $db->setQuery($query);
                        $result = $db->loadObjectList();
                        if (empty($result)) {
                            $parent->getParent()->abort('J2Store v4 Migration first, then install J2Store 4');
                            return false;
                        }
                        foreach ($result as $key => $value) {
                            if (empty($value->installation_status)) {
                                $parent->getParent()->abort('You did not complete J2Store 4 Migration steps,So before install J2Store 4, do J2Store 4 migration');
                                return false;
                            }
                        }
                    }

                }
            }

            //some times the user might have uninstalled v2 and try installing v3. Let us stop them doing so.
            //check for the prices table. It he has the prices table, then he is certainly having the old version.

            if (in_array($prefix . 'j2store_prices', $alltables)) {
                //user has the prices table. So the old version data might be there.
                $parent->getParent()->abort('Tables of J2Store Version 2.x found. If you have already installed J2Store Version 2, its tables might be there. If you do not have any data in those tables, then you can delete those tables via the phpmyadmin and then install J2store version 3. Otherwise, you will have to use our migration tool');
                return false;
            }


            //if we are here, then all checks are passed. Let us allow the user to install J2Store Version 3. Just make sure to remove the template overrides and incompatible modules

            //----file removal//
            //check in the template overrides.

            //first get the default template
            $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
            $db->setQuery($query);
            $template = $db->loadResult();

            $template_path = JPATH_SITE . '/templates/' . $template . '/html';
            $com_override_path = $template_path . '/com_j2store';

            //j2store overrides - mycart
            if (JFolder::exists($com_override_path . '/carts')) {
                if (JFile::exists($com_override_path . '/carts/default_items.php')) {

                    if (!JFolder::move($com_override_path . '/carts/default_items.php', $com_override_path . '/carts/old_default_items.php')) {
                        $parent->getParent()->abort('Could not move file ' . $com_override_path . '/carts/default_items.php. It might be having old code. So please Check permissions and rename this file. ');
                        return false;
                    }
                }

            }

            if (JFolder::exists($com_override_path . '/cart')) {
                if (JFile::exists($com_override_path . '/cart/default_items.php')) {

                    if (!JFolder::move($com_override_path . '/cart/default_items.php', $com_override_path . '/cart/old_default_items.php')) {
                        $parent->getParent()->abort('Could not move file ' . $com_override_path . '/cart/default_items.php. It might be having old code. So please Check permissions and rename this file. ');
                        return false;
                    }
                }

            }

            //the following renaming should happen only during new installs. If its an update, then these issues probably taken care of.

            if ($type != 'update') {
                if (JFolder::exists($com_override_path . '/checkout')) {
                    if (JFile::exists($com_override_path . '/checkout/shipping_yes.php')) {

                        if (!JFolder::move($com_override_path . '/checkout/shipping_yes.php', $com_override_path . '/checkout/old_shipping_yes.php')) {
                            $parent->getParent()->abort('Could not move file ' . $com_override_path . '/checkout/shipping_yes.php. It might be having old code. So please Check permissions and rename this file. ');
                            return false;
                        }
                    }

                }


                //j2store overrides - products
                if (JFolder::exists($com_override_path . '/products')) {
                    if (JFolder::exists($com_override_path . '/old_products')) {
                        if (!JFolder::delete($com_override_path . '/old_products')) {
                            $parent->getParent()->abort('Could not delete folder ' . $com_override_path . '/products  Check permissions.');
                            return false;
                        }
                    }
                    if (!JFolder::move($com_override_path . '/products', $com_override_path . '/old_products')) {
                        $parent->getParent()->abort('Could not move folder ' . $com_override_path . '/products. Check permissions.');
                        return false;
                    }

                }

            }
            if (version_compare(JVERSION, '3.99.99', 'ge') && isset($this->installation_queue) && isset($this->installation_queue['modules']['admin']['j2store_menu'])) {
                $this->installation_queue['modules']['admin']['j2store_menu'] = array('status', 1);
            }
            //----end of file removal//
            //all set. Lets rock..
            return true;
        } else {
            return false;
        }
    }

    public function uninstall($parent)
    {

        // Uninstall database
        $dbInstaller = new F0FDatabaseInstaller(array(
            'dbinstaller_directory' =>
                ($this->schemaXmlPathRelative ? JPATH_ADMINISTRATOR . '/components/' . $this->componentName : '') . '/' .
                $this->schemaXmlPath
        ));

        // Uninstall modules and plugins
        $status = $this->uninstallSubextensions($parent);

        // Uninstall post-installation messages on Joomla! 3.2 and later
        $this->uninstallPostInstallationMessages();

        // Show the post-uninstallation page
        $this->renderPostUninstallation($status, $parent);

    }

    protected function renderPostInstallation($status, $fofInstallationStatus, $strapperInstallationStatus, $parent)
    {
        $fofInstallationStatus = $this->_installFOF($parent);
        $this->_installLocalisation($parent);
    }

    private function _installFOF($parent)
    {
        $src = $parent->getParent()->getPath('source');

        // Load dependencies
        JLoader::import('joomla.filesystem.file');
        JLoader::import('joomla.utilities.date');
        $source = $src . '/fof';

        if (!defined('JPATH_LIBRARIES')) {
            $target = JPATH_ROOT . '/libraries/f0f';
        } else {
            $target = JPATH_LIBRARIES . '/f0f';
        }
        $haveToInstallFOF = false;

        if (!is_dir($target)) {
            $haveToInstallFOF = true;
        } else {
            $fofVersion = array();

            if (file_exists($target . '/version.txt')) {
                $rawData = file_get_contents($target . '/version.txt');
                $info = explode("\n", $rawData);
                $fofVersion['installed'] = array(
                    'version' => trim($info[0]),
                    'date' => new JDate(trim($info[1]))
                );
            } else {
                $fofVersion['installed'] = array(
                    'version' => '0.0',
                    'date' => new JDate('2011-01-01')
                );
            }

            $rawData = file_get_contents($source . '/version.txt');
            $info = explode("\n", $rawData);
            $fofVersion['package'] = array(
                'version' => trim($info[0]),
                'date' => new JDate(trim($info[1]))
            );

            $haveToInstallFOF = $fofVersion['package']['date']->toUNIX() > $fofVersion['installed']['date']->toUNIX();
        }

        $installedFOF = false;

        if ($haveToInstallFOF) {
            $versionSource = 'package';
            $installer = new JInstaller;
            $installedFOF = $installer->install($source);
        } else {
            $versionSource = 'installed';
        }

        if (!isset($fofVersion)) {
            $fofVersion = array();

            if (file_exists($target . '/version.txt')) {
                $rawData = file_get_contents($target . '/version.txt');
                $info = explode("\n", $rawData);
                $fofVersion['installed'] = array(
                    'version' => trim($info[0]),
                    'date' => new JDate(trim($info[1]))
                );
            } else {
                $fofVersion['installed'] = array(
                    'version' => '0.0',
                    'date' => new JDate('2011-01-01')
                );
            }

            $rawData = file_get_contents($source . '/version.txt');
            $info = explode("\n", $rawData);
            $fofVersion['package'] = array(
                'version' => trim($info[0]),
                'date' => new JDate(trim($info[1]))
            );
            $versionSource = 'installed';
        }

        if (!($fofVersion[$versionSource]['date'] instanceof JDate)) {
            $fofVersion[$versionSource]['date'] = new JDate;
        }

        return array(
            'required' => $haveToInstallFOF,
            'installed' => $installedFOF,
            'version' => $fofVersion[$versionSource]['version'],
            'date' => $fofVersion[$versionSource]['date']->format('Y-m-d'),
        );
    }

    function _installLocalisation($parent)
    {


        $installer = $parent->getParent();

        $db = JFactory::getDbo();
        //get the table list
        $alltables = $db->getTableList();
        //get prefix
        $prefix = $db->getPrefix();
        // we have to seperate try catch , because may country install fail, zone table also get affect install
        try {
            $country_status = false;
            if (!in_array($prefix . 'j2store_countries', $alltables)) {
                $country_status = true;
            } else {
                $query = $db->getQuery(true);
                $query->select('*')->from('#__j2store_countries');
                $db->setQuery($query);
                $country_list = $db->loadAssocList();
                if (count($country_list) < 1) {
                    $country_status = true;
                }
            }

            if ($country_status) {
                //countries
                $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/countries.sql';
                $this->_executeSQLFiles($sql);
            }
        } catch (Exception $e) {
            // do nothing
        }

        try {
            $zone_status = false;
            if (!in_array($prefix . 'j2store_zones', $alltables)) {
                $zone_status = true;
            } else {
                $query = $db->getQuery(true);
                $query->select('*')->from('#__j2store_zones');
                $db->setQuery($query);
                $zone_list = $db->loadAssocList();
                if (count($zone_list) < 1) {
                    $zone_status = true;
                }
            }

            if ($zone_status) {
                //zones
                $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/zones.sql';
                $this->_executeSQLFiles($sql);
            }
        } catch (Exception $e) {
            // do nothing
        }

        try {
            //metrics
            $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/lengths.sql';
            $this->_executeSQLFiles($sql);

            $sql = $installer->getPath('source') . '/administrator/components/com_j2store/sql/install/mysql/weights.sql';
            $this->_executeSQLFiles($sql);
        } catch (Exception $e) {

        }

        // ALTER IGNORE removed in latest mysql version
        $query = 'SHOW INDEX FROM `#__j2store_productquantities`';
        $db->setQuery($query);
        $product_qty_index = $db->loadObjectList();
        $add_index = true;
        foreach ($product_qty_index as $pro_qty_index) {
            if (in_array($pro_qty_index->Key_name, array('variantidx'))) {
                $add_index = false;
                break;
            }
        }
        if ($add_index) {
            try {
                $query = 'ALTER TABLE #__j2store_productquantities ADD UNIQUE INDEX variantidx (variant_id)';
                $this->_sqlexecute($query);
            } catch (Exception $e) {

            }

        }
        //remove duplicates from the product quantities table
        /*$query = 'ALTER TABLE #__j2store_productquantities ENGINE MyISAM;
        ALTER IGNORE TABLE #__j2store_productquantities ADD UNIQUE INDEX variantidx (variant_id);
        ALTER TABLE #__j2store_productquantities ENGINE InnoDB;';
        $this->_sqlexecute($query);*/

    }

    private function _executeSQLFiles($sql)
    {
        if (JFile::exists($sql)) {
            $db = JFactory::getDbo();
            $queries = JDatabaseDriver::splitSql(file_get_contents($sql));
            foreach ($queries as $query) {
                $query = trim($query);
                if ($query != '' && $query[0] != '#') {
                    $db->setQuery($query);
                    try {
                        $db->execute();
                    } catch (Exception $e) {
                        //do nothing as customer can do this very well by going to the tools menu
                    }
                }
            }
        }
    }

    private function _sqlexecute($query)
    {
        $db = JFactory::getDbo();
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (Exception $e) {
            //do nothing as customer can do this very well by going to the tools menu
        }
    }


}

