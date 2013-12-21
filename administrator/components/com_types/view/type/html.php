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
			$this->state = $this->model->getState();
			$this->form  = $this->model->getForm();
			$this->item  = $this->model->getItem();
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
		$app = JFactory::getApplication();

		JToolbarHelper::title(JText::_('COM_TYPES'));

		// do not display on "add new" action
		if($app->input->get('task') == 'types.new.type')
		{
			JToolbarHelper::apply('types.save.type.new');
		}
		else
		{
			JToolbarHelper::apply('types.save.type.apply');
			JToolbarHelper::save('types.save.type');

			JToolbarHelper::custom('types.new.field', 'plus-circle', '', 'COM_TYPES_TOOLBAR_ADDFIELD', false);
			JToolbarHelper::custom('types.new.layout', 'plus-circle', '', 'COM_TYPES_TOOLBAR_LAYOUT', false);

			//TODO: remove this when will be fixed retsoring for the childrent types
			if(count(explode('.', $this->item->type_alias)) < 3)
			{
				$bar = JToolbar::getInstance('toolbar');
				$bar->appendButton('Confirm', 'COM_TYPES_TOOLBAR_RESTORE_MESSAGE', 'refresh', 'COM_TYPES_TOOLBAR_RESTORE', 'types.restore.type', false);
			}
		}
		JToolbarHelper::cancel('types.cancel.type');

	}


}
