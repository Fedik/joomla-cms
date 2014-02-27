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
	 * Method to get a Cache Key based on the model state.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getCacheKey()
	{
		return md5($this->state->toString('json'));
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
		$user  = JFactory::getUser();


		// TODO: getInstanse() would be better no? (!!!)
		$type = new JUcmType($this->state->get('type_alias'));

		// Tables info
		$tablesInfo = json_decode($type->type->table);
		$tables = array();

		// Base table
		$tables['base'] = JTable::getInstance('Ucm');
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

		echo $query->dump();

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

		var_dump(isset($tables['base']->ucm_id));
// 		foreach ($filters as $k => $v)
// 		{
// 			// Check what the table related, and take alias
// 			if(property_exists($tables['base'], $k))
// 			{
// 				$alias = $this->table_alias['common'];
// 			}
// 			elseif(in_array($k, $fields_special))
// 			{
// 				$alias = $this->table_alias['special'];
// 			}
// 			else
// 			{
// 				// Nothing to do with it
// 				continue;
// 			}

// 			// if we have array
// 			if(is_array($v))
// 			{
// 				$query->where($alias . '.' . $k . ' IN (' . implode(',', $db->q($v)) . ')');
// 			}
// 			else
// 			{
// 				$query->where($alias . '.' . $k . ' = ' . $db->q($v));
// 			}

// 		}

		var_dump($this->state);

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

		return $query;
	}

}

