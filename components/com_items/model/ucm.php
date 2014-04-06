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
	 * Method to get the Item wrapped by UCM item.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getItemUcm()
	{
		$items = $this->getItemsUcm();
		if (!empty($items[0]))
		{
			return $items[0];
		}
		return false;
	}

	/**
	 * Method to get the Item list, where each item wrapped by UCM item.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getItemsUcm()
	{
		$items = $this->getItems();
		if (!$items)
		{
			return false;
		}

		$layout_name = $this->state->get('layout_name');
		$type_alias  = $this->state->get('type_alias');

		// Get Enabled fields
		$fields_info = $this->getFields($layout_name);

		$ucmItems = array();
		foreach ($items as $item) {
			$data = (array) $item; // ho ho ho ???
			//prepare fields and check whether there any related
			$fields = array();
			foreach($fields_info as $field_info) {
				$field = new JUcmField($field_info);

				//TODO: make it works and where better to do it, here or in getListQuery ???
// 				$related = $field->params->get('related');
// 				if(!empty($data[$field->field_name]) && !empty($related))
// 				{
// 					$data[$field->field_name] = $this->getRelated($data[$field->field_name], $related);
// 				}

				$fields[$field_info->field_name] = $field;
			}

			$ucmItems[] = new JUcmItem($data, $fields, $type_alias, $layout_name);
		}

		return $ucmItems;
	}

	/**
	 * Method to get a item.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getItem()
	{
		$items = $this->getItems();
		if (!empty($items[0]))
		{
			return $items[0];
		}
		return false;
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 */
	public function getItems()
	{
		$cache_key = $this->getCacheKey();
		if (!empty($this->cache[$cache_key]))
		{
			return $this->cache[$cache_key];
		}

		$query = $this->db->getQuery(true);
		$this->buildQuery($query);
		$this->db->setQuery($query, (int) $this->state->get('offset', 0), (int) $this->state->get('limit', 0));
		$this->cache[$cache_key] = $this->db->loadObjectList();

		return $this->cache[$cache_key];
	}

	/**
	 * Get fields related to current type and give layout
	 *
	 * @param string $layout_name Layout name
	 *
	 * @return array of enabled fields, and their properties
	 */
	public function getFields($layout_name = '')
	{
		$layout_name = $layout_name ? $layout_name : $this->state->get('layout_name');
		$type_alias  = $this->state->get('type_alias');

		if(!$layout_name)
		{
			throw new LogicException('Layout name is required.', 503);
		}

		return JUcmTypeHelper::getFields($type_alias, $layout_name);

	}


	/**
	 * Method to get a Cache Key based on the model state.
	 *
	 * @param   string $context
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getCacheKey($context = '')
	{
		return md5($context . $this->state->toString('json'));
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @param   JDatabaseQuery   A JDatabaseQuery object.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 */
	protected function buildQuery($query)
	{
		// @TODO: access
		//$user  = JFactory::getUser();


		// TODO: cached getInstanse() would be better no? (!!!)
		$type = new JUcmType($this->state->get('type_alias'));

		if(!$type->type)
		{
			throw new LogicException('Given Content type not exists.', 503);
		}

		// Tables info
		$tablesInfo = json_decode($type->type->table);
		$tables = array();

		// Base table
		$tables['base'] = JTable::getInstance('Ucm');
		// @TODO: allow define the table alias in more smart way
		$tables['base']->_alias = 'base';
		$query->from($tables['base']->getTableName() . ' AS base');

		// Table Common
		$tables['common'] = JTable::getInstance($tablesInfo->common->type, $tablesInfo->common->prefix, $tablesInfo->common->config);
		$tables['common']->_alias = 'common';
		$query->join(
				'LEFT',
				$tables['common']->getTableName() .' AS common'
				. ' ON common.' . $tables['common']->getKeyName()
				. ' = base.' . $tables['base']->getKeyName()
		);

		// Table Special
		if(!empty($tablesInfo->special))
		{
			$tables['special'] = JTable::getInstance($tablesInfo->special->type, $tablesInfo->special->prefix, $tablesInfo->special->config);
			$tables['special']->_alias = 'special';
			$query->join(
					'LEFT',
					$tables['special']->getTableName() .' AS special'
					. ' ON special.' . $tables['special']->getKeyName()
					. ' = base.ucm_item_id'
			);
		}

		$this->buildQuerySelect($query, $tables);
		$this->buildQueryWhere($query, $tables);
		$this->buildQueryOrdering($query, $tables);

		// @TODO: call field plugins for build query for load related data,
		//        like category name, author name and so on

		//echo $query->dump();

		return $query;
	}

	/**
	 * Method for build Select for data that need to select.
	 *
	 * @param   JDatabaseQuery   A JDatabaseQuery object.
	 * @param	array			 all related table
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object.
	 *
	 */
	protected function buildQuerySelect($query, $tables)
	{
		$query->select('*');

		return $query;
	}

	/**
	 * Method for build Select filter.
	 *
	 * @param   JDatabaseQuery   A JDatabaseQuery object.
	 * @param	array			 all related table
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object.
	 *
	 */
	protected function buildQueryWhere($query, $tables)
	{
		$filter = $this->state->get('filter');
		if(!$filter)
		{
			return $query;
		}

 		foreach ($filter as $k => $v)
 		{
 			// Check what the table related, and take it
 			$table = $this->findTableByField($k, $tables);
 			if(!$table)
 			{
 				continue;
 			}
			// whether value just a string or number
 			if(is_scalar($v))
			{
				$v = (object) array('value' => $v);
			}

 			$where  = $table->_alias . '.' .$k;
 			$clause = empty($v->clause) ? '' : $v->clause;
 			$glue   = empty($v->glue) ? 'AND' : strtoupper($v->glue) == 'AND' ? 'AND' : 'OR';

 			switch (strtoupper($clause)) {
 				case 'REGEXP':
 					// @TODO: make me work
 					break;

 				case 'LIKE':
 					$where .= ' LIKE ' . $this->db->q('%' . $v->value . '%');
 					break;

 				case 'LIKE_LEFT':
 					$where .= ' LIKE ' . $this->db->q('%' . $v->value);
 					break;

 				case 'LIKE_RIGHT':
 					$where .= ' LIKE ' . $this->db->q($v->value . '%');
 					break;

 				case 'IN':
 					// @TODO: make me work
 					break;

 				default:
 					$where .= ' = ' . $this->db->q($v->value);
 					break;
 			}

 			$query->where($where, $glue);
 		}

		return $query;
	}

	/**
	 * Method for build data ordering.
	 *
	 * @param   JDatabaseQuery   A JDatabaseQuery object.
	 * @param	array			 all related table
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object.
	 *
	 */
	protected function buildQueryOrdering($query, $tables)
	{
		$ordering = $this->state->get('ordering');
		if(!$ordering)
		{
			return $query;
		}

		// @TODO: make me work

		return $query;
	}

	/**
	 * Find table that has a field_name
	 *
	 * @param string $field_name - field name
	 * @param array $tables      - array of JTable`s
	 *
	 * @return mixed table that has given field or false
	 */
	protected function findTableByField($field_name, $tables)
	{
		foreach($tables as $table){
			$table_fields = $table->getFields(); //it is cached, so do not worry ;)

			if(!empty($table_fields[$field_name]))
			{
				// Match found
				return $table;
			}
		}

		// No matches found
		return false;
	}

}

