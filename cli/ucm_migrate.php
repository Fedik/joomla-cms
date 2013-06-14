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
	 * Class constructor.
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JEventDispatcher $dispatcher = null)
	{
		parent::__construct($input, $config, $dispatcher);

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

		var_dump($this);
	}

	/**
	 * define objects to migrate
	 */
	protected function objectsToMigrate(JInputCli $input = null){
		//Content
		$this->to_migrate['com_content'] = array(
				'table' => JTable::getInstance('Content', 'JTable'),
		);

		//Tags
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_tags/tables');
		$this->to_migrate['com_tags'] = array(
				'table' => JTable::getInstance('Tag', 'TagsTable'),
		);

		//Weblinks
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_weblinks/tables');
		$this->to_migrate['com_weblinks'] = array(
				'table' => JTable::getInstance('Weblink', 'WeblinksTable'),
		);

		//Newsfeeds
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_newsfeeds/tables');
		$this->to_migrate['com_newsfeeds'] = array(
				'table' => JTable::getInstance('Newsfeed', 'NewsfeedsTable'),
		);

		//Contacts
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_contact/tables');
		$this->to_migrate['com_contact'] = array(
				'table' => JTable::getInstance('Contact', 'ContactTable'),
		);

		//Users
		$this->to_migrate['com_users'] = array(
				'table' => JTable::getInstance('User', 'JTable'),
		);
	}

	public function close($code = 0){
		echo $code.' be be be!';
	}
}
// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('UCMMigrateCli')->execute();

