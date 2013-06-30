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
	 * Construct.
	 *
	 * @param   JFormField  $field  The field object.
	 */
	public function __construct($field = null)
	{
		//parent::__construct($form);
	}

	/**
	 * Set Up Field from array info
	 */
	public function setup($options)
	{
		foreach($options as $k => $value){
			$this->{$k} = $value;
		}

		return $this;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		return '';
	}
}