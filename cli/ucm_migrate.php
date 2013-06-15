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


		//get main types
		//define objects to migrate
		$query = $this->db->getQuery(true);
		$query->select(array('type_id', 'type_alias'));
		$query->from('#__content_types');
		//$query->where('type_id IN (1,2,3,4,5,6,7,8,9,10)');
		$query->where('type_id IN (1)');
		$this->db->setQuery($query);

		$this->to_migrate = $this->db->loadObjectList();
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
			$alias_arr = explode('.', $info->type_alias);
			$com = $alias_arr[0];

			//add table path
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/'.$com.'/tables');

			$ucmContent = new JUcmContent(null, $info->type_alias);

			try {
				$this->migrate($ucmContent, $ucmContentTable, 0, 3);
			} catch (Exception $e) {
				// Display the error
				$this->out($e->getMessage(), true);
				// Close the app
				$this->close($e->getCode());
			}


		}

		//var_dump($this);
	}



	/**
	 * do migration
	 */
	protected function migrate($ucmContent, $ucmContentTable, $offset = 0, $limit = 0){

		//table info
		$tableObject = json_decode($ucmContent->type->type->table);
		//$table = JTable::getInstance($tableObject->special->type, $tableObject->special->prefix);

		//load items
		$query = $this->db->getQuery(true);
		$query->select('*');
		$query->from($tableObject->special->dbtable);
		$this->db->setQuery($query, $offset, $limit);
		$items_data = $this->db->loadAssocList();

		//bind to UCM, and save
		foreach($items_data as $data){
			$ucmData = $ucmContent->mapData($data);
			$primaryId = $ucmContent->getPrimaryKey($ucmData['common']['core_type_id'], $ucmData['common']['core_content_item_id']);

			if($primaryId){
				//already moved
				continue;
			}
			var_dump($primaryId);
			//$ucmContentTable->load($primaryId);
			$ucmContentTable->bind($ucmData['common']);
			$ucmContentTable->check();
			//$result = $ucmContentTable->store();

			var_dump($data['id'], $ucmContentTable);
		}



		var_dump($tableObject);

	}

	public function close($code = 0){
		echo $code.' be be be!';
	}
}
// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('UCMMigrateCli')->execute();

