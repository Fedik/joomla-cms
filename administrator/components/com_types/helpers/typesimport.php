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
class JUcmTypesImport implements JUcm
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
		$typesXML = $ucmXML->xpath('/ucm[@component="' . $component . '"]/types/type');
		if(empty($typesXML))
		{
			throw new RuntimeException('File ucm.xml did not contain info about any Content Type.');
		}

		// If there need any table, create it first
		$tablesXML = $this->ucmXML->xpath('/ucm[@component="' . $component . '"]/tables/table');
		if(!empty($tablesXML))
		{
			$this->doTables($tablesXML);
		}
		// Import Types
		$this->doTypes($typesXML);
	}

	/**
	 * Create/Upgrade tables from by xml data
	 *
	 * @param SimpleXMLElement $tablesXML tables description
	 *
	 */
	public function doTables($tablesXML)
	{
		return true;
	}

	/**
	 * Create/Upgrade a Content Types
	 *
	 * @param  SimpleXMLElement $typesXML types description
	 *
	 */
	public function doTypes($typesXML)
	{

	}




}
