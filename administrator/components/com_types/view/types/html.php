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
 * Types view class for the Types package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class TypesViewTypesHtml extends JViewHtml
{

	/**
	 * Display the view
	 */
	public function render()
	{
		try
		{
			$this->state = $this->model->getState();
			$this->items = $this->model->getItems();
			//$this->pagination = $this->model->getPagination();
		}
		catch (Exception $e)
		{

			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::render();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{

	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 */
	protected function getSortFields()
	{
		return array(
			't.type_title' => JText::_('JGLOBAL_TITLE'),
			't.type_id' => JText::_('JGRID_HEADING_ID')
		);
	}

}
