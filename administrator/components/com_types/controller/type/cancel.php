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
 * The Type Cancel Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerTypeCancel extends TypesControllerBaseCancel
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
		// clear state
		$this->context = 'com_types.type.edit.data';
		// redirect
		$this->redirect = 'index.php?option=com_types';

		return parent::execute();
	}

}
