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
	public function __construct($data, $fields, JUcmType $type)
	{
		// Keep given info
		$this->type = $type;
		$this->data = $data;
		$this->fields_inst = $fields;
		$this->fields = array_keys($fields);

	}


}
