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
 * The Type Layout Cancel Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerLayoutCancel extends TypesControllerBaseCancel
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

		$type_id = $this->app->input->getInt('type_id');
		// clear state
		$this->context = 'com_types.layout.edit.data';
		// redirect
		$this->redirect = 'index.php?option=com_types&task=types.edit.type&type_id=' . $type_id . '&layout_name=form';

		return parent::execute();
	}

}
