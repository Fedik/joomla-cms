<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

//JLoader::import('joomla.form.form');

/**
 * UCM Form Field Class
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 */
class UCMFormField //extends JFormField
{
	/**
	 * Field id in database
	 *
	 * @var unknown
	 */

	protected $field_id = null;

	/**
	 * Field Type
	 */
	protected $type;

	/**
	 * Field Name
	 */
	protected $name;

	/**
	 * Field Label
	 */
	protected $label;

	/**
	 * Field Ordering
	 */
	public $ordering = 0;

	/**
	 * Status published/unpublished
	 */
	protected $state = 0;

	/**
	 * Related view: form, intro, fullview etc.
	 */
	protected $view = 'form';

	/**
	 * What to display: input or value
	 */
	protected $view_type = 'input';

	/**
	 * Field Value
	 */
	protected $value;

	/**
	 * Validation type
	 */
	protected $validation;

	/**
	 * Filter for filter value
	 */
	protected $filter;

	/**
	 * Field JFormField Element
	 */
	protected $element;

	/**
	 * Configuration for a Field
	 */
	public $params;

	/**
	 * Field Configuration form
	 */
	protected $form_coniguration;

	/**
	 * Field Addittional Configuration form
	 */
	protected $form_coniguration_addittional;

	/**
	 * Construct.
	 *
	 * @param   JFormField  $field  The field object.
	 */
	public function __construct($field = null)
	{
		$this->params = new JRegistry();
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'input':
				if($this->element)
				{
					return $this->element->input;
				}
				break;

			case 'label':
				if ($this->element)
				{
					return $this->element->getLabel();
				}
				return $this->label;

			case 'title':
				if ($this->element)
				{
					return $this->element->getTitle();
				}
				return $this->label;

			default:
				if(property_exists($this, $name))
				{
					return $this->$name;
				}
				break;
		}

		return null;
	}

	/**
	 * Set Up Field from array info
	 *
	 * TODO: hm
	 */
	public function setup($options)
	{
		foreach($options as $k => $value){
			// Check whether it is known option
			// Other way it is part of params
			if(property_exists($this, $k))
			{
				$this->$k = $value;
			}
			else
			{
				$this->params->set($k, $value);
			}
		}

		return $this;
	}

	/**
	 * Get all properties
	 */
	public function getProperties()
	{
		// Get all properties
		$properties = get_object_vars($this);
		unset($properties['element']);
		return $properties;
	}

	/**
	 * Return form for a field main configuration
	 */
	public function getFormConfiguration ()
	{
		// TODO: return form for defaul params
		// 		eg. id, type, label, access, validation, filter
		if(!$this->form_configuration)
		{
			JForm::addFormPath(JPATH_LIBRARIES . '/cms/form/form');

			try
			{
				$form = JForm::getInstance('main.' . $this->type . '.' . $this->name, 'field', array(), true, '//fieldset[@name="' . $this->view_type . '"]');
				$form->bind($this->getProperties());

				$this->form_configuration = $form;
			}
			catch (Exception $e)
			{
				// TODO: What to do here ???
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}
		return $this->form_configuration;
	}

	/**
	 * Return form for a field addittional configuration
	 */
	public function getFormConfigurationAddittinal ()
	{
		// temporary disable
		return $this->form_coniguration_addittional;

		// Get form object
		if(!$this->form_coniguration_addittional)
		{
			try
			{
				// TODO: need better place for field.xml form !!!
				//JForm::addFormPath(JPATH_LIBRARIES . '/form/fields');

				// TODO: need a two form:
				//	first - main configuration, eg: id, type, label, access, validation, filter
				//	second - comes from {type}.xml addittional configuration, eg: default, class, options and other possible field atributes

				$form = JForm::getInstance('addittional.' . $this->type . '.' . $this->name, $this->type, array(), true, '//fieldset[@name="' . $this->view_type . '"]/field');
				$form->bind($this->params->toArray());

				$this->form_coniguration_addittional = $form;
			}
			catch (Exception $e)
			{
				var_dump(JFormHelper::addFormPath());
				// TODO: What to do here ???
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

		}

		return $this->form_coniguration_addittional;
	}


}
