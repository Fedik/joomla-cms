<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Page Component Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_page
 */
class PageController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  If true, the view output will be cached
	 * @param   array    An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 */
	public function display($cachable = true, $urlparams = false)
	{
		// TODO: get this data from menu params, not from input
		// Common
		$type_alias = $this->input->get('type');
		$view = $this->input->get('view');
		$layout_name = $this->input->get('layout_name');
		// For Items
		if($view == 'items')
		{
			// Filter
			$filter = $this->input->get('filter', array(), 'ARRAY');
			$this->input->set('filter', $filter); // Define in case if empty
			// Ordering
			$ordering = $this->input->get('ordering', array(), 'ARRAY');
			$this->input->set('ordering', $ordering); // Define in case if empty

			//TODO: hmmm ... what about list layout ???
		}

		if($view == 'item' && !$type_alias)
		{
			// Cannot continue
			return false;
		}

		if(!$layout_name)
		{
			$this->input->set('layout_name', 'default');
		}


		// form view required other model, formModel
		if($layout_name == 'form')
		{
			//TODO: redirect to task=item.edit, or continue without redirect ?????
			//		and what about the form in the items list ???
// 			$model = $this->getModel('ItemForm');
// 			$view = $this->getView('Item', 'html', 'PageView');
// 			$view->setModel($model, true);
		}

		return parent::display($cachable, $urlparams);
	}
}

