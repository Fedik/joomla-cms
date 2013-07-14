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
	 * Field Value
	 */
	protected $value;

	/**
	 * Whether the field is multiple
	 */
	protected $multiple;

	/**
	 * Configuration for a Field
	 */
	public $params;


	/**
	 * Construct.
	 *
	 * @param   JFormField  $field  The field object.
	 */
	public function __construct($field = null)
	{
		$this->params = new JRegistry();
	}



}
