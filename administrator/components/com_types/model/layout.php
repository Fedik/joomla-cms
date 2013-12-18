<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

defined('_JEXEC') or die;

/**
 * Types Component Layout Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 */
class TypesModelLayout extends JModelDatabase
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 */
	protected $context = 'com_types.layout';

	/**
	 * loaded items
	 * @var array
	 */
	protected $items = array();

	/**
	 * loaded forms
	 * @var array
	 */
	protected $forms = array();

	/**
	 * Instantiate the model.
	 *
	 * @param   JRegistry  $state  The model state.
	 */
	public function __construct(JRegistry $state = null, JDatabaseDriver $db = null)
	{
		$db = isset($db) ? $db : JFactory::getDbo();

		// add table path
		JTable::addIncludePath(JPATH_COMPONENT . '/table');

		parent::__construct($state, $db);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 */
	public function getTable($type = 'Layout', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Load the model state.
	 *
	 * @return  JRegistry  The state object.
	 */
	protected function loadState()
	{
		$state = parent::loadState();
		$app = JFactory::getApplication();
		$table = $this->getTable();

		// Get the pk of the record from the request.
		$pk = $app->input->getInt($table->getKeyName());
		$state->set($this->context . '.id', $pk);

		$type_id = $app->input->getInt('type_id');
		$state->set('type_id', $type_id);

		return $state;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->state->get($this->context . '.id');
		// check whether alredy loaded
		if(isset($this->items[$pk])) return $this->items[$pk];

		// get table
		$table = $this->getTable();
		// assign existed type_id
		$table->type_id = $this->state->get('type_id');

		if ($pk)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				throw new RuntimeException($table->getError());
			}
		}
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties();
		$item = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}

		// keep cached
		$this->items[$pk] = $item;

		return $item;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm($this->context, 'layout', array('control' => 'jform', 'load_data' => $loadData));
		return $form;
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->forms[$hash]) && !$clear)
		{
			return $this->forms[$hash];
		}

		// Register the paths @todo change to splqueue when JForm support it
		JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

		// Get the form.
		$form = JForm::getInstance($name, $source, $options, false, $xpath);

		if (isset($options['load_data']) && $options['load_data'])
		{
			// Get the data for the form.
			$data = $this->loadFormData();
		}
		else
		{
			$data = array();
		}
		// Allow for additional modification of the form, and events to be triggered.
		// We pass the data because plugins may require it.
		$this->preprocessForm($form, $data);

		// Load the data into the form after the plugins have operated.
		$form->bind($data);

		// Store the form for later.
		$this->forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->context . '.edit.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		//TODO: preprocessData
		//$this->preprocessData('com_types.type', $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 * Small trick for append the Fields form
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		//TODO: preprocessForm
		//parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);
		$app = JFactory::getApplication();

		// Check for an error.
		if ($return instanceof Exception)
		{
			$app->enqueueMessage($return->getMessage(), 'error');
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				// Check for an error.
				if ($message instanceof Exception)
				{
					$app->enqueueMessage($message->getMessage(), 'error');
					return false;
				} else {
					$app->enqueueMessage($message, 'error');
				}
			}

			return false;
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 */
	public function save($data)
	{
		// Prepare params
		if(isset($data['params']) && is_array($data['params']))
		{
			$params = new JRegistry($data['params']);
			$data['params'] = $params->toString();
		}
		//TODO: Trigger the events.
		$dispatcher = JEventDispatcher::getInstance();

		// TODO: need to check the alias and so on

		// Get tables
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Load the previous Data
		if (!$table->load($data[$key]))
		{
			throw new RuntimeException($table->getError());
		}

		unset($data[$key]);

		// Bind the data.
		if (!$table->bind($data))
		{
			throw new RuntimeException($table->getError());
		}

		// Check the data.
		if (!$table->check())
		{
			throw new RuntimeException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		return true;
	}

}
