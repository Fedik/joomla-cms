<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_items
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ItemsModelItem extends JModelDatabase
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 */
	protected $context = 'com_items.item';

	/**
	 * Instantiate the model.
	 *
	 * @param   JRegistry  $state  The model state.
	*/
	public function __construct(JRegistry $state = null, JDatabaseDriver $db = null)
	{
		parent::__construct($state, $db);
	}

}

