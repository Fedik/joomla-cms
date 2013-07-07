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
<div class="field">
	<!-- Render inputs for a main configuration  -->
	<div class="main control-group form-inline">
	<?php foreach($displayData['main'] as $field): ?>
		<?php echo $field->label; ?>
		<?php echo $field->input; ?>
	<?php endforeach; ?>
	<?php if($displayData['params']): ?>
		<!--
			For show/hide .addittional using JavaScript
		-->
		<a href="#" class="show-addittional">More ...</a>
	<?php endif;?>
	</div>
	<div class="addittional control-group form-inline">
		<!--
			Render inputs for the addittional configuration if exist,
			Show/Hide this fields using SlideDown/SlideUp after click
			on "More ..." button
		 -->
	<?php if($displayData['params']): ?>
		<?php foreach($displayData['params'] as $field): ?>
			<?php echo $field->label; ?>
			<?php echo $field->input; ?>
		<?php endforeach; ?>
	<?php endif;?>
	</div>

</div>
