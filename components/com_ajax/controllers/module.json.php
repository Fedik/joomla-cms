<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Module Controller for JSON format
 *
 * modFooHelper::getAjax() is called where 'foo' is the value
 * of the 'name' variable passed via the URL
 * Example: index.php?option=com_ajax&task=module.call&name=foo&format=json
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
class AjaxControllerModule extends JControllerLegacy
{

	/**
	 * Do job!
	 *
	 */
	public function call()
	{
		// Module name
		$name	= $this->input->get('name');

		// get module helper
		require_once JPATH_COMPONENT.'/helpers/module.php';

		if (!$name || !AjaxModuleHelper::isModuleAvailable($name))
		{
			// Module is not published, you do not have access to it, or it is not assigned to the current menu item
			throw new RuntimeException(JText::sprintf('COM_AJAX_MODULE_NOT_PUBLISHED', $name), 404);
		}

		// Call the module
		$results = AjaxModuleHelper::callModule($name);

		// Output as JSON
		echo new JResponseJson($results, null, false, $this->input->get('ignoreMessages', true, 'bool'));

		return true;
	}
}
