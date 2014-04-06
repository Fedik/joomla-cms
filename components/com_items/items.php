<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_items
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Display items theory
 *
 * display a form:
 * 	index.php?option=com_items&task=items.view.item.form&type_alias=com_content.article&filter[key][value]=1&filter[key][clause]='='
 * display full:
 *  index.php?option=com_items&task=items.view.item.fullview&type_alias=com_content.article&key=1
 * display list:
 * 	index.php?option=com_items&task=items.view.list.intro&type_alias=com_content.article&filter[core_catid]=1&filter[core_tag][0]=tag1&order[core_created_time]=asc
 * display custom page (???):
 * 	index.php?option=com_items&view=page&layout_id=2&filter[core_catid]=1&order[core_created_time]=desc
 */

// TODO: move to common place
JLoader::register('JModelUcm', __DIR__ . '/model/ucm.php');
JLoader::register('JUcmItem', __DIR__ . '/helper/ucmitem.php');
JLoader::register('JUcmField', __DIR__ . '/helper/ucmfield.php');
JLoader::register('JUcmTypeHelper', __DIR__ . '/helper/ucmtypehelper.php');
JLoader::register('JUcmHelper', __DIR__ . '/helper/ucmhelper.php');


// Register a classes
JLoader::registerPrefix('Items', JPATH_COMPONENT);

// Application
$app = JFactory::getApplication();
/*
// test things
// load item
$app->input->set('filter', array(
	'id' => array(
		'value'  => 24,
		'clause' => '=',
		'glue'   => 'AND',
	)
));
*/
// Create the controller
$controllerHelper = new ItemsControllerHelper;
$controller = $controllerHelper->parseController($app);

// Perform the Request task
$controller->execute();

