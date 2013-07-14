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
	 * Return Content type Fields for given Layout.
	 *
	 * @param   string  $type_alias  Type alias.
	 * @param   string  $layout  Layout name.
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
	 * Return Layouts for a given Content type.
	 *
	 * @param   string  $type_alias  Type alias.
	 *
	 * @return  array Array with layouts
	 */
	public static function getLayouts($type_alias)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('l.*');
		$query->from('#__ucm_layouts as l');
		$query->join('LEFT', '#__content_types as c ON c.type_id=l.type_id');
		$query->where('c.type_alias = '. $db->q($type_alias));

		//$query->order('l.layout_name');
		//echo $query->dump();

		$db->setQuery($query);
		$layouts = $db->loadObjectList('layout_name');

		return $layouts;
	}

}