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

		$this->state = $this->model->getState();
		$this->item  = $this->model->getItemUcm();
		//$this->form  = $this->model->getForm();

		//TODO: make it work
		$this->params = new JRegistry();

		//var_dump($this->item);

		return parent::render();
	}
}
