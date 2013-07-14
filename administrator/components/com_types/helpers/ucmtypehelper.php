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
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_types/tables');
		include_once __DIR__ . '/typesimport.php';


		try{
			$typesImport = new JUcmTypesImport($component);
			$typesImport->import();
		}
		catch (Exception $e){
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
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

		// use defaults for test
		$fieldsDef = self::getFieldsDefault($type_alias);
		$fields = array();
		foreach($fieldsDef as $def){
			$fields[$def->name] = (object) $def->getProperties();
			$fields[$def->name]->params = $def->params->toArray();
		}

		return $fields;
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
//				'element' => $JFormField,
			));

			$fields_cache[$type_alias][$field_name] = $field;
		}

		return $fields_cache[$type_alias];

	}

}