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
class UcmTypeHelper
{
	/**
	 * Import New Content type from content ucm.xml
	 *
	 * @param string $component Component name, eg com_content
	 *
	 * @return bool true on success
	 */
	public static function importContentType ($component, $type = null)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_types/tables');
		include_once __DIR__ . '/typesimport.php';


		try{
			$typesImport = new JUcmTypesImport($component, $type);
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
	 * @param   bool  $published  Return only active or not.
	 * @param	bool  $all load fields that assigned to the current Content Type
	 * 						but do not exist in current layout
	 *
	 * @return  array Array with fields
	 */
	public static function getFields($type_alias, $layout = 'form', $published = true, $all = false)
	{
		static $cache;
		$key = md5(serialize(array($type_alias, $layout, $published, $all)));

		if(isset($cache[$key]))
		{
			return $cache[$key];
		}

		$user = JFactory::getUser();

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('fl.*, f.field_name, f.field_type, l.layout_name');
// 		$query->from('#__ucm_fields_layouts as fl');
// 		$query->join('LEFT', '#__ucm_fields as f ON f.field_id=fl.field_id');
// 		$query->join('LEFT', '#__ucm_layouts as l ON l.layout_id=fl.layout_id');
// 		$query->join('LEFT', '#__content_types as c ON c.type_id=fl.type_id');
// 		$query->where('c.type_alias = '. $db->q($type_alias));
		$query->from('#__ucm_fields as f');
		$query->join('LEFT', '#__content_types as c ON c.type_id=f.type_id');
		$query->join('LEFT', '#__ucm_fields_layouts as fl ON fl.field_id=f.field_id');
		$query->join('LEFT', '#__ucm_layouts as l ON l.layout_id=fl.layout_id');
		$query->where('c.type_alias = '. $db->q($type_alias));
		$query->where('l.layout_name = '. $db->q($layout));

		// Check access
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$query->where('fl.access IN (' . $groups . ')');

		if($published === true)
		{
			$query->where('fl.state = 1');
		}
		elseif($published === false)
		{
			$query->where('fl.state = 0');
		}
		$query->order('fl.ordering');
		//echo $query->dump();

		$db->setQuery($query);
		$fields = $db->loadObjectList('field_name');

		//TODO: to tricky ???
		if($all)
		{
			// get layout id
			$table = JTable::getInstance('Layout', 'JTable');
			if(!$table->load(array('layout_name' => $layout)))
			{
				return array();
			}
			$layout_id = $table->layout_id;

			$fields_other = self::getFields($type_alias, 'form', null);
			// check if the field from the form layout, so not related to requested layout
			foreach($fields_other as $field_name => $field){
				// the field from form layout
				// set it unpublished, and reset key
				if (empty($fields[$field_name]))
				{
					$field->state = 0;
					$field->id = null;
					$field->params = '';
					$field->layout_id = $layout_id;
					$field->layout_name = $layout;
					$fields[$field_name] = $field;
				}
			}
		}

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
		static $cache;
		$key = $type_alias;

		if(isset($cache[$key]))
		{
			return $cache[$key];
		}

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

		// Cache
		$cache[$key] = $layouts;

		return $layouts;
	}

}
