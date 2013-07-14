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
	public static function getFields($type_alias, $layout = 'form', $published_only = true)
	{
		static $cache;
		$key = md5(serialize(array($type_alias, $layout, $published_only)));

		if(isset($cache[$key]))
		{
			return $cache[$key];
		}

		$user = JFactory::getUser();

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('fl.*, f.field_name, f.field_type, l.layout_name');
		$query->from('#__ucm_fields_layouts as fl');
		$query->join('LEFT', '#__ucm_fields as f ON f.field_id=fl.field_id');
		$query->join('LEFT', '#__ucm_layouts as l ON l.layout_id=fl.layout_id');
		$query->join('LEFT', '#__content_types as c ON c.type_id=fl.type_id');
		$query->where('c.type_alias = '. $db->q($type_alias));
		$query->where('l.layout_name = '. $db->q($layout));

		// Check access
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('fl.access IN (' . $groups . ')');

		if($published_only)
		{
			$query->where('fl.state = 1');
		}
		$query->order('fl.ordering');
		//echo $query->dump();

		$db->setQuery($query);
		$fields = $db->loadObjectList('field_name');

		// Prepare params
		// TODO need or???
// 		foreach($fields as $field){
// 			$field->params = new JRegistry($field->params);
// 		}

		// Cache
		$cache[$key] = $fields;

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