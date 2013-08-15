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

		var_dump($tableCommon);
	}
}
