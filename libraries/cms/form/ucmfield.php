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
	 * Field JFormField Element
	 */
	protected $element;

	/**
	 * Field Configuration form
	 */
	protected $coniguration_form;

	/**
	 * Construct.
	 *
	 * @param   JFormField  $field  The field object.
	 */
	public function __construct($field = null)
	{
		//parent::__construct($form);
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
				if(isset($this->$name))
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
	 * TODO: need something for field configuration !!!
	 */
	public function setup($options)
	{
		foreach($options as $k => $value){
			$this->{$k} = $value;
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
	 * Return form for a field configuration
	 */
	public function getFormConfiguration () {
		// Get form object
		if(!$this->coniguration_form)
		{
			try
			{
				// TODO: need better place for field.xml form !!!
				//JForm::addFormPath(JPATH_LIBRARIES . '/form/fields');

				// TODO: need a two form:
				//	first - main configuration, eg: id, type, label, access, validation, filter
				//	second - comes from {type}.xml addittional configuration, eg: default, class, options and other possible field atributes

				$form = JForm::getInstance($this->type . '.' . $this->name, $this->type, array(), true, '//fieldset[@name="' . $this->view_type . '"]');
				$form->bind($this->getProperties());

				$this->coniguration_form['addittional'] = $form;
			}
			catch (Exception $e)
			{
				var_dump(JFormHelper::addFormPath());
				// TODO: What to do here ???
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}

		}

		return $this->coniguration_form;
	}


}
