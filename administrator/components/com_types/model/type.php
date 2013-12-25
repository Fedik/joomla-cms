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
class TypesModelType extends JModelDatabase
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 */
	protected $context = 'com_types.type';

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

		// keep a parent if any
		$type_id_parent = $app->input->getInt('type_id_parent');
		$state->set('type_id_parent', $type_id_parent);

		// Get Item View (Item Layout) name and save in to state
		// Use Form layout when we ceate a new type based on $type_id_parent
		// TODO: bad place for it ???
		$layout_name = $type_id_parent ? 'form' : $app->input->get('layout_name', 'form');
		$state->set('layout_name', $layout_name);



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

		// Get fields info
		$layout_name = $this->state->get('layout_name', 'form');
		$layouts 	 = UcmTypeHelper::getLayouts($item->type_alias);
		// Check whether layout exist
		$layout = null;
		if(empty($layouts[$layout_name]))
		{
			// Use the Form layout as base Type layout
			$layout_name = 'form';
			$this->state->set('layout_name', 'form');
			$layouts['form'] = empty($layouts['form']) ? (object) array(
				'layout_name' => 'form',
				'layout_title' => 'Form',
			) : $layouts['form'];
		}

		$layout = $layouts[$layout_name];

		// Get related Fields
		$fields = UcmTypeHelper::getFields($item->type_alias, $layout_name, null, true);

		// Prepare fields params
		foreach($fields as $field) {
			$params = new JRegistry($field->params);
			$field->params = $params->toArray();
		}

		$item->set('layout', $layout);
		$item->set('fields', $fields);
		$item->set('layouts', $layouts);
		$item->set('type_id_parent', $this->state->get('type_id_parent'));

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
		// If we have a data (mainly on save action)
		// save type_alias for future action,
		// @see: $this->preprocessForm()
		if($data)
		{
			$this->state->set('type_alias', $data['type_alias']);
			$this->state->set('layout_name', $data['layout']['layout_name']);
		}
		// Get the form.
		$form = $this->loadForm($this->context, 'type', array('control' => 'jform', 'load_data' => $loadData));
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
			$fields = UcmTypeHelper::getFields($this->state->get('type_alias'), $this->state->get('layout_name'), null, true);
		}

		// Get the form file for a fields main configuration
		//JForm::addFormPath(JPATH_LIBRARIES . '/cms/form/form');
		JForm::addFormPath(JPATH_COMPONENT . '/model/field/form');
		$field_main_file = JPath::find(JForm::addFormPath(), 'field.xml');

		if(!empty($fields) && $field_main_file
			&& $fieldMainXMLRaw = file_get_contents($field_main_file))
		{
			$display = $this->state->get('layout_name', 'form') == 'form' ? 'input' : 'value';
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
		$app = JFactory::getApplication();
		//TODO: Trigger the events.
		$dispatcher = JEventDispatcher::getInstance();

		// Get tables
		$table = $this->getTable();
		$key = $table->getKeyName();

		// handle new content type, created by user,
		// create new based on parent
		if($app->input->get('task') == 'types.save.type.new' && !empty($data['type_id_parent']))
		{
			$data[$key]		= null;
			$itemParent		= $this->getItem($data['type_id_parent']);
			$dataParent 	= $itemParent->getProperties();
			$alias_suffix 	= JFilterOutput::stringURLSafe($data['type_title']);
			$alias_suffix	= str_replace('-', '', strtolower($alias_suffix));

			if(!$alias_suffix)
			{
				throw new RuntimeException(JText::_('COM_TYPES_PROVIDE_VALID_TITLE'));
			}

			$data['type_alias'] = $dataParent['type_alias'] . '.' . $alias_suffix;
			$data = array_merge($dataParent, $data);

			// Reset field id`s
			foreach ($data['fields'] as $n => $field) {
				$field->id = null;
				$data['fields'][$n] = (array) $field;
			}
		}

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
		$data['type_id'] = $table->type_id;

		// Save layout
		$layoutModel = new TypesModelLayout;
		$data['layout']['type_id'] = $data['type_id'];
		$data['layout'] = $layoutModel->save($data['layout']);


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
		if (empty($data['fields']) || empty($data['layout']['layout_id']))
		{
			return true;
		}

		$dispatcher = JEventDispatcher::getInstance();

		// Get tables
		//$tableField  = $this->getTable('Field', 'JTable');
		$tableFieldLayout = $this->getTable('FieldsLayouts', 'JTable');

		// Get type_id
		$type_id = $data['type_id'];
		$layout_id = $data['layout']['layout_id'];

		// Include the content plugins for the on save events.
		// TODO: "fields" or "content" or something else ???
		JPluginHelper::importPlugin('fields');

		// So prepare and store the Fields
		foreach ($data['fields'] as $field) {
			// save a main field if new
// 			if(empty($field['field_id']))
// 			{
// 				// Store the Base Field first
// 				$field['type_id'] = $type_id;
// 				if(!$tableField->save($field))
// 				{
// 					throw new RuntimeException($tableField->getError());
// 				}
// 				$field['field_id'] = $tableField->field_id;
// 				// reset table
// 				$tableField->reset();
// 				$tableField->field_id = null;
// 			}

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
				throw new RuntimeException($tableFieldLayout->getError());
			}

			//TODO: Trigger the onFieldLayoutBeforeSave event.

			// Store the data.
			if (!$tableFieldLayout->store())
			{
				throw new RuntimeException($tableFieldLayout->getError());
			}

			//TODO: Trigger the onFieldLayoutAfterSave event.

			// Reset the Table instead of call get new Instanse each time
			$tableFieldLayout->reset();
			$tableFieldLayout->{$tableFieldLayout->getKeyName()} = null;
		}

		return true;
	}

}
