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
class TypesModelTypes extends JModelBase
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 */
	protected $context = 'com_types.types';

	/**
	 * Instantiate the model.
	 *
	 * @param   JRegistry  $state  The model state.
	 */
	public function __construct(JRegistry $state = null)
	{
		parent::__construct($state);
	}

	/**
	 * Load the model state.
	 *
	 * @return  JRegistry  The state object.
	 */
	protected function loadState()
	{
		$state = parent::loadState();
		$context = $this->context;
		$app = JFactory::getApplication();

		$search = $app->getUserStateFromRequest($context . '.search', 'filter_search');
		$state->set('filter.search', $search);

		// Pre-fill the limits
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
		$state->set('list.limit', $limit);

		$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
		$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
		$state->set('list.start', $limitstart);

		//$published = $this->getUserStateFromRequest($context . '.filter.published', 'filter_published', '');
		//$state->set('filter.published', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_types');
		$state->set('params', $params);

		// Return state information.
		return $state;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Get query for load the list items.
		$query = $this->getListQuery($query);

		$db->setQuery($query, $this->state->get('list.start'), $this->state->get('list.limit'));
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 */
	protected function getListQuery($query)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();

		// Select the required fields from the table.
		$query->select('t.type_id, t.type_title, t.type_alias');
		$query->from('#__content_types AS t');

		// Filter by search in title
		if ($search = $this->state->get('filter.search'))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(t.type_id LIKE ' . $search . ' OR t.type_title LIKE ' . $search . ' OR t.type_alias LIKE ' . $search . ')');
		}

		return $query;
	}

}
