<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Types Component Type Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_page
 *
 */
class PageModelItemForm extends JModelAdmin
{
	/**
	 * Method to get item data.
	 *
	 * @param   integer  An optional ID
	 *
	 * @return  object
	 *
	 */
	public function getItem($pk = null)
	{

	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = null;
		return $form;
	}
}