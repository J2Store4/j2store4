<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/**
 * ensure this file is being included by a parent file
 */
defined('_JEXEC') or die ('Restricted access');

class J2StoreAppModel extends F0FModel
{

    public $_element = '';

    /**
     * Method to get a form object.
     *
     * @param string $name The name of the form.
     * @param string $source The form filename (e.g. form.browse)
     * @param array $options Optional array of options for the form creation.
     * @param boolean $clear Optional argument to force load a new form.
     * @param bool|string $xpath An optional xpath to search for the fields.
     *
     * @return  mixed  F0FForm object on success, False on error.
     *
     * @throws  Exception
     *
     * @see     F0FForm
     * @since   2.0
     */
    protected function loadForm($name, $source, $options = array(), $clear = false, $xpath = 'config')
    {

        if (empty($this->_element)) return parent::loadForm($name, $source, $options, $clear, $xpath);

        // Handle the optional arguments.
        $options['control'] = isset($options['control']) ? $options['control'] : false;

        // Create a signature hash.
        $hash = md5($source . serialize($options));

        // Check if we can use a previously loaded form.
        if (isset($this->_forms[$hash]) && !$clear) {
            return $this->_forms[$hash];
        }

        // Try to find the name and path of the form to load
        $paths = array();
        $paths[] = JPATH_SITE . '/plugins/j2store/' . $this->_element;
        $name = $this->_element;
        $source = $this->_element;
        $formFilename = $this->findFormFilename($source, $paths);

        // No form found? Quit!
        if ($formFilename === false) {
            return false;
        }

        // Set up the form name and path
        $source = basename($formFilename, '.xml');
        F0FForm::addFormPath(dirname($formFilename));

        // Set up field paths
        $option = $this->input->getCmd('option', 'com_foobar');
        $componentPaths = F0FPlatform::getInstance()->getComponentBaseDirs($option);
        $view = $this->name;
        $file_root = $componentPaths['main'];
        $alt_file_root = $componentPaths['alt'];

        F0FForm::addFieldPath($file_root . '/fields');
        F0FForm::addFieldPath($file_root . '/models/fields');
        F0FForm::addFieldPath($alt_file_root . '/fields');
        F0FForm::addFieldPath($alt_file_root . '/models/fields');

        F0FForm::addHeaderPath($file_root . '/fields/header');
        F0FForm::addHeaderPath($file_root . '/models/fields/header');
        F0FForm::addHeaderPath($alt_file_root . '/fields/header');
        F0FForm::addHeaderPath($alt_file_root . '/models/fields/header');

        // Get the form.
        try {
            $form = F0FForm::getInstance($name, $source, $options, false, $xpath);

            if (isset($options['load_data']) && $options['load_data']) {
                // Get the data for the form.
                $data = $this->loadFormData();
            } else {
                $data = array();
            }

            // Allows data and form manipulation before preprocessing the form
            $this->onBeforePreprocessForm($form, $data);

            // Allow for additional modification of the form, and events to be triggered.
            // We pass the data because plugins may require it.
            $this->preprocessForm($form, $data);

            // Allows data and form manipulation After preprocessing the form
            $this->onAfterPreprocessForm($form, $data);

            // Load the data into the form after the plugins have operated.
            $form->bind($data);
        } catch (Exception $e) {
            // The above try-catch statement will catch EVERYTHING, even PhpUnit exceptions while testing
            if (stripos(get_class($e), 'phpunit') !== false) {
                throw $e;
            } else {
                $this->setError($e->getMessage());

                return false;
            }
        }

        // Store the form for later.
        $this->_forms[$hash] = $form;
        return $form;
    }

}