<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$active = array();
$inactive = array();

foreach($this->item->fields as $field) {
	// TODO: bad place for this !!!
	$field->setForm($this->form);

	//var_dump($field);
	break;
}
?>
<div class="row-fluid">
	<div class="">
		<h4><?php echo JText::_('COM_TYPES_FIELDS_ACTIVE_LABEL'); ?></h4>
		<div class="control-group">
			<?php echo implode("\n", $active); ?>
		</div>
	</div>
	<div class="">
		<h4><?php echo JText::_('COM_TYPES_FIELDS_INACTIVE_LABEL'); ?></h4>
		<div class="control-group">
			<?php echo implode("\n", $inactive); ?>
		</div>
	</div>
</div>