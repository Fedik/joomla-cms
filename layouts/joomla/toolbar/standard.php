<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

extract($displayData, EXTR_OVERWRITE);

/**
 * Layout variables
 * -----------------
 * @var   string  $id
 * @var   string  $onclick
 * @var   string  $class
 * @var   string  $text
 * @var   string  $btnClass
 * @var   string  $tagName
 * @var   string  $htmlAttributes
 * @var   string  $task             The task which should be executed
 * @var   bool    $listCheck        Boolean, whether selection from a list is needed
 * @var   string  $form             CSS selector for a target form
 * @var   bool    $formValidation   Whether the form need to be validated before run the task
 * @var   string  $showOnKey        Toggle button visibility: make it visible when key is pressed
 * @var   string  $hideOnKey        Toggle button visibility: make it hidden when key is pressed
 * @var   string  $message          Confirmation message before run the task
 */

$wa = Factory::getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('webcomponent.toolbar-button');

$tagName  = $tagName ?? 'button';

$taskAttr        = '';
$idAttr          = !empty($id) ? ' id="' . $id . '"' : '';
$listAttr        = !empty($listCheck) ? ' list-selection' : '';
$formAttr        = !empty($form) ? ' form="' . $this->escape($form) . '"' : '';
$validate        = !empty($formValidation) ? ' form-validation' : '';
$msgAttr         = !empty($message) ? ' confirm-message="' . $this->escape($message) . '"' : '';
$showOnKeyAttr   = !empty($showOnKey) ? ' hidden show-on-key="' . $this->escape($showOnKey) . '"' : '';
$hideOnKeyAttr   = !empty($hideOnKey) ? ' hide-on-key="' . $this->escape($hideOnKey) . '"' : '';

if (!empty($task)) {
    $taskAttr = ' task="' . $task . '"';
} elseif (!empty($onclick)) {
    $htmlAttributes .= ' onclick="' . $onclick . '"';
}

?>
<joomla-toolbar-button <?php echo $idAttr . $taskAttr . $listAttr . $formAttr . $validate . $msgAttr . $showOnKeyAttr . $hideOnKeyAttr; ?>>
<?php if (!empty($group)) : ?>
<a href="#" class="dropdown-item">
    <span class="<?php echo trim($class ?? ''); ?>"></span>
    <?php echo $text ?? ''; ?>
</a>
<?php else : ?>
<<?php echo $tagName; ?>
    class="<?php echo $btnClass ?? ''; ?>"
    <?php echo $htmlAttributes ?? ''; ?>
    >
    <span class="<?php echo trim($class ?? ''); ?>" aria-hidden="true"></span>
    <?php echo $text ?? ''; ?>
</<?php echo $tagName; ?>>
<?php endif; ?>
</joomla-toolbar-button>
