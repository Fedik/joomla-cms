<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';


// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

//???
$_SERVER['argv'] = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();

/**
 * A command line cron job to move existing conetnt to UCM.
 *
 * @package  Joomla.CLI
 */
class UCMMigrateCli extends JApplicationCli
{
	/**
	 * objects info
	 */
	protected $to_migrate = array();

	/**
	 * database
	 */

	protected $db;

	/**
	 * Class constructor.
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JEventDispatcher $dispatcher = null)
	{
		parent::__construct($input, $config, $dispatcher);

		//db driver
		$this->db = JFactory::getDbo();

		//because empty. tricky? (:
		JFactory::$application = $this;

		//define objects to migrate
		$this->objectsToMigrate($input);
	}
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 */
	public function doExecute()
	{
		//UCM table
		$ucmContentTable = JTable::getInstance('Corecontent');

		//each object
		foreach($this->to_migrate as $key => $info){

			var_dump($info);
		}

		//var_dump($this);
	}

	/**
	 * define objects to migrate
	 */
	protected function objectsToMigrate(JInputCli $input = null){

		//get main types
		$query = $this->db->getQuery(true);
		$query->select(array('type_id', 'type_alias'));
		$query->from('#__content_types');
		//$query->where('type_id IN (1,2,3,4,5,6,7,8,9,10)');
		$query->where('type_id = 1');
		$this->db->setQuery($query);

		$types = $this->db->loadObjectList();

		if(empty($types)){
			echo 'No type found!';
			return;
		}

		foreach($types as $type){
			$alias_arr = explode('.', $type->type_alias);
			$com = $alias_arr[0];

			//add table path
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/'.$com.'/tables');

			$ucm = new JUcmContent(null, $type->type_alias);
			$this->to_migrate[$com] = array();
			$this->to_migrate[$com]['ucm'] = $ucm;
			$this->to_migrate[$com]['table_info'] = json_decode($ucm->type->type->table);
		}

	}

	public function close($code = 0){
		echo $code.' be be be!';
	}
}
// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('UCMMigrateCli')->execute();

