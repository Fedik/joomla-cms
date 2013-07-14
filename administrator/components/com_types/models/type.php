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
class TypesModelType extends JModelAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 *
	 */
	public function __construct($config = array())
	{
		if (isset($config['event_after_delete']))
		{
			$this->event_after_delete = $config['event_after_delete'];
		}
		elseif (empty($this->event_after_delete))
		{
			$this->event_after_delete = 'onTypeAfterDelete';
		}

		if (isset($config['event_after_save']))
		{
			$this->event_after_save = $config['event_after_save'];
		}
		elseif (empty($this->event_after_save))
		{
			$this->event_after_save = 'onTypeAfterSave';
		}

		if (isset($config['event_before_delete']))
		{
			$this->event_before_delete = $config['event_before_delete'];
		}
		elseif (empty($this->event_before_delete))
		{
			$this->event_before_delete = 'onTypeBeforeDelete';
		}

		if (isset($config['event_before_save']))
		{
			$this->event_before_save = $config['event_before_save'];
		}
		elseif (empty($this->event_before_save))
		{
			$this->event_before_save = 'onTypeBeforeSave';
		}

		if (isset($config['event_change_state']))
		{
			$this->event_change_state = $config['event_change_state'];
		}
		elseif (empty($this->event_change_state))
		{
			$this->event_change_state = 'onTypeChangeState';
		}

		if (isset($config['event_clean_cache']))
		{
			$this->event_clean_cache = $config['event_clean_cache'];
		}
		elseif (empty($this->event_clean_cache))
		{
			$this->event_clean_cache = 'onTypeCleanCache';
		}

		parent::__construct($config);
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
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 */
	protected function populateState()
	{
		parent::populateState();
		$app = JFactory::getApplication();

		// Get Item View (Item Layout) name and save in to state
		// TODO: bad place for it ???
		$item_view = $app->input->get('item_view', 'form');
		$this->setState('item_view', $item_view);
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
		$item = parent::getItem($pk);

		//get fields info
		$item_view = $this->getState('item_view', 'form');
		$fields = UCMTypeHelper::getFields($item->type_alias, $item_view, false);

		// Prepare fields params
		foreach($fields as $field) {
			$params = new JRegistry($field->params);
			$field->params = $params->toArray();
		}

		$item->set('item_view', $item_view);
		$item->set('fields', $fields);

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
		$form = $this->loadForm('com_types.type', 'type', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

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

		$this->preprocessData('com_types.type', $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
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
		// Get the form file for a fields main configuration
		JForm::addFormPath(JPATH_LIBRARIES . '/cms/form/form');
		$field_main_file = JPath::find(JForm::addFormPath(), 'field.xml');

		$fields = $data ? $data->get('fields') : null;

		if($fields && $field_main_file
			&& $fieldMainXMLRaw = file_get_contents($field_main_file))
		{
			$display = $data->get('item_view', 'form') == 'form' ? 'input' : 'value';
			foreach($fields as $field) {
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

		parent::preprocessForm($form, $data, $group);
	}

}
