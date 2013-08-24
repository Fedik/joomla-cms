<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * display a form:
 * 	index.php?option=com_page&view=item&type=com_content.article&layout_name=form&id=1
 * display full:
 * 	index.php?option=com_page&view=item&type=com_content.article&layout_name=fullview&id=1
 * display list:
 * 	index.php?option=com_page&view=items&type=com_content.article&layout_name=intro&filter[core_catid]=1&filter[core_tag][0]=tag1&order[core_created_time]=asc
 * display custom page:
 * 	index.php?option=com_page&view=page&layout_id=2&filter[core_catid]=1&order[core_created_time]=desc
 */

defined('_JEXEC') or die;

// TODO: move to common place
JLoader::register('UCMTypeHelper', __DIR__ . '/helpers/ucmtypehelper.php');
JLoader::register('UcmItem', __DIR__ . '/helpers/ucmitem.php');

$controller = JControllerLegacy::getInstance('Page');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

