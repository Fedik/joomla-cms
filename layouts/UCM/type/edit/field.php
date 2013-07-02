<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//$displayData - is UCMFormField object

$form_main = $displayData->getFormConfiguration();
$form_add = $displayData->getFormConfigurationAddittinal();
var_dump($form_main, $form_add);
?>
<div class="field">
 <?php echo 'a'; ?>
</div>