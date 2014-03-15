<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<form action="<?php echo JRoute::_('index.php?option=com_types&task=types.edit.type&type_id=' . (int) $this->item->type_id); ?>"
	method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<!-- Base Type Info -->
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('type_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('type_id'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('type_title'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('type_title'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('type_alias'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('type_alias'); ?>
				</div>
			</div>

			<!-- Params -->
			<?php foreach($this->form->getGroup('params') as $field_param):?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field_param->label; ?>
					</div>
					<div class="controls">
						<?php echo $field_param->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="span10">
			<!-- Fields and Layouts -->
			<?php if(!empty($this->item->layouts)): ?>

			<!-- Layouts tabs -->
			<ul class="nav nav-tabs">
			<?php foreach($this->item->layouts as $layout):?>
				<li<?php if($layout->layout_name == $this->item->layout->layout_name) echo ' class="active"'?>>
				<?php
				$link = JRoute::_('index.php?option=com_types&task=types.edit.type&type_id=' . $this->item->type_id . '&layout_name=' . $layout->layout_name);
				echo JHtml::link($link, $layout->layout_title);
				?>
				</li>
			<?php endforeach;?>
			</ul>

			<!-- Layout editing -->
			<div class="form-inline form-inline-header">
			<?php foreach($this->form->getGroup('layout') as $layout_field): ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $layout_field->label; ?>
					</div>
					<div class="controls">
						<?php echo $layout_field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>

			<!-- Fields Configuration -->
			<?php echo JLayoutHelper::render('type.edit.fields', $this, JPATH_COMPONENT . '/layouts'); ?>
			<?php //echo $this->loadTemplate('fields'); ?>
			<?php endif;?>
		</div>
	</div>

	<!-- Hidden -->
	<?php
		echo $this->form->getInput('layout_name');
		echo $this->form->getInput('type_id_parent');
		echo JHtml::_('form.token');
	?>
	<input type="hidden" name="task" value="" />
</form>
