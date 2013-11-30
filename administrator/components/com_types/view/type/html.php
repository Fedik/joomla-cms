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
 * Type view class for the Types package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class TypesViewTypeHtml extends JViewHtml
{

	/**
	 * Display the view
	 */
	public function render()
	{
		$app = JFactory::getApplication();

		try
		{
			$this->form  = $this->model->getForm();
			$this->item  = $this->model->getItem();
			$this->state = $this->model->getState();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		$app->input->set('hidemainmenu', true);

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::render();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_TYPES'));

		JToolbarHelper::apply('type.apply');
		JToolbarHelper::save('type.save');

		JToolbarHelper::custom('type.addField', 'plus-circle', '', 'COM_TYPES_TOOLBAR_ADDFIELD', false);
		JToolbarHelper::custom('type.addLayout', 'plus-circle', '', 'COM_TYPES_TOOLBAR_LAYOUT', false);

		JToolbarHelper::custom('type.restore', 'refresh', '', 'COM_TYPES_TOOLBAR_RESTORE', false);

		JToolbarHelper::cancel('type.cancel');

	}


}
