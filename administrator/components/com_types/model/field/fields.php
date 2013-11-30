<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the UCM Types component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class JFormFieldFields extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Fields';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 */
	protected function getInput()
	{


		return 'aaa';
	}
}
