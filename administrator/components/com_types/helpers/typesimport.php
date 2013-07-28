<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Import Content Types from ucm.xml
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 */
class JUcmTypesImport //extends JObject
{

	/**
	 * The component name for the content types
	 *
	 * @var	  string
	 *
	 */
	protected $component;

	/**
	 * SimpleXML object from ucm.xml
	 *
	 * @var SimpleXMLElement
	 *
	 */
	protected $ucmXML;

	/**
	 * Associative array contain imported types
	 *
	 * @var array
	 *
	 */
	protected $types = array();

	/**
	 * Constructor.
	 *
	 * @param   string  $component  Component name where import to.
	 *
	 * @throws RuntimeException
	 *
	 */
	public function __construct($component)
	{
		// Find ucm.xml in component folder
		$ucmFile = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/ucm.xml');
		if (!file_exists($ucmFile) || !$ucmXML = simplexml_load_file($ucmFile))
		{
			// Something went wrong
			throw new RuntimeException('File ucm.xml not found in component folder.');
		}

		// It is right component?
		if(!$ucmXML->xpath('/ucm[@component="' . $component . '"]'))
		{
			// No Componet found
			throw new RuntimeException('File ucm.xml did not contain info about a given component.');
		}

		$this->component = $component;
		$this->ucmXML = $ucmXML;

	}

	/**
	 * Run import procces
	 *
	 * @return void
	 */
	public function import()
	{
		// Find types
		$typesXML = $this->ucmXML->xpath('/ucm[@component="' . $this->component . '"]/types/type');
		if(empty($typesXML))
		{
			throw new RuntimeException('File ucm.xml did not contain info about any Content Type.');
		}

		// If there need any table, create it first
		$tablesXML = $this->ucmXML->xpath('/ucm[@component="' . $this->component . '"]/tables/table');
		if(!empty($tablesXML))
		{
			$this->doTables($tablesXML);
		}
		// Import Types
		$types = $this->doTypes($typesXML);

		// Continue if any Content type imported
		if(empty($types)) {
			return;
		}

		// Import/modify views and their fields
		$this->doTypeViews($types);

		// TODO: Import/modify admin views
	}

	/**
	 * Create/Upgrade tables from by xml data
	 *
	 * @param array $tablesXML contain SimpleXMLElement $tablesXML tables description
	 *
	 */
	public function doTables($tablesXML)
	{
		// TODO: need one more class for create tables
		return true;
	}

	/**
	 * Import/Upgrade a Content Types
	 *
	 * @param array $typesXML contain SimpleXMLElement $typesXML Content Types description
	 *
	 * @return array $types with imported types
	 */
	public function doTypes($typesXML)
	{
		$types = array();

		foreach($typesXML as $typeXML) {
			$typeTable = JTable::getInstance('Contenttype', 'JTable');
			// Get info
			$info = $this->getAttributes($typeXML);
			$type_name = $info->get('name');

			if(!$type_name) continue;

			// Build aliase
			$type_alias = $this->component . '.' . $type_name;

			$params = $this->prepareParams($info, array('name', 'title', 'table'));

			// Load if already exist
			$typeTable->load(array('type_alias' => $type_alias));

			$typeTable->bind(array(
				'type_alias' => $type_alias,
				'type_title' => $info->get('title', $type_name),
				'params' => $params,
			));

			if(!$typeTable->check() || !$typeTable->store())
			{
				// Something wrong
				// TODO (???)
				continue;
			}

			// Store for future steps
			$types[$type_name] = $typeTable;

		}
		return $types;
	}

	/**
	 * Import/Upgrade a views of the Content Type
	 *
	 * @param array $types Content types which Views need to import
	 *
	 */
	public function doTypeViews($types)
	{
		foreach($types as $type_name => $type){
			// Get Views for a Conetnt Type
			$views = $this->ucmXML->xpath('/ucm[@component="' . $this->component . '"]/types/type[@name="' . $type_name . '"]/views/view');
			foreach ($views as $viewXML) {
				$viewInfo = $this->getAttributes($viewXML);
				if(!$layoutTable = $this->doView($viewInfo, $type->type_id)) continue;

				// Continue with fields
				if($fields = $viewXML->xpath('field'))
				{
					$this->doFields($fields, $layoutTable->getProperties());
				}

			}
		}
	}
	/**
	 * Import/Upgrade a View of the Content Type
	 *
	 * @param JRegistry $viewInfo that contain a view info
	 * @param int $type_id of the View
	 *
	 *
	 */
	public function doView($viewInfo, $type_id = 0)
	{
		$name = $viewInfo->get('name');
		if(!$name) return false;
		$title = $viewInfo->get('title');
		$title = $title ? $title : JString::ucfirst($name);

		$layoutTable = JTable::getInstance('Layout', 'JTable');

		// Load if already exist
		$layoutTable->load(array(
			'layout_name' => $name,
			'type_id' => $type_id,
		));

		$layoutTable->bind(array(
			'layout_name' => $name,
			'layout_title' => $title,
			'type_id' => $type_id,
			'params' => $this->prepareParams($viewInfo, array('name', 'title')),
		));

		if(!$layoutTable->check() || !$layoutTable->store())
		{
			return false;
		}

		return $layoutTable;
	}

	/**
	 * Import/Upgrade a Fields of the Content Type
	 *
	 * @param array $fields that contain a fields
	 * @param array $layout that contain a info about fields layout
	 *
	 *
	 */
	public function doFields($fields, $layout)
	{
		foreach($fields as $i => $fieldXML){
			$fieldInfo = $this->getAttributes($fieldXML);
			if(!$fieldInfo->get('ordering'))
			{
				$fieldInfo->set('ordering', $i);
			}

			if(!$this->doField($fieldInfo, $layout))
			{
				var_dump('Cannot import: ' . $fieldInfo->get('name'));
				continue;
			}
		}
	}

	/**
	 * Import/Upgrade a Field of the Content Type
	 *
	 * @param JRegistry $fieldInfo that contain a field info
	 * @param array $layout that contain a info about fields layout
	 *
	 *
	 */
	public function doField($fieldInfo, $layout)
	{
		if(!$field_name = $fieldInfo->get('name'))
		{
			return false;
		}

		$baseFieldTable = JTable::getInstance('Field', 'JTable');
		$baseFieldTable->load(array(
			'type_id' => $layout['type_id'],
			'field_name' => $field_name,
		));

		if($layout['layout_name'] == 'form')
		{
			// Store the Base Field first
			$baseFieldTable->bind(array(
				'field_name' => $field_name,
				'field_type' => $fieldInfo->get('type', 'text'),
				'type_id' => $layout['type_id'],
				'locked' => $fieldInfo->get('locked', 1),
			));

			if(!$baseFieldTable->check() || !$baseFieldTable->store())
			{
				// Something wrong
				return false;
			}
		}
		elseif(!$baseFieldTable->field_id)
		{
			// Base Field should exist for continue
			// TODO: can be that this field from "Metadata" or "Publication options"
			//       need check this too !!!
			return false;
		}

		// Now store field info in to FieldsLayouts table
		$fieldLayoutTable = JTable::getInstance('FieldsLayouts', 'JTable');
		// Load if already exist
		$fieldLayoutTable->load(array(
			'field_id' => $baseFieldTable->field_id,
			'layout_id' => $layout['layout_id'],
		));

		// Prepare params
		// TODO: what about <option> for a some field types ???
		$params = $this->prepareParams($fieldInfo,
				array('name', 'type', 'ordering', 'access', 'locked', 'state', 'stage'));

		$default_access = $layout['layout_name'] == 'form' ? 3 : 1;

		$fieldLayoutTable->bind(array(
			'field_id' => (int) $baseFieldTable->field_id,
			'layout_id' => (int) $layout['layout_id'],
			'ordering' => (int) $fieldInfo->get('ordering'),
			'access' => (int) $fieldInfo->get('access', $default_access),
			'state' => (int) $fieldInfo->get('state', 1),
			'stage' => (int) $fieldInfo->get('stage', 0),
			'params' => $params,
		));

		return $fieldLayoutTable->check() && $fieldLayoutTable->store();
	}


	/**
	 * Parse XML attributes and return as JRegistry
	 *
	 * @param SimpleXMLElement where need to parse attributes
	 *
	 * @return JRegistry
	 *
	 */
	protected function getAttributes(SimpleXMLElement $element)
	{
		$attributes = (array) $element->attributes();
		$data = array();

		if(!empty($attributes['@attributes']))
		{
			foreach($attributes['@attributes'] as $k => $v){
				// Normalise booleans
				if(strtolower($v) == 'true')
				{
					$v = 1;
				}
				elseif(strtolower($v) == 'false')
				{
					$v = 0;
				}

				$data[$k] = $v;
			}
		}

		return new JRegistry($data);
	}

	/**
	 * Prepare Params
	 *
	 * @param mixed $info JRegistry or array with data for conver to params string
	 * @param array $exclude contain keys for clean up from $info
	 *
	 * @return string JSON string or empty string
	 */
	protected function prepareParams($info, $exclude = array())
	{
		$params = ($info instanceof JRegistry) ? $info->toArray() : (array) $info;
		foreach($exclude as $k) {
			if(isset($params[$k]))
			{
				unset($params[$k]);
			}
		}

		return empty($params) ? '' : json_encode($params);
	}



}



