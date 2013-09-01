<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * UCM Item class.
 *
 * @package     Joomla.Administrator
 */
class UcmItem
{
	/**
	 * Item data
	 */
	protected $data;

	/**
	 * Layout name for current item instance
	 */
	protected $layout_name;

	/**
	 * Active fields instances
	 */
	protected $fields_inst;

	/**
	 * Active fields array
	 */
	public $fields;

	/**
	 * JUcmType object
	 */
	public $type;

	/**
	 * Instantiate UCMItem.
	 *
	 * @param   array    $data  The item data
	 * @param   array     $fields  active fields instances
	 * @param   JUcmType  $type   The type object
	 *
	 */
	public function __construct($data, $fields, $layout_name, JUcmType $type)
	{
		// Keep given info
		$this->type = $type;
		$this->data = (array) $data;
		$this->layout_name = $layout_name;
		$this->fields_inst = $fields;
		$this->fields = array_keys($fields);

	}

	/**
	 * Method to get certain data.
	 * Like: $UcmItem->field_name->format - return formated value for this field
	 * 		$UcmItem->field_name->value - return value without formating
	 * 		$UcmItem->field_name->label - return label
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 */
	public function __get($name)
	{
		// Return UcmField instance for fields that enabled
		if(!empty($this->fields_inst[$name]))
		{
			if(isset($this->data[$name]) && empty($this->fields_inst[$name]->value))
			{
				$this->fields_inst[$name]->setValue($this->data[$name]);
			}
			return $this->fields_inst[$name];
		}
		// Return stdClass for fields that no enabled
		if(isset($this->data[$name]))
		{
			return (object) array(
				'value' => $this->data[$name],
				'format' => '',
				'label' => '',
			);
		}
		return null;
	}

	/**
	 * Get layout name
	 * @return string
	 */
	public function getLayoutName()
	{
		return $this->layout_name;
	}

	/**
	 * Get layout path for current item
	 * @return string
	 */
	public function getLayoutPath()
	{
		//TODO: why so deeep ???
		return $this->type->type->type_alias . '.' . $this->layout_name;
	}

}
