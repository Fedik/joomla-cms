<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * UCM Type helper.
 *
 * @package     Joomla.Administrator
 */
class UCMTypeHelper
{
	/**
	 * Return Content type Fields for given View.
	 *
	 * @param   string  $type_alias  Type alias.
	 * @param   string  $view  View name.
	 * @param   bool  $published_only  Return only active or not.
	 *
	 * @return  array Array with fields
	 */
	public static function getFields($type_alias, $view = 'form', $published_only = true)
	{
		return array();
	}

	/**
	 * Return Default Content type Fields.
	 *
	 * @param   string  $type_alias  Type alias.
	 *
	 * @return  array Array with fields
	 */
	public static function getFieldsDefault($type_alias)
	{
		$app = JFactory::getApplication();

		// Find file name
		$alias_parts = explode('.', $type_alias);
		$source = empty($alias_parts[1]) ? $alias_parts[0] : $alias_parts[1];

		// Add include folders
		$path_administrator = JPATH_ADMINISTRATOR . '/components/' . $alias_parts[0];

		JForm::addFormPath($path_administrator . '/models/forms');
		//JForm::addFieldPath($path_administrator . '/models/fields');
		JForm::addFormPath($path_administrator . '/model/form');
		//JForm::addFieldPath($path_administrator . '/model/field');

		// Get a original form.
		try
		{
			$form = JForm::getInstance($type_alias, $source, array(), true);
			//$fields = JForm::getInstance($type_alias, $source, array(), true, 'descendant-or-self::field');
			//$names = JForm::getInstance($type_alias, $source, array(), true, '//@name');

		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
			return array();
		}

		// XML Fields elements
		$elements = $form->getGroup(null);

		$fields = array();
		$i = 0;
		foreach ($elements as $element){
			// TODO: make JForm::getAtributes() ho ho ho!!!
			$refl = new ReflectionClass($element);
			$property = $refl->getProperty('element');
			$property->setAccessible(true);
			$element_xml = $property->getValue($element);

			$attributes = (array) $element_xml;
			$attributes = $attributes['@attributes'];

			$field = array(
				'field_id' => 0,
				'type' => $attributes['type'],
				'name' => $attributes['name'],
				'label' => isset($attributes['label']) ? $attributes['label'] : '',
				'default' => isset($attributes['default']) ? $attributes['default'] : '',
				'ordering' => $i,
				'state' => 1,
				'view' => 'form',
				'view_type' => 'input',
				'params' => $attributes,
			);

			$fields[] = $field;
			$i++;
		}

		return $fields;
	}

}