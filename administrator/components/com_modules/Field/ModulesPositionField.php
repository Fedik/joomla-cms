<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Modules Position field.
 *
 * @since  3.4.2
 */
class ModulesPositionField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.4.2
	 */
	protected $type = 'ModulesPosition';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.modulesposition';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$data = $this->getLayoutData();

		$clientId  = Factory::getApplication()->input->get('client_id', 0, 'int');
		$positions = HTMLHelper::_('modules.positions', $clientId, 1, $this->value);

		$data['client']    = $clientId;
		$data['positions'] = $positions;

		$renderer = $this->getRenderer($this->layout);
		$renderer->setComponent('com_categories');
		$renderer->setClient(1);

		return $renderer->render($data);
	}
}
