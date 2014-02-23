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
 * 	index.php?option=com_items&task=items.view.item.form&filter[type]=com_content.article&filter[core_content_id]=1
 * display full:
 *  index.php?option=com_items&task=items.view.item.fullview&filter[type]=com_content.article&filter[core_content_id]=1
 * display list:
 * 	index.php?option=com_items&task=items.view.list.intro&filter[type]=com_content.article&filter[core_catid]=1&filter[core_tag][0]=tag1&order[core_created_time]=asc
 * display custom page (???):
 * 	index.php?option=com_items&view=page&layout_id=2&filter[core_catid]=1&order[core_created_time]=desc
 */

// Register a classes
JLoader::registerPrefix('Items', JPATH_COMPONENT);

// Application
$app = JFactory::getApplication();

// Create the controller
$controllerHelper = new ItemsControllerHelper;
$controller = $controllerHelper->parseController($app);

// Perform the Request task
$controller->execute();

