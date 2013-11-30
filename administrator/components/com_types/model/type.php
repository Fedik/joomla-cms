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
 * Types Component Type Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 */
class TypesModelType extends JModelBase
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 */
	protected $context = 'com_types.type';

	/**
	 * Instantiate the model.
	 *
	 * @param   JRegistry  $state  The model state.
	 */
	public function __construct(JRegistry $state = null)
	{
		parent::__construct($state);
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
	public function getTable($type = 'Contenttype', $prefix = 'JTable', $config = array())
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

		// Get Item View (Item Layout) name and save in to state
		// TODO: bad place for it ???
		$layout_name = $app->input->get('layout_name', 'form');
		$state->set('layout_name', $layout_name);
var_dump($layout_name);
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
		// get table
		$table = $this->getTable();

		if ($pk)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
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

		//get fields info
		$layout_name = $this->state->set('layout_name', 'form');
		$fields = UcmTypeHelper::getFields($item->type_alias, $layout_name, null, true);
		$layouts = UcmTypeHelper::getLayouts($item->type_alias);

		// Prepare fields params
		foreach($fields as $field) {
			$params = new JRegistry($field->params);
			$field->params = $params->toArray();
		}

		$item->set('layout_name', $layout_name);
		$item->set('fields', $fields);
		$item->set('layouts', $layouts);

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
		// If we have a data (mainly on save action)
		// save type_alias for future action,
		// @see: $this->preprocessForm()
		if($data)
		{
			$this->setState('type_alias', $data['type_alias']);
			$this->setState('layout_name', $data['layout_name']);
		}
		// Get the form.
		$form = $this->loadForm('com_types.type', 'type', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

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
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Register the paths @todo change to splqueue when JForm support it
		JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

		// Get the form.
		try
		{
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

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

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
		$data = JFactory::getApplication()->getUserState('com_types.edit.type.data', array());

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
	 * @see     JModelForm::preprocessForm()
	 *
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Get fields
		if(is_object($data))// form open
		{
			$fields = $data->get('fields');
		}
		elseif(is_array($data) && !empty($data['fields'])) // when data from State
		{
			$fields = $data['fields'];
		}
		else  // mainly when first Save attempt
		{
			$fields = UcmTypeHelper::getFields($this->getState('type_alias'), $this->getState('layout_name'), null, true);
		}

		// Get the form file for a fields main configuration
		JForm::addFormPath(JPATH_LIBRARIES . '/cms/form/form');
		$field_main_file = JPath::find(JForm::addFormPath(), 'field.xml');

		if(!empty($fields) && $field_main_file
			&& $fieldMainXMLRaw = file_get_contents($field_main_file))
		{
			$display = $this->getState('layout_name', 'form') == 'form' ? 'input' : 'value';
			foreach($fields as $field) {
				$field = is_array($field) ? (object) $field : $field;
				// Prepare XML data, overwrite {FIELD_NAME}
				$newFieldMain = str_replace('{FIELD_NAME}', $field->field_name,  $fieldMainXMLRaw);

				// Load form for the main field configuration
				$form->load($newFieldMain, true, '//fieldset[@name="' . $display . '"]/fields');

				// Now what about the addittional configurations...
				// TODO: this can be cached by TYPE
				$field_more_file = JPath::find(JForm::addFormPath(), $field->field_type . '.xml');
				if(!$field_more_file || !$fieldMoreXMLRaw = file_get_contents($field_more_file))
				{
					continue;
				}

				// Ok! Same procedure...
				$newFieldMore = str_replace('{FIELD_NAME}', $field->field_name,  $fieldMoreXMLRaw);
				$form->load($newFieldMore, true, '//fieldset[@name="' . $display . '"]/fields');

			}
		}

		//TODO: preprocessForm
		//parent::preprocessForm($form, $data, $group);
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

		// TODO: need to check the alias and so on
		if(!parent::save($data))
		{
			return false;
		}

		// Save Fields Layout options
		return $this->saveFieldsLayout($data);
	}

	/**
	 * Method to save the Fields Layout.
	 *
	 * @param   array  $data  The form data, that contain $data[fields].
	 *
	 * @return  boolean  True on success.
	 *
	 */
	public function saveFieldsLayout($data)
	{
		// If empty then nothing to do here
		if (empty($data['fields'])) {
			return true;
		}

		$dispatcher = JEventDispatcher::getInstance();

		// Get tables
		$tableType = $this->getTable();
		$tableLayout = $this->getTable('Layout', 'JTable');
		$tableFieldLayout = $this->getTable('FieldsLayouts', 'JTable');

		// Get type_id
		$key = $tableType->getKeyName();
		$type_id = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

		// Include the content plugins for the on save events.
		// TODO: "fields" or "content" or something else ???
		JPluginHelper::importPlugin('fields');

		try
		{
			// Load Layout
			$result = $tableLayout->load(array(
					'layout_name' => $data['layout_name'],
					'type_id' => $type_id,
			));
			if(!$result)
			{
				$this->setError('Layout should exist!');
				return false;
			}

			$layout_id = $tableLayout->layout_id;

			// So prepare and store the Fields
			foreach ($data['fields'] as $field) {
				// Load if new
				$tableFieldLayout->load(array('id' => $field['id']));

				// Set layout id
				$field['layout_id'] = $layout_id;
				// Prepare params
				if(isset($field['params']) && is_array($field['params']))
				{
					$params = new JRegistry($field['params']);
					$field['params'] = $params->toString();
				}
				$tableFieldLayout->bind($field);

				// Check the data.
				if (!$tableFieldLayout->check())
				{
					$this->setError($tableFieldLayout->getError());
					return false;
				}

				//TODO: Trigger the onFieldLayoutBeforeSave event.

				// Store the data.
				if (!$tableFieldLayout->store())
				{
					$this->setError($tableFieldLayout->getError());
					return false;
				}

				//TODO: Trigger the onFieldLayoutAfterSave event.

				// Reset the Table instead of call get new Instanse each time
				$tableFieldLayout->reset();
				$tableFieldLayout->{$tableFieldLayout->getKeyName()} = null;
			}

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}

}
