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
	 * The Database object
	 *
	 * @var    JDatabaseDriver
	 *
	 */
	protected $db;

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
	 * Associative array contain imported tables
	 *
	 * @var array
	 *
	 */
	protected $tables = array();

	/**
	 * Associative array contain imported fields
	 *
	 * @var array
	 *
	 */
	protected $fields = array();


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
		$this->doTypes($typesXML);

		// Continue if any Content type imported
		if(empty($this->types)) {
			return;
		}

		// TODO: Import/modify fields and  views
		$this->doFields();
		// TODO: Import/modify admin views
	}

	/**
	 * Create/Upgrade tables from by xml data
	 *
	 * @param array contain SimpleXMLElement $tablesXML tables description
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
	 * @param array contain SimpleXMLElement $typesXML Content Types description
	 *
	 */
	public function doTypes($typesXML)
	{
		foreach($typesXML as $typeXML) {
			$typeTable = JTable::getInstance('Contenttype', 'JTable');
			// Get info
			$info = $this->getAttributes($typeXML);
			$type_name = $info->get('name');

			if(!$type_name) continue;

			// Build aliase
			$type_alias = $this->component . '.' . $type_name;
			$newParams = new JRegistry(array(
				'metadata' => $info->get('metadata') == 'true' || $info->get('metadata') == '1' ? 1 : 0,
				'publish_options' => $info->get('publish_options') == 'true' || $info->get('publish_options') == '1' ? 1 : 0,
				'permissions' => $info->get('permissions') == 'true' || $info->get('permissions') == '1' ? 1 : 0,
			));

			// Load if already exist
			$typeTable->load(array('type_alias' => $type_alias));
			// Check the old params
			$params = new JRegistry($typeTable->params);
			// Merge with new params
			$params->merge($newParams);

			$typeTable->bind(array(
				'type_alias' => $type_alias,
				'type_title' => $info->get('title', $type_name),
				'params' => $params->toString(),
			));

			if(!$typeTable->check() || !$typeTable->store())
			{
				// Something wrong
				// TODO (???)
				continue;
			}

			// Store for future steps
			$this->types[$type_name] = $type_alias; //$typeTable;

		}
		return true;
	}

	/**
	 * Import/Upgrade a main Fields of the Content Type
	 *
	 */
	public function doFields()
	{
		foreach($this->types as $type_name => $type_alias){
			// Get the Fields for current content type
			$xpath = '/ucm[@component="' . $this->component . '"]/types/type[@name="' . $type_name . '"]/fields/field';
			$fields = $this->ucmXML->xpath($xpath);
			var_dump($fields);
		}
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

		return new JRegistry(isset($attributes['@attributes']) ? $attributes['@attributes'] : '');
	}




}



