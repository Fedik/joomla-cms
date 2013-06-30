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
			<div class="control-group">
				<?php echo $this->form->getLabel('type_id'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('type_id'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('type_title'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('type_title'); ?>
				</div>
			</div>
			<div class="control-group">
				<?php echo $this->form->getLabel('type_alias'); ?>
				<div class="controls">
					<?php echo $this->form->getInput('type_alias'); ?>
				</div>
			</div>

			<!-- Fields Configuration -->
			<?php echo JLayoutHelper::render('joomla.edit.metadata', $this);//$this->loadTemplate('fields'); ?>
		</div>
	</div>

	<!-- Hidden -->
	<?php echo $this->form->getInput('item_view'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>