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
class PageModelItem extends JModelItem
{
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
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('content.id', $pk);

		$type_alias = $app->input->get('type');
		$this->setState('content.type_alias', $type_alias);

		$layout_name = $app->input->get('layout_name');
		$this->setState('content.layout_name', $layout_name);

		$offset = $app->input->getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);


		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	/**
	 * Method to get item data.
	 *
	 * @param   integer  An optional ID
	 *
	 * @return  object
	 *
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('content.id');

		// TODO: cached getInstanse() would be better
		$ucmContent = new JUcmContent(null, $this->getState('content.type_alias'));
		//table info
		$tableInfo = json_decode($ucmContent->type->type->table);

		//Content tables
		$tableCommon = $this->getTable($tableInfo->common->type, $tableInfo->common->prefix, $tableInfo->common->config);
		$tableSpecial = $this->getTable($tableInfo->special->type, $tableInfo->special->prefix, $tableInfo->special->config);

		//load data
		// TODO: why JUcmContent have no load() eg: JUcmContent->load() ???
		$tableCommon->load($pk);
		$tableSpecial->load($pk);

		$data = array_merge($tableCommon->getProperties(), $tableSpecial->getProperties());

		//fields
		$fields_info = UcmTypeHelper::getFields($this->getState('content.type_alias'), $this->getState('content.layout_name'));

		//prepare fields and check whether there any related
		$fields = array();
		foreach($fields_info as $field_info) {
			// TODO: realy should be here, or better move to UcmTypeHelper::getFields ???
			$field = new UcmField($field_info);
			$related = $field->params->get('related');

			//TODO: make it works
			if(!empty($data[$field->field_name]) && !empty($related))
			{
				$data[$field->field_name] = $this->getRelated($data[$field->field_name], $related);
			}
			//TODO: prepare fields instances
			//$this->prepareFields($fields);

			$fields[$field_info->field_name] = $field;
		}

		$item = new UcmItem($data, $fields, $this->getState('content.layout_name'), $ucmContent->type);

		return $item;
	}

	/**
	 * Load related data, eg. articles for category or for tag ... category or tag item info
	 *
	 * @param int $pk - id of the parent item
	 * @param JRegistry $params - info about realted content
	 * @return multitype:
	 */
	public function getRelated($pk, $params)
	{
		return array();
	}
}
