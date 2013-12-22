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
		// fake select for test

		$attr = '';
		$values = $this->value ? $this->value : array('name' => '', 'params' => '');

		$options = array(
				JHtml::_('select.option', '', 'Default'),
				JHtml::_('select.option', 'heading', 'Heading'),
				JHtml::_('select.option', 'link', 'Link'),
				JHtml::_('select.option', 'link_modal', 'Link Modal'),
				JHtml::_('select.option', 'date', 'Date'),
				JHtml::_('select.option', 'image', 'Image'),
				JHtml::_('select.option', 'image_modal', 'Image Modal'),
		);

		$html  = JHtml::_('select.genericlist', $options, $this->name . '[name]', $attr, 'value', 'text', $values['name'], $this->id);
		$html .= 'Params: <input type="text" name="' . $this->name . '[params]" value="' . $values['params'] . '" />';

		return $html;
	}
}
