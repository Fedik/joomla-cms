<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal menu item picker.
 *
 * @since  3.7.0
 */
class MenuField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.7.0
     */
    protected $type = 'Modal_Menu';

    /**
     * Determinate, if the select button is shown
     *
     * @var     boolean
     * @since   3.7.0
     * /
    protected $allowSelect = true;

    /**
     * Determinate, if the clear button is shown
     *
     * @var     boolean
     * @since   3.7.0
     * /
    protected $allowClear = true;

    /**
     * Determinate, if the create button is shown
     *
     * @var     boolean
     * @since   3.7.0
     * /
    protected $allowNew = false;

    /**
     * Determinate, if the edit button is shown
     *
     * @var     boolean
     * @since   3.7.0
     * /
    protected $allowEdit = false;

    /**
     * Determinate, if the propagate button is shown
     *
     * @var     boolean
     * @since   3.9.0
     * /
    protected $allowPropagate = false;
     */

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.7.0
     * /
    public function __get($name)
    {
        switch ($name) {
            case 'allowSelect':
            case 'allowClear':
            case 'allowNew':
            case 'allowEdit':
            case 'allowPropagate':
                return $this->$name;
        }

        return parent::__get($name);
    }*/

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.7.0
     * /
    public function __set($name, $value)
    {
        switch ($name) {
            case 'allowSelect':
            case 'allowClear':
            case 'allowNew':
            case 'allowEdit':
            case 'allowPropagate':
                $value       = (string) $value;
                $this->$name = !($value === 'false' || $value === 'off' || $value === '0');
                break;

            default:
                parent::__set($name, $value);
        }
    }*/

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                        For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if (!$return) {
            return $return;
        }

        $app = Factory::getApplication();

        $app->getLanguage()->load('com_menus', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];
        $clientId  = (int) $this->element['clientid'];

        // Prepare enabled actions
        $this->canDo['propagate']  = ((string) $this->element['propagate'] == 'true') && count($languages) > 2;

        // Creating/editing menu items is not supported in frontend.
        if (!$app->isClient('administrator')) {
            $this->canDo['new']  = false;
            $this->canDo['edit'] = false;
        }

        // Prepare Urls
        $linkItems = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkItems->setQuery([
            'option'                => 'com_menus',
            'view'                  => 'items',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            'client_id'             => $clientId,
            Session::getFormToken() => 1,
        ]);
        $linkItem = clone $linkItems;
        $linkItem->setVar('view', 'item');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option'                => 'com_menus',
            'task'                  => 'items.checkin',
            'format'                => 'json',
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkItems->setVar('forcedLanguage', $language);
            $linkItem->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_MENUS_SELECT_A_MENUITEM') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('COM_MENUS_SELECT_A_MENUITEM');
        }

        $urlSelect = $linkItems;
        $urlEdit   = clone $linkItem;
        $urlEdit->setVar('task', 'item.edit');
        $urlNew    = clone $linkItem;
        $urlNew->setVar('task', 'item.add');

        $this->urls['select']  = (string) $urlSelect;
        $this->urls['new']     = (string) $urlNew;
        $this->urls['edit']    = (string) $urlEdit;
        $this->urls['checkin'] = (string) $linkCheckin;

        // Prepare titles
        $this->modalTitles['select']  = $modalTitle;
        $this->modalTitles['new']     = Text::_('COM_MENUS_NEW_MENUITEM');
        $this->modalTitles['edit']    = Text::_('COM_MENUS_EDIT_MENUITEM');

        $this->hint = $this->hint ?: Text::_('COM_MENUS_SELECT_A_MENUITEM');

        return $return;
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
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $title ?: $value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since __DEPLOY_VERSION__
     */
    protected function getLayoutData()
    {
        $data             = parent::getLayoutData();
        $data['language'] = (string) $this->element['language'];

        return $data;
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getRenderer($layoutId = 'default')
    {
        $layout = parent::getRenderer($layoutId);
        $layout->setComponent('com_menus');
        $layout->setClient(1);

        return $layout;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.7.0
     */
    protected function getInput1()
    {
        $clientId    = (int) $this->element['clientid'];
        $languages   = LanguageHelper::getContentLanguages([0, 1], false);

        // Load language
        Factory::getLanguage()->load('com_menus', JPATH_ADMINISTRATOR);

        // The active article id field.
        $value = (int) $this->value ?: '';

        // Create the modal id.
        $modalId = 'Item_' . $this->id;

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Add the modal field script to the document head.
        $wa->useScript('field.modal-fields');

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($this->allowSelect) {
            static $scriptSelect = null;

            if (is_null($scriptSelect)) {
                $scriptSelect = [];
            }

            if (!isset($scriptSelect[$this->id])) {
                $wa->addInlineScript(
                    "
				window.jSelectMenu_" . $this->id . " = function (id, title, object) {
					window.processModalSelect('Item', '" . $this->id . "', id, title, '', object);
				}",
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkSuffix = '&amp;layout=modal&amp;client_id=' . $clientId . '&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
        $linkItems  = 'index.php?option=com_menus&amp;view=items' . $linkSuffix;
        $linkItem   = 'index.php?option=com_menus&amp;view=item' . $linkSuffix;
        $modalTitle = Text::_('COM_MENUS_SELECT_A_MENUITEM');

        if (isset($this->element['language'])) {
            $linkItems .= '&amp;forcedLanguage=' . $this->element['language'];
            $linkItem .= '&amp;forcedLanguage=' . $this->element['language'];
            $modalTitle .= ' &#8212; ' . $this->element['label'];
        }

        $urlSelect = $linkItems . '&amp;function=jSelectMenu_' . $this->id;
        $urlEdit   = $linkItem . '&amp;task=item.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
        $urlNew    = $linkItem . '&amp;task=item.add';

        if ($value) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $value, ParameterType::INTEGER);

            $db->setQuery($query);

            try {
                $title = $db->loadResult();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        // Placeholder if option is present or not
        if (empty($title)) {
            if ($this->element->option && (string) $this->element->option['value'] == '') {
                $title_holder = Text::_($this->element->option);
            } else {
                $title_holder = Text::_('COM_MENUS_SELECT_A_MENUITEM');
            }
        }

        $title = empty($title) ? $title_holder : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current menu item display field.
        $html  = '';

        if ($this->allowSelect || $this->allowNew || $this->allowEdit || $this->allowClear) {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35">';

        // Select menu item button
        if ($this->allowSelect) {
            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_select"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalSelect' . $modalId . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
                . '</button>';
        }

        // New menu item button
        if ($this->allowNew) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_new"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalNew' . $modalId . '">'
                . '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
                . '</button>';
        }

        // Edit menu item button
        if ($this->allowEdit) {
            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_edit"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalEdit' . $modalId . '">'
                . '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
                . '</button>';
        }

        // Clear menu item button
        if ($this->allowClear) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        // Propagate menu item button
        if ($this->allowPropagate && count($languages) > 2) {
            // Strip off language tag at the end
            $tagLength            = (int) strlen($this->element['language']);
            $callbackFunctionStem = substr("jSelectMenu_" . $this->id, 0, -$tagLength);

            $html .= '<button'
            . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
            . ' type="button"'
            . ' id="' . $this->id . '_propagate"'
            . ' title="' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
            . ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
            . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
            . '</button>';
        }

        if ($this->allowSelect || $this->allowNew || $this->allowEdit || $this->allowClear) {
            $html .= '</span>';
        }

        // Select menu item modal
//        if ($this->allowSelect) {
//            $html .= HTMLHelper::_(
//                'bootstrap.renderModal',
//                'ModalSelect' . $modalId,
//                [
//                    'title'      => $modalTitle,
//                    'url'        => $urlSelect,
//                    'height'     => '400px',
//                    'width'      => '800px',
//                    'bodyHeight' => 70,
//                    'modalWidth' => 80,
//                    'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
//                                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
//                ]
//            );
//        }
//
//        // New menu item modal
//        if ($this->allowNew) {
//            $html .= HTMLHelper::_(
//                'bootstrap.renderModal',
//                'ModalNew' . $modalId,
//                [
//                    'title'       => Text::_('COM_MENUS_NEW_MENUITEM'),
//                    'backdrop'    => 'static',
//                    'keyboard'    => false,
//                    'closeButton' => false,
//                    'url'         => $urlNew,
//                    'height'      => '400px',
//                    'width'       => '800px',
//                    'bodyHeight'  => 70,
//                    'modalWidth'  => 80,
//                    'footer'      => '<button type="button" class="btn btn-secondary"'
//                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'item\', \'cancel\', \'item-form\'); return false;">'
//                            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
//                            . '<button type="button" class="btn btn-primary"'
//                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'item\', \'save\', \'item-form\'); return false;">'
//                            . Text::_('JSAVE') . '</button>'
//                            . '<button type="button" class="btn btn-success"'
//                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'item\', \'apply\', \'item-form\'); return false;">'
//                            . Text::_('JAPPLY') . '</button>',
//                ]
//            );
//        }
//
//        // Edit menu item modal
//        if ($this->allowEdit) {
//            $html .= HTMLHelper::_(
//                'bootstrap.renderModal',
//                'ModalEdit' . $modalId,
//                [
//                    'title'       => Text::_('COM_MENUS_EDIT_MENUITEM'),
//                    'backdrop'    => 'static',
//                    'keyboard'    => false,
//                    'closeButton' => false,
//                    'url'         => $urlEdit,
//                    'height'      => '400px',
//                    'width'       => '800px',
//                    'bodyHeight'  => 70,
//                    'modalWidth'  => 80,
//                    'footer'      => '<button type="button" class="btn btn-secondary"'
//                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'item\', \'cancel\', \'item-form\'); return false;">'
//                            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
//                            . '<button type="button" class="btn btn-primary"'
//                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'item\', \'save\', \'item-form\'); return false;">'
//                            . Text::_('JSAVE') . '</button>'
//                            . '<button type="button" class="btn btn-success"'
//                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'item\', \'apply\', \'item-form\'); return false;">'
//                            . Text::_('JAPPLY') . '</button>',
//                ]
//            );
//        }

        // Note: class='required' for client side validation.
        $class = $this->required ? ' class="required modal-value"' : '';

        // Placeholder if option is present or not when clearing field
        if ($this->element->option && (string) $this->element->option['value'] == '') {
            $title_holder = Text::_($this->element->option);
        } else {
            $title_holder = Text::_('COM_MENUS_SELECT_A_MENUITEM');
        }

        $html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
            . '" data-text="' . htmlspecialchars($title_holder, ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';

        return $html;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.7.0
     */
    protected function getLabel1()
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }
}
