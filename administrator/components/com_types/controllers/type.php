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
 * The Type Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class TypesControllerType extends JControllerForm
{

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 */
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();
		return ($user->authorise('core.create', 'com_types'));
	}

	/**
	 * Restore Type layouts and params from ucm.xml
	 *
	 */
	public function restore()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_types/tables');
		include_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/typesimport.php';

		// Init variables
		$typeTable = JTable::getInstance('Contenttype', 'JTable');
		$urlVar = $typeTable->getKeyName();
		$data  = $this->input->post->get('jform', array(), 'array');
		$type_parts = explode('.', $data['type_alias']);

		try{
			// Init importer
			$typesImport = new JUcmTypesImport($type_parts[0], $type_parts[1]);
			// Import Types
			$types = $typesImport->doTypes();
			$typesImport->doTypeViews($types);
		}
		catch (Exception $e){
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($data['type_id'], $urlVar), false
				),
				$e->getMessage(), 'error'
			);
			return false;
		}

		// Redirect back.
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($data['type_id'], $urlVar), false
			),
			'Restore Success!'
		);

		return true;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * /
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		//$app = JFactory::getApplication();
		//var_dump($_POST); exit;
	}
	*/

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$data = $this->input->post->get('jform', array(), 'array');
		if (!empty($data['layout_name']))
		{
			$append .= '&layout_name=' . $data['layout_name'];
		}
		else
		{
			$append .= '&layout_name=' . $this->input->get('layout_name', 'form');
		}

		return $append;
	}

}
