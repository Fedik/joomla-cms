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
class TypesViewType extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$app->input->set('hidemainmenu', true);
		$this->addToolbar();
		parent::display($tpl);
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
