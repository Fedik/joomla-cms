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
 * The Type Display Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerTypeEdit extends TypesControllerBaseDisplay
{
	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		$this->input->set('layout', 'edit');
		return parent::execute();
	}

}
