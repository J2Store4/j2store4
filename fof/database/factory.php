<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning F0FTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('F0F_INCLUDED') or die;

/**
 * Joomla Platform Database Factory class
 *
 * @since  12.1
 */
class F0FDatabaseFactory
{
	/**
	 * Contains the current F0FDatabaseFactory instance
	 *
	 * @var    F0FDatabaseFactory
	 * @since  12.1
	 */
	private static $_instance = null;

	/**
	 * Method to return a F0FDatabaseDriver instance based on the given options. There are three global options and then
	 * the rest are specific to the database driver. The 'database' option determines which database is to
	 * be used for the connection. The 'select' option determines whether the connector should automatically select
	 * the chosen database.
	 *
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method.  This ensures that we don't end up with unnecessary database connection resources.
	 *
	 * @param   string  $name     Name of the database driver you'd like to instantiate
	 * @param   array   $options  Parameters to be passed to the database driver.
	 *
	 * @return  F0FDatabaseDriver  A database driver object.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getDriver($name = 'joomla', $options = array())
	{
		// Sanitize the database connector options.
		$options['driver']   = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		$options['database'] = (isset($options['database'])) ? $options['database'] : null;
		$options['select']   = (isset($options['select'])) ? $options['select'] : true;

		// Derive the class name from the driver.
		$class = 'F0FDatabaseDriver' . ucfirst(strtolower($options['driver']));

		// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
		if (!class_exists($class))
		{
			throw new RuntimeException(sprintf('Unable to load Database Driver: %s', $options['driver']));
		}

		// Create our new F0FDatabaseDriver connector based on the options given.
		try
		{
			$instance = new $class($options);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()), $e->getCode(), $e);
		}

		return $instance;
	}

	/**
	 * Gets an instance of the factory object.
	 *
	 * @return  F0FDatabaseFactory
	 *
	 * @since   12.1
	 */
	public static function getInstance()
	{
		return self::$_instance ? self::$_instance : new F0FDatabaseFactory;
	}

	/**
	 * Get the current query object or a new F0FDatabaseQuery object.
	 *
	 * @param   string           $name  Name of the driver you want an query object for.
	 * @param   F0FDatabaseDriver  $db    Optional F0FDatabaseDriver instance
	 *
	 * @return  F0FDatabaseQuery  The current query object or a new object extending the F0FDatabaseQuery class.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getQuery($name, F0FDatabaseDriver $db = null)
	{
		// Derive the class name from the driver.
		$class = 'F0FDatabaseQuery' . ucfirst(strtolower($name));

		// Make sure we have a query class for this driver.
		if (!class_exists($class))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new RuntimeException('Database Query class not found');
		}

		return new $class($db);
	}

	/**
	 * Gets an instance of a factory object to return on subsequent calls of getInstance.
	 *
	 * @param   F0FDatabaseFactory  $instance  A F0FDatabaseFactory object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public static function setInstance(F0FDatabaseFactory $instance = null)
	{
		self::$_instance = $instance;
	}
}
