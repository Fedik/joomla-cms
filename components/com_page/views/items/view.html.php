<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * HTML View class for the Page component
 *
 * @package     Joomla.Site
 * @subpackage  com_page
 */
class PageViewItems extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items  = $this->get('Items');
		$this->form  = $this->get('Forms'); //TODO ???
var_dump($this->items);


		parent::display($tpl);
	}
}
