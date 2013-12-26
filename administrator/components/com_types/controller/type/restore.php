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
 * The Type Restore Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerTypeRestore extends JControllerBase
{

	/**
	 * Execute the controller.
	 *
	 * @return  void
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		// Init variables
		$data  = $this->input->post->get('jform', array(), 'array');
		$layout_name = empty($data['layout']['layout_name']) ? 'form' : $data['layout']['layout_name'];
		$type_parts = explode('.', $data['type_alias']);

		if(UcmTypeHelper::importContentType($type_parts[0], $type_parts[1]))
		{
			$this->app->enqueueMessage('Restore Success!');
		}
		// Redirect back.
		$this->app->redirect(JRoute::_('index.php?option=com_types&task=types.edit.type&type_id=' . $data['type_id'] . '&layout_name=' . $layout_name, false));

	}

}

