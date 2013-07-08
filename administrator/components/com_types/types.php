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
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::register('UCMTypeHelper', __DIR__ . '/helpers/ucmtypehelper.php');

//test here hehe
//UCMTypeHelper::importContentType('com_content');exit;


$controller = JControllerLegacy::getInstance('Types');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();