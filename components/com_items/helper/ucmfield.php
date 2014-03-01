<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * UCM Form Field Class
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 */
class JUcmField //extends JFormField
{
	/**
	 * Field_layout instance id in the database
	 *
	 * @var unknown
	 */
	protected $id = null;

	/**
	 * Field id in the database
	 *
	 * @var unknown
	 */
	protected $field_id = null;

	/**
	 * Field Type
	 */
	protected $field_type;

	/**
	 * Field Name
	 */
	protected $field_name;

	/**
	 * Status published/unpublished
	 */
	protected $state = 0;

	/**
	 * Access
	 */
	protected $access = 1;

	/**
	 * Field Label
	 */
	public $label;

	/**
	 * Field Ordering
	 */
	public $ordering = 0;

	/**
	 * Field value
	 */
	public $value = null;

	/**
	 * Formated value
	 */
	public $format = null;

	/**
	 * Configuration for a Field
	 */
	public $params;

	/**
	 * Construct.
	 *
	 * @param object  $field  The field info.
	 */
	public function __construct($field)
	{
		// Prepare params
		$field->params = !empty($field->params) ? $field->params : '';

		// merge
		foreach($field as $k => $v)	{
			if($k == 'params')
			{
				$this->params = new JRegistry($v);
				continue;
			}
			$this->$k = $v;
		}

		//label
		$this->label = $this->params->get('label', '');
	}

	/**
	 * Method to get certain data.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 */
	public function __get($name)
	{
		// format value
		if($name == 'format')
		{
			$this->formatValue();
			return $this->format;
		}

		if(property_exists($this, $name))
		{
			return $this->$name;
		}
		return null;
	}

	/**
	 * Define field value
	 *
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function setValue($value)
	{
		$this->value = $value;
		// TODO: format it only when it need, not here !!!
		$this->formatValue();
		return $this;
	}

	/**
	 * Format value
	 *
	 * @return $this
	 */
	public function formatValue()
	{
		//TODO: make format work
		$this->format = $this->value;
		return $this;
	}

}
