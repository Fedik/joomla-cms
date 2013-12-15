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
 * The Type Cancel Base Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerBaseCancel extends JControllerBase
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
		$this->app->setUserState($this->context, null);
		// redirect
		$this->app->redirect(JRoute::_($this->redirect, false));
	}

}
