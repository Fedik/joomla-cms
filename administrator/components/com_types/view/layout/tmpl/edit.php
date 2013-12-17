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
<form action="<?php echo JRoute::_('index.php?option=com_types&layout=edit&type_id=' . (int) $this->item->type_id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<!-- Base Layout Info -->
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('layout_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('layout_id'); ?>
				</div>
			</div>
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
					<?php echo $this->form->getLabel('layout_title'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('layout_title'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('layout_name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('layout_name'); ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Hidden -->
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
