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
 * The Type Save Base Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerBaseSave extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 */
	public $prefix = 'Types';

	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 */
	public $context = 'com_types.type';

	/**
	 * Redirect url
	 *
	 * @var string
	 */
	public $redirect = 'index.php';

	/**
	 * Redirect url if error hapened
	 *
	 * @var string
	 */
	public $redirect_error = 'index.php';

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

		// Check for validation errors.
		if ($dataValidated === false)
		{
			// Save the data in the session.
			$this->app->setUserState($this->context, $data);

			// Redirect back to the edit screen.
			$this->app->redirect(JRoute::_($this->redirect_error, false));
		}

		// Attempt to save the data.
		try
		{
			$model->save($data);
		}
		catch (RuntimeException $e)
		{
			// Save the data in the session.
			$this->app->setUserState($this->context, $data);

			// Save failed, go back to the screen and display a notice.
			$this->app->enqueueMessage(JText::sprintf('JERROR_SAVE_FAILED', $e->getMessage()), 'error');
			$this->app->redirect(JRoute::_($this->redirect_error, false));
		}

		// clear state
		$this->app->setUserState($this->context, null);
		// redirect
		$this->app->enqueueMessage(JText::_('COM_TYPES_SAVE_SUCCESS'));
		$this->app->redirect(JRoute::_($this->redirect, false));
	}

}

