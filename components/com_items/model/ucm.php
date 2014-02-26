<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_items
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class JModelUcm extends JModelDatabase
{
	/**
	 * Internal memory based cache array of data.
	 *
	 * @var    array
	 */
	protected $cache = array();

	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 */
	protected $context = 'jmodel.ucm';

	/**
	 * Instantiate the model.
	 *
	 * @param   JRegistry  $state  The model state.
	*/
	public function __construct(JRegistry $state = null, JDatabaseDriver $db = null)
	{
		parent::__construct($state, $db);
	}

	/**
	 * Method to get a item.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getItem()
	{

	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getItems()
	{

	}

	/**
	 * Method to get a Cache Key based on the model state.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getCacheKey()
	{
		return md5($this->state->toString('json'));
	}

}

