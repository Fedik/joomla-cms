<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_types'))
{
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

// TODO: move to common place
JLoader::register('UcmField', __DIR__ . '/helper/ucmfield.php');
JLoader::register('UcmTypeHelper', __DIR__ . '/helper/ucmtypehelper.php');

// Register a classes
JLoader::registerPrefix('Types', JPATH_COMPONENT);

//test import here hehe
UCMTypeHelper::importContentType('com_content');exit;

// Application
$app = JFactory::getApplication();

// Create the controller
$controllerHelper = new TypesControllerHelper;
$controller = $controllerHelper->parseController($app);

// Perform the Request task
$controller->execute();


