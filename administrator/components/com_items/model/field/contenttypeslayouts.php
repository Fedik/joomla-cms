<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 */
class JFormFieldContenttypeslayouts extends JFormField
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 */
	public $type = 'Contenttypeslayouts';

	/**
	 * Method to get a list of layouts
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getGroups()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('a.layout_name AS value, a.layout_title AS text, b.type_alias, b.type_title')
			->from('#__ucm_layouts AS a')
			->leftJoin('#__content_types AS b ON b.type_id = a.type_id')
			//->from('#__content_types AS a')

			->order('b.type_title ASC')
			->order('a.layout_id ASC');

		// Get the layouts.
		$db->setQuery($query);

		try
		{
			$layouts = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		$groups = array();

		foreach ($layouts as $layout)
		{

			$group_name =  $layout->type_alias;

			// Initialize the group if necessary.
			if (!isset($groups[$group_name]))
			{
				$groups[$group_name] = array(
					'id' => $this->id . '-' . $layout->type_alias,
					'label' => JText::_($layout->type_title),
					'items' => array(),
				);

			}

			// Create a new option object
			$tmp = JHtml::_(
				'select.option', $layout->value, JText::_($layout->text), 'value', 'text'
			);

			// Add the option.
			$groups[$group_name]['items'][] = $tmp;
		}

		return $groups;
	}

	/**
	 * Method to get the field input markup fora grouped list.
	 * Multiselect is enabled by using the multiple attribute.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		if (!is_array($this->value))
		{
			if (is_object($this->value))
			{
				$this->value = $this->value->tags;
			}

			if (is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field groups.
		$groups = (array) $this->getGroups();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ($this->readonly)
		{
			$html[] = JHtml::_(
				'select.groupedlist', $groups, null,
				array(
					'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value,
					'group.items' => 'items', 'group.label' => 'label', 'group.id' => 'id',
					'option.key.toHtml' => false, 'option.text.toHtml' => false,
				)
			);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}

		// Create a regular grouped list.
		else
		{
			$html[] = JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array(
					'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value,
					'group.items' => 'items', 'group.label' => 'label', 'group.id' => 'id',
					'option.key.toHtml' => false, 'option.text.toHtml' => false,

				)
			);
		}

		return implode($html);
	}
}
