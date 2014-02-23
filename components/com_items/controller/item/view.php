<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_items
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ItemsControllerItemView extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 */
	public $prefix = 'Items';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 */
	public function execute()
	{
		// Get the document object.
	    $document     	= JFactory::getDocument();

	    $viewName     	= empty($this->options[2]) ? '' : strtolower($this->options[2]);
	    $viewFormat   	= $document->getType();
	    $layoutName   	= $this->input->getWord('layout', 'default');
	    $itemLayoutName = empty($this->options[3]) ? '' : strtolower($this->options[3]);

	    if(!$itemLayoutName)
	    {
	    	throw new LogicException('Item layout_name must be defined.', 500);
	    }

	    // Register the layout paths for the view
	    $paths = new SplPriorityQueue;
	    $paths->insert(JPATH_COMPONENT . '/view/' . $viewName . '/tmpl', 'normal');

	    $viewClass  = $this->prefix . 'View' . ucfirst($viewName) . ucfirst($viewFormat);
	    $modelClass = $this->prefix . 'Model' . ucfirst($viewName);

	    if (!class_exists($viewClass))
	    {
	    	throw new LogicException('View Class "' . $viewClass . '" not exists.', 404);
	    }

	    if (!class_exists($modelClass))
	    {
	    	throw new LogicException('Model Class "' . $modelClass . '" not exists.', 404);
	    }

	    // Get View
	    $model = new $modelClass;
	    $view  = new $viewClass($model, $paths);
	    $view->setLayout($layoutName);

	    // Render our view.
	    echo $view->render();

	    return true;
	}

}
