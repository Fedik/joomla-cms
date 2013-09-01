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
	 * Table aliases
	 * @var array
	 */
	protected $table_alias = array(
		'common' => 'c',
		'special' => 's',
	);

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @throws  Exception
	 */
	public function getTable($name = 'Corecontent', $prefix = 'JTable', $options = array())
	{
		$table = JTable::getInstance($name, $prefix, $options);

		if($table)
		{
			return $table;
		}

		throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 */
	public function getItems()
	{
		$items = parent::getItems();
		if(!$items)
		{
			return false;
		}
		// Content type info
		// TODO: cached getInstanse() would be better !!!
		$ucmContent = new JUcmContent(null, $this->getState('content.type_alias'));

		// Get Enabled fields
		$fields_info = UcmTypeHelper::getFields($this->getState('content.type_alias'), $this->getState('content.layout_name'));

		// Do unified
		$ucmItems = array();
		foreach ($items as $item) {
			$data = (array) $item; // ho ho ho ???
			//prepare fields and check whether there any related
			$fields = array();
			foreach($fields_info as $field_info) {
				$field = new UcmField($field_info);
				$related = $field->params->get('related');

				//TODO: make it works and where better to do it, here or in getListQuery ???
				if(!empty($data[$field->field_name]) && !empty($related))
				{
					$data[$field->field_name] = $this->getRelated($data[$field->field_name], $related);
				}

				$fields[$field_info->field_name] = $field;
			}

			$ucmItems[] = new UcmItem($data, $fields, $this->getState('content.layout_name'), $ucmContent->type);
		}

		return $ucmItems;
	}

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
		$id .= ':' . serialize($this->getState('list.filter'));
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

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 */
	protected function getListQuery()
	{
		// Init variables
		$app = JFactory::getApplication();
		$user	= JFactory::getUser();
		$db = $this->getDbo();
		$filter = $this->getState('list.filter', array());
		$ordering = $this->getState('list.ordering', array());

		// Get Enabled fields
		//$fields_info = UcmTypeHelper::getFields($this->getState('content.type_alias'), $this->getState('content.layout_name'));

		// Content type info
		// TODO: cached getInstanse() would be better !!!
		$ucmContent = new JUcmContent(null, $this->getState('content.type_alias'));
		//table info
		$tableInfo = json_decode($ucmContent->type->type->table);

		//Content tables
		$common_alias = $this->table_alias['common'];
		$tableCommon = $this->getTable($tableInfo->common->type, $tableInfo->common->prefix, $tableInfo->common->config);
		$fields_common = $tableCommon->getFields();

		$tableSpecial = null;
		$fields_special = array();
		if(!empty($tableInfo->special->type))
		{
			$special_alias = $this->table_alias['special'];;
			$tableSpecial = $this->getTable($tableInfo->special->type, $tableInfo->special->prefix, $tableInfo->special->config);
			$fields_special = $tableSpecial->getFields();
		}

		// Build query object.
		$query = $db->getQuery(true);

		// Common table
		$query->from($db->qn($tableCommon->getTableName(), $common_alias));
		$query->select($common_alias . '.*');

		// Join special table if exist
		if($tableSpecial)
		{
			$query->join('LEFT',
					$db->qn($tableSpecial->getTableName(), $special_alias)
					. ' ON ' . $special_alias . '.' . $tableSpecial->getKeyName()
					. ' = ' . $common_alias . '.core_content_item_id' // TODO: not very flexible !!!
			);
			$query->select($special_alias . '.*');
		}

		//TODO: in theory we also can check here wether exist any "relation field" and JOIN it here ???

		// Build filter
		// language
		if ($this->getState('filter.language'))
		{
			$filter['core_language'] = array(JFactory::getLanguage()->getTag(), '*');
		}
		// Access
		$filter['core_access'] = array_unique($user->getAuthorisedViewLevels());

		// Filter data
		$this->buildQueryFilter($query, $filter, array_keys($fields_common), array_keys($fields_special));

		// Data ordering
		if(!empty($ordering))
		{
			$this->buildQueryOrdering($query, $ordering, array_keys($fields_common), array_keys($fields_special));
		}

		//echo $query->dump();

		return $query;
	}

	/**
	 * Build query filter
	 *
	 * @param JDatabaseQuery $query Query for apend filter
	 * @param array $filters filter data, fild_name=>filed_value[values_array]
	 * @param array $fields_common Fields that exist in the Common table
	 * @param array $fields_special Fields that exist in the Special table
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object
	 *
	 */
	protected function buildQueryFilter($query, $filters, $fields_common, $fields_special = array())
	{
		$db = $this->getDbo();

		foreach ($filters as $k => $v)
		{
			// Check what the table related, and take alias
			if(in_array($k, $fields_common))
			{
				$alias = $this->table_alias['common'];
			}
			elseif(in_array($k, $fields_special))
			{
				$alias = $this->table_alias['special'];
			}
			else
			{
				// Nothing to do with it
				continue;
			}

			// if we have array
			if(is_array($v))
			{
				$query->where($alias . '.' . $k . ' IN (' . implode(',', $db->q($v)) . ')');
			}
			else
			{
				$query->where($alias . '.' . $k . ' = ' . $db->q($v));
			}

			// TODO: how can handle LIKE or REGEXP ???

		}

		return $query;
	}

	/**
	 * Build query ordering
	 *
	 * @param JDatabaseQuery $query Query for apend filter
	 * @param array $ordering Fields for ordering, fild_name=>asc
	 * @param array $fields_common Fields that exist in the Common table
	 * @param array $fields_special Fields that exist in the Special table
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object
	 *
	 */
	protected function buildQueryOrdering($query, $ordering, $fields_common, $fields_special = array())
	{

		foreach ($ordering as $k => $dir)
		{
			// Check what the table related, and take alias
			if(in_array($k, $fields_common))
			{
				$alias = $this->table_alias['common'];
			}
			elseif(in_array($k, $fields_special))
			{
				$alias = $this->table_alias['special'];
			}
			else
			{
				// Nothing to do with it
				continue;
			}

			$dir = strtoupper($dir) == 'DESC' ? 'DESC' : 'ASC';
			$query->order($alias . '.' . $k . ' ' . $dir);

		}

		return $query;
	}
}

