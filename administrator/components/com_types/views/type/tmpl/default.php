<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//var_dump($this->form);
?>
<form action="<?php echo JRoute::_('index.php?option=com_types&layout=edit&id=' . (int) $this->item->type_id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<!-- Base Type Info -->
			<?php foreach ($this->form->getGroup('base') as $field) : ?>
			<div class="control-group">
				<?php if (!$field->hidden) : ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
			<?php endforeach; ?>

			<div class="row-fluid">
				<div class="span10">
					Fields
				</div>
			</div>
		</div>
	</div>
	<!-- Hidden -->
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>