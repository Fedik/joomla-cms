<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal article picker.
 *
 * @since  1.6
 */
class ArticleField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Modal_Article';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        $allowNew       = ((string) $this->element['new'] == 'true');
        $allowEdit      = ((string) $this->element['edit'] == 'true');
        $allowClear     = ((string) $this->element['clear'] != 'false');
        $allowSelect    = ((string) $this->element['select'] != 'false');
        $allowPropagate = ((string) $this->element['propagate'] == 'true');

        $languages = LanguageHelper::getContentLanguages([0, 1], false);

        // Load language
        Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

        // The active article id field.
        $value = (int) $this->value ?: '';
        $title = '';

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Add the modal field script to the document head.
        $wa->useScript('field.modal-fields')->useScript('content-select-field');

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect) {
            static $scriptSelect = null;

            if (is_null($scriptSelect)) {
                $scriptSelect = [];
            }

            if (!isset($scriptSelect[$this->id])) {
                $wa->addInlineScript(
                    "
				window.jSelectArticle_" . $this->id . " = function (id, title, catid, object, url, language) {
					window.processModalSelect('Article', '" . $this->id . "', id, title, catid, object, url, language);
				}",
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkArticles = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkArticles->setQuery([
            'option' => 'com_content',
            'view'   => 'articles',
            'layout' => 'modal',
            'tmpl'   => 'component',
            Session::getFormToken() => 1,
        ]);
        $linkArticle = clone $linkArticles;
        $linkArticle->setVar('view', 'article');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option' => 'com_content',
            'task'   => 'articles.checkin',
            'format' => 'json',
            Session::getFormToken() => 1,
        ]);

        if (isset($this->element['language'])) {
            $linkArticles->setVar('forcedLanguage', (string) $this->element['language']);
            $linkArticle->setVar('forcedLanguage', (string) $this->element['language']);

            $modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE') . ' &#8212; ' . $this->element['label'];
        } else {
            $modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
        }

        $urlSelect = $linkArticles; // . '&function=jSelectArticle_' . $this->id;
        $urlEdit   = clone $linkArticle;
        $urlEdit->setVar('task', 'article.edit'); // . '&task=article.edit&id=\' + document.getElementById(&quot;' . $this->id . '_id&quot;).value + \'';
        $urlNew    = clone $linkArticle;
        $urlNew->setVar('task', 'article.add'); // . '&task=article.add';

        if ($value) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' = :value')
                ->bind(':value', $value, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $title = $db->loadResult();
                $title = $title ? htmlspecialchars($title) : '';
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
            if (!$title) {
                $value = '';
            }
        }

        // The current article display field.
        $html  = '<div class="js-content-dialog-field">';

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control js-input-title" type="text" value="' . $title . '"'
            . ' id="' . $this->id . '_name" name="' . $this->name . '[name]" readonly'
            . ' placeholder="' . htmlspecialchars(Text::_('COM_CONTENT_SELECT_AN_ARTICLE')) . '">';

        // Select article button
        if ($allowSelect) {
            $optionsSelect = [
                'popupType'  => 'iframe',
                'src'        => (string) $urlSelect,
                'textHeader' => $modalTitle,
            ];
            $html .= '<button type="button" class="btn btn-primary"'
                . ($value ? ' hidden' : '')
                . ' data-dialog-field-action="select" data-show-when-value=""'
                . ' data-dialog-field="' . htmlspecialchars(json_encode($optionsSelect)) . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
                . '</button>';
        }

        // New article button
        if ($allowNew) {
            $optionsNew = [
                'popupType'  => 'iframe',
                'src'        => (string) $urlNew,
                'textHeader' => Text::_('COM_CONTENT_NEW_ARTICLE'),
            ];

            $html .= '<button type="button" class="btn btn-secondary"'
                . ($value ? ' hidden' : '')
                . ' data-dialog-field-action="create" data-show-when-value=""'
                . ' data-dialog-field="' . htmlspecialchars(json_encode($optionsNew)) . '">'
                . '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
                . '</button>';
        }

        // Edit article button
        if ($allowEdit) {
            $optionsEdit = [
                'popupType'  => 'iframe',
                'src'        => (string) $urlEdit,
                'textHeader' => Text::_('COM_CONTENT_EDIT_ARTICLE'),
                'checkinUrl' => (string) $linkCheckin,
            ];
            $html .= '<button type="button" class="btn btn-primary"'
                . ($value ? '' : ' hidden')
                . ' data-dialog-field-action="edit" data-show-when-value="1"'
                . ' data-dialog-field="' . htmlspecialchars(json_encode($optionsEdit)) . '">'
                . '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
                . '</button>';
        }

        // Clear article button
        if ($allowClear) {
            $html .= '<button type="button" class="btn btn-secondary"'
                . ($value ? '' : ' hidden')
                . ' data-dialog-field-action="clear" data-show-when-value="1">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        // Propagate article button
        if ($allowPropagate && count($languages) > 2) {
            // Strip off language tag at the end
            $tagLength            = (int) strlen((string) $this->element['language']);
            $callbackFunctionStem = substr("jSelectArticle_" . $this->id, 0, -$tagLength);

            $html .= '<button'
            . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
            . ' type="button"'
            . ' id="' . $this->id . '_propagate"'
            . ' title="' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
            . ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
            . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
            . '</button>';
        }

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '</span>';
        }

        /*// Select article modal
        if ($allowSelect) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalSelect' . $modalId,
                [
                    'title'      => $modalTitle,
                    'url'        => $urlSelect,
                    'height'     => '400px',
                    'width'      => '800px',
                    'bodyHeight' => 70,
                    'modalWidth' => 80,
                    'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                ]
            );
        }*/

        /*// New article modal
        if ($allowNew) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalNew' . $modalId,
                [
                    'title'       => Text::_('COM_CONTENT_NEW_ARTICLE'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlNew,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'article\', \'cancel\', \'item-form\'); return false;">'
                            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                            . '<button type="button" class="btn btn-primary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'article\', \'save\', \'item-form\'); return false;">'
                            . Text::_('JSAVE') . '</button>'
                            . '<button type="button" class="btn btn-success"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'article\', \'apply\', \'item-form\'); return false;">'
                            . Text::_('JAPPLY') . '</button>',
                ]
            );
        }*/

        /*// Edit article modal
        if ($allowEdit) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalEdit' . $modalId,
                [
                    'title'       => Text::_('COM_CONTENT_EDIT_ARTICLE'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlEdit,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'article\', \'cancel\', \'item-form\'); return false;">'
                            . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                            . '<button type="button" class="btn btn-primary"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'article\', \'save\', \'item-form\'); return false;">'
                            . Text::_('JSAVE') . '</button>'
                            . '<button type="button" class="btn btn-success"'
                            . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'article\', \'apply\', \'item-form\'); return false;">'
                            . Text::_('JAPPLY') . '</button>',
                ]
            );
        }*/

        // Note: class='required' for client side validation.
        $class  = $this->required ? 'required modal-value' : '';
        $class .= ' js-input-value';

        $html .= '<input type="hidden" id="' . $this->id . '_id" class="' . $class . '" data-required="' . (int) $this->required . '" name="' . $this->name
            . '" value="' . $value . '">';
        $html .= '</div>';

        return $html;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.4
     */
    protected function getLabel()
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }
}
