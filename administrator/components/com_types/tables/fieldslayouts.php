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
class JTableFieldsLayouts extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabaseDriver A database connector object
	 */
	public function __construct($db)
	{
		parent::__construct('#__ucm_fields_layouts', 'id', $db);
	}
}
