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
		$active[] = JLayoutHelper::render('type.edit.field', $field, JPATH_COMPONENT . '/layouts');
	}
	else
	{
		$inactive[] = JLayoutHelper::render('type.edit.field', $field, JPATH_COMPONENT . '/layouts');
	}

}
?>
<!-- Make a bit nicer -->
<style>
#fields-group .field{
	margin-bottom: 12px;
}
#fields-group .field .main{
	border: 1px solid #13294A;
	padding: 8px;
}
#fields-group .field .params{
	display: none;
	border-left: 1px solid #0088CC;
	border-right: 1px solid #0088CC;
	border-bottom: 1px solid #0088CC;
	border-radius: 0 0 8px 8px;
	padding: 8px;
	margin: 0 8px;
}

</style>
<script type="text/javascript">
jQuery(document).ready(function(){
	// show/hide params
	jQuery('#fields-group a.show-params').bind('click', function(){
		jQuery(this).parents('.field').children('.params').slideToggle();
		return false;
	});
});
</script>
<div id="fields-group" class="row-fluid">
	<div class="active">
		<h4><?php echo JText::_('COM_TYPES_FIELDS_ACTIVE_LABEL'); ?></h4>
		<div class="control-group">
			<!-- Display active fields -->
			<!-- With posibility change status by Drag-and-Drop -->
			<?php echo implode("\n", $active); ?>
		</div>
	</div>
	<div class="inactive">
		<h4><?php echo JText::_('COM_TYPES_FIELDS_INACTIVE_LABEL'); ?></h4>
		<div class="control-group">
			<!-- Display inactive fields -->
			<?php echo implode("\n", $inactive); ?>
		</div>
	</div>
</div>
