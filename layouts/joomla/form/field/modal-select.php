<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*
 * @var   string   $urlSelect
 * @var   string   $valueTitle
 */

// Add the field script
/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('field.modal-fields')->useScript('content-select-field');

$optionsSelect = [
    'popupType'  => 'iframe',
    'src'        => (string) $urlSelect,
    'textHeader' => Text::_('JSELECT'),
];

$fieldClass  = $required ? 'required modal-value' : '';
$fieldClass .= ' js-input-value';

?>

<div class="js-content-select-field <?php echo $class; ?>">
    <div class="input-group">
        <input class="form-control js-input-title" type="text" value="<?php echo $this->escape($valueTitle ?? $value); ?>" readonly
               id="<?php echo $id; ?>_name" name="<?php echo $name; ?>[name]"
               placeholder="<?php echo $this->escape($hint); ?>"/>

        <?php if ($urlSelect): ?>
        <button type="button" class="btn btn-primary"
                data-content-select-field-action="select"
                data-modal-config="<?php echo $this->escape(json_encode($optionsSelect)); ?>">
            <span class="icon-file" aria-hidden="true"></span> <?php echo Text::_('JSELECT'); ?>
        </button>
        <?php endif; ?>
    </div>

    <input type="hidden" id="<?php echo $id; ?>_id" class="<?php echo $fieldClass; ?>" data-required="<?php echo (int) $required; ?>"
           name="<?php echo $name; ?>" value="<?php echo $this->escape($value); ?>">
</div>
