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
 * The Type Restore Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerTypeRestore extends JControllerBase
{

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		// Check for request forgeries.
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_types/table');
		include_once JPATH_COMPONENT_ADMINISTRATOR . '/helper/typesimport.php';

		// Init variables
		$typeTable = JTable::getInstance('Contenttype', 'JTable');
		$urlVar = $typeTable->getKeyName();
		$data  = $this->input->post->get('jform', array(), 'array');
		$layout_name = empty($data['layout_name']) ? 'form' : $data['layout_name'];
		$type_parts = explode('.', $data['type_alias']);
		$error = '';

		try{
			// Init importer
			$typesImport = new JUcmTypesImport($type_parts[0], $type_parts[1]);
			// Import Types
			$types = $typesImport->doTypes();
			$typesImport->doTypeViews($types);
		}
		catch (Exception $e){
			// Restore failed
			$error = JText::sprintf('JERROR_SAVE_FAILED', $e->getMessage());

		}
		if($error)
		{
			$this->app->enqueueMessage($error, 'error');
		}
		else {
			$this->app->enqueueMessage('Restore Success!');
		}
		// Redirect back.
		$this->app->redirect(JRoute::_('index.php?option=com_types&task=types.edit.type&type_id=' . $data['type_id'] . '&layout_name=' . $layout_name, false));

	}

}

