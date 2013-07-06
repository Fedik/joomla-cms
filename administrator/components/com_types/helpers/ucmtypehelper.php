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
 * UCM Type helper.
 *
 * @package     Joomla.Administrator
 */
class UCMTypeHelper
{
	/**
	 * Import New Content type from content ucm.xml
	 *
	 * @param string $component Component name, eg com_content
	 *
	 * @return bool true on success
	 */
	public static function importContentType ($component)
	{
		// Find ucm.xml in component folder
		$ucmFile = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/ucm.xml');
		if (!file_exists($ucmFile) || !$ucmXML = simplexml_load_file($ucmFile))
		{
			// Something went wrong
			return false;
		}

		// It is right component?
		if(!$ucmXML->xpath('/ucm[@component="' . $component . '"]'))
		{
			// No Componet found
			return false;
		}
		// ok move on
		// init main variables
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$typeTable = JTable::getInstance('Contenttype', 'JTable');

		// TODO: Import/modify types and their views


		// TODO: Import/modify admin views

		// TODO: Create or modify tables

		return true;
		// not works!
		$tablesXML = $ucmXML->xpath('/ucm[@component="' . $component . '"]/tables/table');
		foreach($tablesXML as $tableXML) {
			$tableAttributes = $tableXML->attributes();
			$name = (string) $tableAttributes->name;
			$tableFieldsXML = $tableXML->xpath('field[@name]');

			// Check if exist
			$table = null;
			try{
				$table = $db->getTableCreate($name);
			}
			catch (Exception $e){}

			if (!$table)
			{
				// TODO: Create new table, need a better way !!!
				$field_query = array();
				$query = 'CREATE TABLE ' . $db->quoteName($name) . ' (' . "\n";

				foreach($tableFieldsXML as $k => $tableField) {
					$fieldAttributes = $tableField->attributes();
					$field_name = (string) $fieldAttributes->name;
					$field_type = JString::strtoupper((string) $fieldAttributes->type);
					if(!$field_type) {
						$field_type = 'VARCHAR';
					}
					$field_lenght = (int) $fieldAttributes->lenght;
					if(!$field_lenght && $field_type == 'VARCHAR') {
						$field_lenght = 255;
					}
					$field_extra = (string) $fieldAttributes->extra;
					$field_default = (string) $fieldAttributes->default;
					$field_comment = (string) $fieldAttributes->comment;

					$field_query[$k] = $db->quoteName($field_name);
					$field_query[$k] .= ' ' . $field_type;
					$field_query[$k] .= $field_lenght ? '(' . $field_lenght . ')' : '';
					$field_query[$k] .= ' ' . $field_extra;
					if($field_default)
					{
						$field_query[$k] .= ' DEFAULT ' . $db->quote($field_default);
					}
					if($field_comment)
					{
						$field_query[$k] .= ' COMMENT ' . $db->quote($field_comment);
					}

				}
				$query .= implode(",\n", $field_query);
				// Finish query
				$table_extra = (string) $tableAttributes->extra;
				if($table_extra) {
					// TODO: noooo! need know what is extra exactly eg ENGINE/CHARSET ...
					$query .= ') ' . $table_extra . ';';
				}
				else {
					$query .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8;';
				}

				$db->setQuery($query);

				try{
					$db->execute();
				}
				catch (Exception $e){
					$app->enqueueMessage($e->getMessage(), 'error');
					return false;
				}



			}
			else
			{
				// TODO: Compare the differences and modify table
			}

		}

		return true;
	}

	/**
	 * Return Content type Fields for given View.
	 *
	 * @param   string  $type_alias  Type alias.
	 * @param   string  $view  View name.
	 * @param   bool  $published_only  Return only active or not.
	 *
	 * @return  array Array with fields
	 */
	public static function getFields($type_alias, $view = 'form', $published_only = true)
	{
		// TODO: load fields from database;
		//		main fields stored in table like #__ucm_fields;
		//		fields relation to View stored in separated table like #__ucm_layouts;
		//		or something
		return array();
	}

	/**
	 * Return Default Content type Fields.
	 * Take fields from ucm.xml of Content Type defination.
	 *
	 * @param   string  $type_alias  Type alias.
	 *
	 * @return  array Array with fields
	 */
	public static function getFieldsDefault($type_alias)
	{
		// Cache!
		static $fields_cache;

		if (isset($fields_cache[$type_alias])) {
			return $fields_cache[$type_alias];
		}
		$fields_cache[$type_alias] = array();

		JLoader::import('cms.form.ucmfield');

		$app = JFactory::getApplication();

		// Find file name
		$alias_parts = explode('.', $type_alias);
		if(count($alias_parts) != 2)
		{
			// TODO: wrong alias. Need a message or throw
		}
		$component = $alias_parts[0];
		$type = $alias_parts[1];

		// Path to ucm.xml file
		$ucmFile = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/ucm.xml');

		if (!file_exists($ucmFile) || !$ucmXML = simplexml_load_file($ucmFile))
		{
			//TODO: need throw here ???
			return $fields_cache[$type_alias];
		}

		// Ok file exist move on
		// Get default fields
		$elements = $ucmXML->xpath('/ucm/types/type[@name="' . $type . '"]/fields/field');

		$i = 0;
		foreach ($elements as $element){
			// Prepare
			$attributes = $element->attributes();
			$field_name = (string) $attributes->name;
			$field = new UCMFormField();
			$JFormField = null;

			// TODO: realy need it ???
			if($JFormField = JFormHelper::loadFieldType((string) $attributes->type))
			{
				$JFormField->setup($element, '');
			}

			// Setup,
			// TODO: looks ugly no? (:
			$field->setup(array(
				'field_id' => null,
				'type' => (string) $attributes->type,
				'name' => $field_name,
				'label' => (string) $attributes->label,
				'default' => (string) $attributes->default,
				'ordering' => $i,
				'state' => 1,
				'view' => 'form',
				'view_type' => 'input',
				'value' => (string) $attributes->value,
				'element' => $JFormField,
			));

			$fields_cache[$type_alias][$field_name] = $field;
		}

		return $fields_cache[$type_alias];

	}

}