<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$user = JFactory::getUser();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();
?>

<form action="<?php echo JRoute::_('index.php?option=com_types&view=types'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_TYPES_FILTER_SEARCH_DESC'); ?></label>
			<input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_TYPES_FILTER_SEARCH_DESC'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_TYPES_FILTER_SEARCH_DESC'); ?>" />
		</div>
		<div class="btn-group pull-left hidden-phone">
			<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
				<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
				<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
			<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
			</select>
		</div>
	</div>
	<div class="clearfix"> </div>
	<table class="table table-striped" id="articleList">
		<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 't.type_title', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
<?php foreach ($this->items as $i => $item) :?>
<?php
	// Check access
	$canCreate  = $user->authorise('core.create', 'com_types');
	$canEdit    = $user->authorise('core.edit', 'com_types');
	//$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
	//$canChange  = $user->authorise('core.edit.state', 'com_types') && $canCheckin;
?>
			<tr class="row<?php echo $i % 2; ?>" sortable-item-id="<?php echo $item->type_id; ?>">
				<td class="center hidden-phone">
					<?php echo JHtml::_('grid.id', $i, $item->type_id); ?>
				</td>
				<td class="nowrap has-context">
					<div class="pull-left">
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_types&task=type.edit&id=' . $item->type_id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
								<?php echo $this->escape($item->type_title); ?></a>
						<?php else : ?>
							<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->type_alias)); ?>"><?php echo $this->escape($item->type_title); ?></span>
						<?php endif; ?>
						<div class="small">
							<?php echo JText::_('JFIELD_ALIAS_LABEL') . ": " . $this->escape($item->type_alias); ?>
						</div>
					</div>
				</td>
			</tr>
<?php endforeach; ?>
		</tbody>
	</table>


<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>