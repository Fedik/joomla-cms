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
 * The Type New Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerTypeNew extends TypesControllerTypeEdit
{
	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		// find a selected parent type
		$cid = $this->input->get('cid', array(), 'array');
		// take parent_id from selected or from request
		$type_id_parent = empty($cid[0]) ? $this->input->getInt('type_id_parent') : (int) $cid[0];
		if(!$type_id_parent)
		{
			$this->app->enqueueMessage(JText::_('COM_TYPES_TYPE_WRONG_PARENT'), 'error');
			$this->app->redirect('index.php?option=com_types');
		}
		$this->input->set('type_id_parent', $type_id_parent);

		return parent::execute();

		//$this->app->redirect('index.php?option=com_types&task=types.edit.type&type_id=0&layout_name=form&type_id_parent=' . $type_id_parent);
	}

}
