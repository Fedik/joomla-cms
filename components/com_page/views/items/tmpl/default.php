<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_page
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php  if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif;  ?>
<div class="items-default">
<?php foreach ($this->items as $item): ?>
	<?php
	//TODO: need fallback to default.php if given layout not exist !!!
	$layout = 'ucm.types.' . $item->getLayoutPath();
	?>
	<?php echo JLayoutHelper::render($layout, $item); ?>
<?php endforeach; ?>
</div>
