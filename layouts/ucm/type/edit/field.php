<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//$displayData - is array that contain the fields for Main and Params

?>
<div class="field control-group">
	<!-- Render inputs for a main configuration  -->
	<div class="main  form-inline">
	<?php foreach($displayData['main'] as $field): ?>
		<?php echo $field->label; ?>
		<?php echo $field->input; ?>
	<?php endforeach; ?>
	<?php if($displayData['params']): ?>
		<!--
			For show/hide .addittional using JavaScript
		-->
		<a href="#" class="show-params">More ...</a>
	<?php endif;?>
	</div>
	<?php if($displayData['params']): ?>
	<div class="params form-horizontal">
		<!--
			Render inputs for the addittional configuration if exist,
			Show/Hide this fields using SlideDown/SlideUp after click
			on "More ..." button
		 -->
		<?php foreach($displayData['params'] as $field): ?>
		<div class="control-group">
			<div class="control-label">
			<?php echo $field->label; ?>
			</div>
			<div class="controls">
			<?php echo $field->input; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif;?>

</div>
