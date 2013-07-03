<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//$displayData - is array with a fields

$active = array();
$inactive = array();

foreach($displayData as $field) {
	if($field->state)
	{
		$active[] = JLayoutHelper::render('ucm.type.edit.field', $field);
	}
	else
	{
		$inactive[] = JLayoutHelper::render('ucm.type.edit.field', $field);
	}

	//var_dump($field);
	//break;
}
?>
<div class="row-fluid">
	<div class="">
		<h4><?php echo JText::_('COM_TYPES_FIELDS_ACTIVE_LABEL'); ?></h4>
		<div class="control-group">
			<!-- Display active fields -->
			<!-- With posibility change status by Drag-and-Drop -->
			<?php echo implode("\n", $active); ?>
		</div>
	</div>
	<div class="">
		<h4><?php echo JText::_('COM_TYPES_FIELDS_INACTIVE_LABEL'); ?></h4>
		<div class="control-group">
			<!-- Display inactive fields -->
			<?php echo implode("\n", $inactive); ?>
		</div>
	</div>
</div>