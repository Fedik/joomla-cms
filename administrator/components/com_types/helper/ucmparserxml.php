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
 * Content Types Parser from ucm.xml
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 */
class JUcmParserXml
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
	protected $resource;

	/**
	 * Associative array contain the types
	 *
	 * @var array
	 *
	 */
	protected $types = array();

	/**
	 * Associative array contain the layouts by type,
	 * and related fields
	 *
	 * @var array
	 *
	 */
	protected $layouts = array();

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
			throw new RuntimeException('File ucm.xml not found in component folder. For component: ' . $component, 404);
		}

		// It is right component?
		if(!$ucmXML->xpath('/ucm[@component="' . $component . '"]'))
		{
			// No Componet found
			throw new RuntimeException('File ucm.xml did not contain info about a given component.');
		}

		$this->component = $component;
		$this->resource = $ucmXML;

	}

	/**
	 * Method to get certain data.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 */
	public function __get($name)
	{
		if(property_exists($this, $name))
		{
			return $this->$name;
		}
		return null;
	}

	/**
	 * Parse the types, layouts and related fields from the xml data
	 *
	 * @return self
	 * @throws RuntimeException
	 */
	public function parse()
	{
		// Parse types
		$this->parseTypes();
		// Parse the layouts for each type
		foreach(array_keys($this->types) as $type_name){
			$this->parseLayouts($type_name);
		}

		// TODO: Parse the admin side layouts

		return $this;
	}

	/**
	 * Parse the database tables from the xml data
	 *
	 * @return self
	 * @throws RuntimeException
	 */
	public function parseTables()
	{
		return $this;
	}

	/**
	 * Parse the types from the xml data
	 *
	 * @return self
	 * @throws RuntimeException
	 */
	protected function parseTypes()
	{
		// Get available types
		$typesXML = $this->resource->xpath('/ucm[@component="' . $this->component . '"]/types/type');
		if(empty($typesXML))
		{
			throw new RuntimeException('File ucm.xml did not contain info about any Content Type.');
		}

		// Parse
		$type_template = $this->getTableStructure('Contenttype');// TODO: is realy useful (???)
		$params_exclude = array_keys($type_template);
		$params_exclude[] = 'name';
		foreach($typesXML as $typeXML) {
			$type = array();

			// Get info
			$info = $this->getAttributes($typeXML);
			$type_name = strtolower(trim($info->get('name')));

			// Check whether type name exist
			if(!$type_name) continue;

			foreach($type_template as $n => $v) {
				$key = str_replace('type_', '', $n);
				$params_exclude[] = $key;
				$type[$n] = $info->get($key);
			}

			// Build aliase
			$type['type_alias'] = $this->component . '.' . $type_name;
			// Make sure that title exist
			if(empty($type['type_title']))
			{
				$type['type_title'] = $type_name;
			}
			$type['type_id'] = null; // if anyone will try to set this

			// All data that out of the template goes to params (???)
			$type['params'] = $this->prepareParams($info, $params_exclude);


			// Store for future steps
			$this->types[$type_name] = $type;
		}

		return $this;
	}

	/**
	 * Parse the layouts for giwen type from the xml data
	 *
	 * @param string the type name
	 *
	 * @return self
	 */
	protected function parseLayouts($type_name)
	{
		// Get layouts for a Conetnt Type
		$layouts = $this->resource->xpath('/ucm[@component="' . $this->component . '"]/types/type[@name="' . $type_name . '"]/layouts/layout');
		foreach ($layouts as $layoutXML) {
			$layoutInfo = $this->getAttributes($layoutXML);
			$name = strtolower(trim($layoutInfo->get('name')));
			// Check whether name exist
			if(!$name) continue;
			$layout = array(
				'layout_name' => $name,
				'layout_title'=> $layoutInfo->get('title', $name),
				'fields' => array(),
			);

			$this->layouts[$type_name][$name] = $layout;

			// Check the fields
			if($layoutXML->xpath('field'))
			{
				$this->parseFields($type_name, $name);
			}

		}

		return $this;
	}

	/**
	 * Parse the fields for given layout from the xml data
	 *
	 * @return self
	 */
	protected function parseFields($type_name, $layout_name)
	{
		$fieldsXML = $this->resource->xpath('/ucm[@component="' . $this->component . '"]/types/type[@name="' . $type_name . '"]/layouts/layout[@name="' . $layout_name . '"]/field');
		$fields = array();
		foreach($fieldsXML as $i => $fieldXML){
			$fieldInfo = $this->getAttributes($fieldXML);
			$field_name = $fieldInfo->get('name');
			if(!$field_name) continue;

			$default_access = $layout_name == 'form' ? 3 : 1;

			$field = array(
				'field_name' => $field_name,
				'field_type' => $fieldInfo->get('type', 'text'),
				'locked' 	 => $fieldInfo->get('locked', 1),
				'ordering' 	 => $fieldInfo->get('ordering', $i),
				'layout_name'=> $layout_name,
				'access' 	 => $fieldInfo->get('access', $default_access),
				'state' 	 => $fieldInfo->get('state', 1),
				'stage' 	 => $fieldInfo->get('stage', 0),
			);

			// Prepare params
			// TODO: what about <option> for a some field types ???
			$field['params'] = $this->prepareParams($fieldInfo,
					array('name', 'type', 'ordering', 'access', 'locked', 'state', 'stage'));

			$fields[$field_name] = $field;
		}

		$this->layouts[$type_name][$layout_name]['fields'] = $fields;

		return $this;
	}

	/**
	 * Return the Type structure based on the type Table properties
	 *
	 * @param   string  $type    The table name.
	 * @param   string  $prefix  The class prefix. Optional.
	 *
	 * @return  array
	 */
	protected function getTableStructure($type, $prefix = 'JTable')
	{
		$table = JTable::getInstance($type, $prefix);
		return $table->getProperties();
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
	 * Prepare Params,
	 * All data that out of the template goes to params
	 *
	 * @param mixed $data JRegistry or array with data for conver to params string
	 * @param array $exclude contain keys for clean up from $info
	 *
	 * @return string JSON string or empty string
	 */
	protected function prepareParams($data, $exclude = array())
	{
		$params = ($data instanceof JRegistry) ? $data->toArray() : (array) $data;
		foreach($exclude as $k) {
			if(isset($params[$k]))
			{
				unset($params[$k]);
			}
		}

		return empty($params) ? '' : json_encode($params);
	}
}
