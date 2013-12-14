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
 * The Type Save Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerSave extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 */
	public $prefix = 'Types';

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

		$data   = $this->input->get('jform', array(), 'array');
		$layout_name = empty($data['layout_name']) ? 'form' : $data['layout_name'];
		$type_id = $this->input->getInt('type_id');
		$model  = new TypesModelType;
		$form   = $model->getForm($data, false);


		// Validate the posted data.
		$dataValidated = $model->validate($form, $data);

		// Check for validation errors.
		if ($dataValidated === false)
		{
			// Save the data in the session.
			$this->app->setUserState('com_types.type.edit.data', $data);

			// Redirect back to the edit screen.
			$this->app->redirect(JRoute::_('index.php?option=com_types&task=type.edit&type_id=' . $type_id . '&layout_name=' . $layout_name, false));
		}

		// Attempt to save the data.
		try
		{
			$model->save($data);
		}
		catch (RuntimeException $e)
		{
			// Save the data in the session.
			$this->app->setUserState('com_types.type.edit.data', $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->enqueueMessage(JText::sprintf('JERROR_SAVE_FAILED', $e->getMessage()), 'error');
			$this->app->redirect(JRoute::_('index.php?option=com_types&task=type.edit&type_id=' . $type_id . '&layout_name=' . $layout_name, false));
		}

		// clear state
		$this->app->setUserState('com_types.type.edit.data', null);
		// redirect
		$this->app->enqueueMessage(JText::_('COM_TYPES_TYPE_SAVE_SUCCESS'));
		if(!empty($this->tasks[2]) && $this->tasks[2] == 'apply')
		{
			$this->app->redirect(JRoute::_('index.php?option=com_types&task=type.edit&type_id=' . $type_id . '&layout_name=' . $layout_name, false));
		}
		else {
			$this->app->redirect('index.php?option=com_types');
		}
	}

}

