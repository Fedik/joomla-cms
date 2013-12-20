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
 * The Type Layout Save Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerLayoutSave extends TypesControllerBaseSave
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

		$type_id = $this->input->getInt('type_id');

		//define context
		$this->context = 'com_types.layout.edit.data';

		// Get a model name
		$viewName = empty($this->options[2]) ? '' : $this->options[2];
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		if (!class_exists($modelClass))
		{
			return false;
		}

		$model  = new $modelClass;
		$data   = $this->input->get('jform', array(), 'array');
		$form   = $model->getForm($data, false);

		// Validate the posted data.
		$dataValidated = $model->validate($form, $data);

		//redirect on error
		$this->redirect_error = 'index.php?option=com_types&task=types.edit.layout&type_id=' . $type_id;

		// Check for validation errors.
		if ($dataValidated === false)
		{
			// messages added by model::validate
			// Save the data in the session.
			$this->app->setUserState($this->context, $data);

			// Redirect back to the edit screen.
			$this->app->redirect(JRoute::_($this->redirect_error, false));
		}

		// Attempt to save the data.
		$dataSaved = array();
		try
		{
			$dataSaved = $model->save($data);
		}
		catch (RuntimeException $e)
		{
			// Save the data in the session.
			$this->app->setUserState($this->context, $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->enqueueMessage(JText::sprintf('JERROR_SAVE_FAILED', $e->getMessage()), 'error');
			$this->app->redirect(JRoute::_($this->redirect_error, false));
		}

		// set redirects
		if(!empty($this->options[3]) && $this->options[3] == 'apply')
		{
			$this->redirect = 'index.php?option=com_types&task=types.edit.layout&type_id=' . $type_id . '&layout_id=' . (int) $dataSaved['layout_id'];
		}
		else {
			$this->redirect = 'index.php?option=com_types&task=types.edit.type&type_id=' . $type_id . '&layout_name=' . $dataSaved['layout_name'];
		}

		// clear state
		$this->app->setUserState($this->context, null);
		// redirect
		$this->app->enqueueMessage(JText::_('COM_TYPES_SAVE_SUCCESS'));
		$this->app->redirect(JRoute::_($this->redirect, false));
	}
}
