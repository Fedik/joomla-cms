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
	 * @param string $type_only The type name that need to import
	 *
	 * @return bool true on success
	 */
	public static function importContentType ($component, $type_only = null)
	{
		$app = JFactory::getApplication();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_types/table');
		//include_once __DIR__ . '/typesimport.php';
		include_once __DIR__ . '/ucmparserxml.php';


		try{
			//$typesImport = new JUcmTypesImport($component, $type);
			//$typesImport->import();
			$parser = new JUcmParserXml($component);
			$parser->parse();

			// Save the Type data
			$typeModel = new TypesModelType;
			foreach($parser->types as $name => $typeData){
				// check whether we need to import only specific type
				if($type_only && $type_only != $name)
				{
					continue;
				}

				//FIXME: temporary unset table, because no clear api
				unset($typeData['table']);

				// Try load old if any
				$type 		 = $typeModel->getItem(array('type_alias' => $typeData['type_alias']));
				$typeDataOld = $type->getProperties();
				$data 		 = array_merge($typeDataOld, $typeData);

				// Related layouts
				$layoutsData = empty($parser->layouts[$name]) ? array() : $parser->layouts[$name];
				// The Form layout
				if(!empty($layoutsData['form']))
				{
					$data['layout'] = array_merge((array) $data['layout'], $layoutsData['form']);
				}
				if(!empty($data['layout']['fields']))
				{
					foreach($data['layout']['fields'] as $field_name => $field){
						if(isset($data['fields'][$field_name]))
						{
							$data['fields'][$field_name] = array_merge((array) $data['fields'][$field_name], $field);
						}
						else
						{
							$data['fields'][$field_name] = $field;
						}
					}
					// Cleat fields from layout
					unset($data['layout']['fields']);
				}

				// save the type data
				$typeSaved = $typeModel->save($data);

				// save the Layouts and related fields
				foreach ($layoutsData as $layout_name => $layoutData){
					$data = array('type_id' => $typeSaved['type_id']);
					// The form layout alredy should be saved
					if($layout_name == 'form') continue;

					$layoutOld = empty($typeDataOld['layouts'][$layout_name]) ? array() : (array) $typeDataOld['layouts'][$layout_name];
					$fieldsOld = UcmTypeHelper::getFields($typeSaved['type_alias'], $layout_name, null, true);

					$data['layout'] = array_merge($layoutOld, $layoutData);
					//  prepare fields
					if(!empty($data['layout']['fields']))
					{
						foreach($data['layout']['fields'] as $field_name => $field){
							// get base field id
							$field_id = empty($typeSaved['fields'][$field_name]) ? null : (int) $typeSaved['fields'][$field_name]['field_id'];

							// Merge with old if any
							if(isset($fieldsOld[$field_name]))
							{
								$data['fields'][$field_name] = array_merge((array) $fieldsOld[$field_name], $field);
							}
							elseif($field_id)
							{
								$field['field_id'] = $field_id;
								$data['fields'][$field_name] = $field;
							}
							else
							{
								// Base Field should exist for continue
								// TODO: can be that this field from "Metadata" or "Publication options"
								//       need check this too !!!
								$app->enqueueMessage('Cannot import: ' . $field_name, 'notice');
							}
						}
						// Cleat fields from layout
						unset($data['layout']['fields']);
					}
					// save
					$typeModel->saveFieldsLayout($data);
				}
			}

		}
		catch (Exception $e){
			var_dump($e);
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return true;

	}

	/**
	 * Return Content type Fields for given Layout.
	 *
	 * @param   string  $type_alias  Type alias.
	 * @param   string  $layout 	 Layout name.
	 * @param   bool  	$published 	 Return only active or not.
	 * @param	bool 	$all 		 Load fields that assigned to the current Content Type
	 * 									but do not assigned to the current layout
	 *
	 * @return  array 	Array with fields
	 */
	public static function getFields($type_alias, $layout_name = 'form', $published = true, $all = false)
	{
		static $cache;
		$key = md5(serialize(array($type_alias, $layout_name, $published, $all)));

		if(isset($cache[$key]))
		{
			return $cache[$key];
		}

		// Get available layouts for the given content type
		$layouts = self::getLayouts($type_alias);
		if(empty($layouts[$layout_name]))
		{
			// Layout not exist, so the fields also cannot exist
			return array();
		}
		$layout = $layouts[$layout_name];
		$user 	= JFactory::getUser();
		$db   	= JFactory::getDbo();

		// Make ure that we use the alias for the parent type
		// because childrens cannot have a own fields
		$parts = explode('.', $type_alias);
		$alias = $parts[0] . '.' . $parts[1];

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
		$query->where('c.type_alias = ' . $db->q($alias));
		$query->where('l.layout_id = '  . (int) $layout->layout_id);
//		$query->where('l.layout_name = '. $db->q($layout_name));

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
					$field->layout_id = $layout->layout_id;
					$field->layout_name = $layout_name;
					$fields[$field_name] = $field;
				}
			}
		}

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
