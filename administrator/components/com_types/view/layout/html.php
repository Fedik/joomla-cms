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
 * Layout view class for the Types package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class TypesViewLayoutHtml extends JViewHtml
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
		JToolbarHelper::title(JText::_('COM_TYPES_LAYOUT'));

		JToolbarHelper::apply('types.save.layout.apply');
		JToolbarHelper::save('types.save.layout');

		JToolbarHelper::cancel('types.cancel.layout');

	}


}
