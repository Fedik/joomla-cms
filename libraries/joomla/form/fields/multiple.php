<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class
 * Supports a multiple field.
 */
class JFormFieldMultiple extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Multiple';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 */
	public function __construct($form = null)
	{
		parent::__construct($form);

		//it is multiple anyway ;)
		$this->multiple = true;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		//prepare values
		if(is_array($this->value)){
			$values = $this->value;
		} else {
			$min_length = (int) ($this->element['multimin'] ? $this->element['sizemin'] : 1);
			$values = array_fill(0, $min_length, '');
		}

		//get children field type
		$child_type = (string) ($this->element['children'] ? $this->element['children'] : 'text');
		$child = JFormHelper::loadFieldType($child_type);
		$child->setForm($this->form);
		$child->setup($this->element, '');

		//get inputs
		$html = array();
		foreach($values as $k => $v){
			$child->id = $this->id . '_' . $k;
			//@TODO: check whether children is multiple
			$child->name = $this->name . '[' . $k . ']';
			$child->value = $v;

			$html[]= $child->getInput();
		}

		return implode("\n", $html);
	}
}
