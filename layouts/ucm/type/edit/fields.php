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

foreach($displayData->item->get('fields') as $field_data) {
	// Get field group for main configuration
	$main = $displayData->form->getGroup('fields.' . $field_data->field_name);
	// .... and for Params
	$params = $displayData->form->getGroup('fields.' . $field_data->field_name . '.params');

	$field = array(
		'main' => $main,
		'params' => empty($params) ? array() : $params,
	);

	// All this dance was just for split the fields by the field state
	if($field_data->state)
	{
		$active[] = JLayoutHelper::render('ucm.type.edit.field', $field);
	}
	else
	{
		$inactive[] = JLayoutHelper::render('ucm.type.edit.field', $field);
	}

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
