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
 * The Type Save Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerTypeSave extends TypesControllerBaseSave
{

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		$type_id 		= $this->input->getInt('type_id');
		$data   		= $this->input->get('jform', array(), 'array');
		$layout_name 	= empty($data['layout']['layout_name']) ? 'form' : $data['layout']['layout_name'];
		$type_id_parent = empty($data['type_id_parent']) ? 0 : $data['type_id_parent'];
		//define context
		$this->context 	= 'com_types.type.edit.data';

		// set redirects
		$this->redirect_error = 'index.php?option=com_types&task=types.edit.type&type_id=' . $type_id . '&layout_name=' . $layout_name;

		if(!empty($this->options[3]) && $this->options[3] == 'apply')
		{
			$this->redirect = $this->redirect_error;
		}
		elseif(!empty($this->options[3]) && $this->options[3] == 'new')
		{
			$this->redirect_error = 'index.php?option=com_types&task=types.new.type&type_id=0&layout_name=form'
									. '&type_id_parent=' . (int) $type_id_parent;
			//TODO: need redirect to just saved item
			$this->redirect = 'index.php?option=com_types';
		}
		else
		{
			$this->redirect = 'index.php?option=com_types';
		}

		return parent::execute();
	}
}
