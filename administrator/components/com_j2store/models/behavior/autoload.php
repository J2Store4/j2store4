<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

function behaviorAutoloader($class) {

	if (strpos($class, 'Behavior') === false)
	{
		return;
	}
$className = $class;
	$class = preg_replace('/(\s)+/', '_', $class);
	$class = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class));
	$parts = explode('_', $class);
	$last_key = key( array_slice( $parts, -1, 1, TRUE ) );
	$path = __DIR__.DIRECTORY_SEPARATOR.$parts[$last_key].'.php';

	if (file_exists($path ) && is_readable($path ) && !class_exists($className)) {
		require_once $path;
	}

}

spl_autoload_register('behaviorAutoloader');