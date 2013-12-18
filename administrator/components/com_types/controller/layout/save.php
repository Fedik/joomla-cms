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
 * The Type Layout Save Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerLayoutSave extends TypesControllerBaseSave
{

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		$type_id = $this->input->getInt('type_id');

		//define context
		$this->context = 'com_types.layout.edit.data';


		// set redirects
		$this->redirect_error = 'index.php?option=com_types&task=types.edit.layout&type_id=' . $type_id;
		if(!empty($this->options[3]) && $this->options[3] == 'apply')
		{
			$this->redirect = $this->redirect_error;
		}
		else {
			$this->redirect = 'index.php?option=com_types&task=types.edit.type&type_id=' . $type_id . '&layout_name=form';
		}
var_dump($this);
exit;
		//return parent::execute();
	}
}
