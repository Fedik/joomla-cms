<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Types Controller Helper
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerHelper
{
	/**
	 * Method to parse a controller from a url
	 * Defaults to the base controllers and passes an array of options.
	 * $options[0] is the location of the controller which defaults to the core libraries (referenced as 'j'
	 * and then the named folder within the component entry point file.
	 * $options[1] is the name of the controller file,
	 * $options[2] is the name of the folder found in the component controller folder for controllers
	 * not prefixed with Types.
	 * Additional options maybe added to parameterise the controller.
	 *
	 * @param   JApplicationBase  $app  An application object
	 *
	 * @return  JController  A JController object
	 * @throws  LogicException when no controller found
	 *
	 */
	public function parseController($app)
	{
		$tasks = array();

		if ($task = $app->input->get('task'))
		{
			// Toolbar expects old style but we are using new style
			// Remove when toolbar can handle either directly
			if (strpos($task, '/') !== false)
			{
				$tasks = explode('/', $task);
			}
			else
			{
				$tasks = explode('.', $task);
			}
		}

		if (empty($tasks[0]))
		{
			$location = 'types';
			$tasks[0] = $location;
		}
		else
		{
			$location = strtolower($tasks[0]);
		}

		if (empty($tasks[1]))
		{
			$activity = 'display';
			$tasks[1] = $activity;
		}
		else
		{
			$activity = strtolower($tasks[1]);
		}

		$view = '';

		if (empty($tasks[2]))
		{
			$view = $location;
			$tasks[2] = $view;
		}
		else
		{
			$view = strtolower($tasks[2]);
		}

		$controllerName = ucfirst($location) . 'Controller' . ucfirst($view) . ucfirst($activity);

		if (!class_exists($controllerName))
		{
			throw new LogicException('Controller "' . $controllerName . '" not exists.', 500);
		}

		$controller = new $controllerName;
		$controller->options = $tasks;

		return $controller;
	}
}
