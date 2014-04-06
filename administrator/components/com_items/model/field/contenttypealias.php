<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 */
class JFormFieldContenttypealias extends JFormFieldContenttype
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 */
	public $type = 'Contenttypealias';

	/**
	 * Method to get a list of content types
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptions()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('a.type_alias AS value, a.type_title AS text')
			->from('#__content_types AS a')

			->order('a.type_title ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(JFormFieldList::getOptions(), $options);

		foreach ($options as $option)
		{
			//$option->text = mb_strtoupper(str_replace(' ', '_', $option->text), 'UTF-8');
			//$option->text = 'COM_TAGS_CONTENT_TYPE_' . $option->text;
			$option->text = JText::_($option->text);
		}

		return $options;
	}
}
