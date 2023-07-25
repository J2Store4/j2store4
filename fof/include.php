<?php
/**
 *  @package     FrameworkOnFramework
 *  @subpackage  include
 *  @copyright   Copyright (C) 2010-2015 Nicholas K. Dionysopoulos
 *  @license     GNU General Public License version 2, or later
 *
 *  Initializes F0F
 */

defined('_JEXEC') or die();

if (!defined('F0F_INCLUDED'))
{
    define('F0F_INCLUDED', 'revAA17947');

	// Register the F0F autoloader
    require_once __DIR__ . '/autoloader/fof.php';
	F0FAutoloaderFof::init();

	// Register a debug log
	if (defined('JDEBUG') && JDEBUG)
	{
		F0FPlatform::getInstance()->logAddLogger('fof.log.php');
	}
}