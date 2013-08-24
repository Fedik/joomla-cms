<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//$displayData - UcmItem object
?>
<div class="page-<?php echo $displayData->getLayoutName();?>">
<?php foreach($displayData->fields as $field_name): ?>
	<div class="<?php echo $field_name;?>">
		<?php //TODO: display <label> if not empty ?>
		<?php echo $displayData->{$field_name}; ?>
	</div>
<?php endforeach; ?>
</div>
