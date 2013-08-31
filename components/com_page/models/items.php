<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Types Component Type Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_page
 *
 */
class PageModelItems extends JModelList
{
	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.offset');
		$id .= ':' . $this->getState('list.limit');
		// TODO: maybe something smarter ???
		$id .= ':' . serialize($this->getState('list.ordering'));
		$id .= ':' . serialize($this->getState('list.direction'));

		return md5($this->context . ':' . $id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Load state from the input
		$type_alias = $app->input->get('type');
		$this->setState('content.type_alias', $type_alias);

		$layout_name = $app->input->get('layout_name');
		$this->setState('content.layout_name', $layout_name);

		// Filter
		$filter = $app->input->get('filter', array(), 'ARRAY');
		$this->setState('list.filter', $filter);
		// Ordering
		$ordering = $app->input->get('ordering', array(), 'ARRAY');
		$this->setState('list.ordering', $ordering);

		// Limitation
		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
		$this->setState('list.limit', $limit);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);


		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}
}

