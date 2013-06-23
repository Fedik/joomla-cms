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
 * Types Component Types Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class TypesModeltypes extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 * @see        JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'type_id', 't.type_id',
				'type_title', 't.type_title',
				'type_alias', 't.type_alias',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   3.1
	 * /
	public function getTable($type = 'Contenttype', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return    void
	 * @since    3.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$context = $this->context;

		$search = $this->getUserStateFromRequest($context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		//$published = $this->getUserStateFromRequest($context . '.filter.published', 'filter_published', '');
		//$this->setState('filter.published', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_types');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('t.type_title', 'asc');
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.1
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				't.type_id, t.type_title, t.type_alias'
			)
		);
		$query->from('#__content_types AS t');

		// Filter by search in title
		if ($search = $this->getState('filter.search'))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(t.type_id LIKE ' . $search . ' OR t.type_title LIKE ' . $search . ' OR t.type_alias LIKE ' . $search . ')');
		}

		return $query;
	}


}