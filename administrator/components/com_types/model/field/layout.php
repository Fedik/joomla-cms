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
 * Form Field Layout class for the UCM Types component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class JFormFieldLayout extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Layout';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 */
	protected function getInput()
	{
		// toy
		$html = '<select name="' . $this->name . '[name]">
	<option value="">Heading</option>
	<option value="">Link</option>
	<option value="">Link Modal</option>
	<option value="">Date</option>
	<option value="">Image</option>
	<option value="">Image Modal</option>
</select>';
		//toy : lauput params
		$html .= '<input type="text" name="' . $this->name . '[params]" />';

		return $html;
	}
}
