<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_items
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ItemsViewItemHtml extends JViewHtml
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
			$this->item  = $this->model->getItem();
			//$this->form  = $this->model->getForm();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return parent::render();
	}
}
