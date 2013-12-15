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
 * The Type Base Display Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */

class TypesControllerBaseDisplay extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 */
	public $prefix = 'Types';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		// Get the document object.
	    $document     = JFactory::getDocument();

	    $viewName     = empty($this->options[2]) ? 'types' : strtolower($this->options[2]);
	    $viewFormat   = $document->getType();
	    $layoutName   = $this->input->getWord('layout', 'default');

	    // Register the layout paths for the view
	    $paths = new SplPriorityQueue;
	    $paths->insert(JPATH_COMPONENT . '/view/' . $viewName . '/tmpl', 'normal');

	    $viewClass  = $this->prefix . 'View' . ucfirst($viewName) . ucfirst($viewFormat);
	    $modelClass = $this->prefix . 'Model' . ucfirst($viewName);

	    if (!class_exists($viewClass) || !class_exists($modelClass))
	    {
	    	return false;
	    }

	    // Get View
	    $view = new $viewClass(new $modelClass, $paths);
	    $view->setLayout($layoutName);

	    // Render our view.
	    echo $view->render();

	    return true;
	}
}
