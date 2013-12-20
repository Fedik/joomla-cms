<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags table
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 */
class JTableLayout extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__ucm_layouts', 'layout_id', $db);
	}

	/**
	 * Check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check()
	 */
	public function check()
	{
		// check whether title still here (:
		if (!trim($this->layout_title))
		{
			$this->setError(JText::_('COM_TYPES_PROVIDE_VALID_TITLE'));
			return false;
		}

		// check the aliase eg layout_name, should be unique for  current content type
		if (!trim($this->layout_name))
		{
			$this->layout_name = $this->layout_title;
		}

		$this->layout_name = JFilterOutput::stringURLSafe($this->layout_name);
		// tricky (:
		if (!trim($this->layout_name))
		{
			$this->setError(JText::_('COM_TYPES_PROVIDE_VALID_TITLE'));
			return false;
		}

		// find a duplication
		$table = self::getInstance('Layout', 'JTable');
		$test  = $table->load(array(
			'layout_name' => $this->layout_name,
			'type_id' => $this->type_id,
		));
		if ($test && $table->layout_id != $this->layout_id)
		{
			$this->setError(JText::_('COM_TYPES_LAYOUT_ERROR_UNIQUE_ALIAS'));
			return false;
		}

		return true;
	}
}
