<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\FormField;

/**
 * Provides a modal content selection
 *
 * @since  __DEPLOY_VERSION__
 */
class ModalSelectField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'ModalSelect';

    /**
     * Layout to render
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $layout = 'joomla.form.field.modal-select';

    /**
     * Url to display selectable content in modal
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $urlSelect = '';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            $this->urlSelect = (string) $element['urlSelect'];
        }

        return $result;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        $data               = $this->getLayoutData();
        $data['urlSelect']  = $this->urlSelect;
        $data['valueTitle'] = $this->getValueTitle();

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getValueTitle()
    {
        return $this->value;
    }
}
