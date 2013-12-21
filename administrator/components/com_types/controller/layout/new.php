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
 * The Type Display New Layout Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerLayoutNew extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}
		// Redirect to the edit form.
		$type_id = $this->input->getInt('type_id');
		$redirect = 'index.php?option=com_types&task=types.edit.layout&type_id=' . $type_id;
		$this->app->redirect(JRoute::_($redirect, false));
	}
}
