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
use Joomla\CMS\Form\Field\ModalSelectField;
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
 * Supports a modal article picker.
 *
 * @since  1.6
 */
class ArticleField extends ModalSelectField
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
        if (empty($this->layout)) {
            throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
        }

        Factory::getApplication()->getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];
        $canDo     = [
            'new'       => ((string) $this->element['new'] == 'true'),
            'edit'      => ((string) $this->element['edit'] == 'true'),
            'clear'     => ((string) $this->element['clear'] != 'false'),
            'select'    => ((string) $this->element['select'] != 'false'),
            'propagate' => ((string) $this->element['propagate'] == 'true') && count($languages) > 2,
        ];

        // Prepare links
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

        if ($language) {
            $linkArticles->setVar('forcedLanguage', $language);
            $linkArticle->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE') . ' &#8212; ' . $this->getTitle();
        } else {
            $modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
        }

        $urlSelect = $linkArticles;
        $urlEdit   = clone $linkArticle;
        $urlEdit->setVar('task', 'article.edit');
        $urlNew    = clone $linkArticle;
        $urlNew->setVar('task', 'article.add');

        $data                = $this->getLayoutData();
        $data['hint']        = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
        $data['canDo']       = $canDo;
        $data['urlSelect']   = (string) $urlSelect;
        $data['urlEdit']     = (string) $urlEdit;
        $data['urlNew']      = (string) $urlNew;
        $data['urlCheckin']  = (string) $linkCheckin;
        $data['valueTitle']  = $this->getValueTitle();
        $data['titleSelect'] = $modalTitle;
        $data['titleNew']    = Text::_('COM_CONTENT_NEW_ARTICLE');
        $data['titleEdit']   = Text::_('COM_CONTENT_EDIT_ARTICLE');
        $data['language']    = $language;
        $data['languages']   = $languages;

        return $this->getRenderer($this->layout)->render($data);
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput1()
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

        $urlSelect = $linkArticles;
        $urlEdit   = clone $linkArticle;
        $urlEdit->setVar('task', 'article.edit');
        $urlNew    = clone $linkArticle;
        $urlNew->setVar('task', 'article.add');

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
        $html  = '<div class="js-content-select-field">';

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
                . ' data-content-select-field-action="select" data-show-when-value=""'
                . ' data-modal-config="' . htmlspecialchars(json_encode($optionsSelect)) . '">'
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
                . ' data-content-select-field-action="create" data-show-when-value=""'
                . ' data-modal-config="' . htmlspecialchars(json_encode($optionsNew)) . '">'
                . '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
                . '</button>';
        }

        // Edit article button
        if ($allowEdit) {
            $optionsEdit = [
                'popupType'  => 'iframe',
                'src'        => (string) $urlEdit,
                'textHeader' => Text::_('COM_CONTENT_EDIT_ARTICLE'),
            ];
            $html .= '<button type="button" class="btn btn-primary"'
                . ($value ? '' : ' hidden')
                . ' data-content-select-field-action="edit" data-show-when-value="1"'
                . ' data-modal-config="' . htmlspecialchars(json_encode($optionsEdit)) . '"'
                . ' data-checkin-url="' . htmlspecialchars((string) $linkCheckin) . '">'
                . '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
                . '</button>';
        }

        // Clear article button
        if ($allowClear) {
            $html .= '<button type="button" class="btn btn-secondary"'
                . ($value ? '' : ' hidden')
                . ' data-content-select-field-action="clear" data-show-when-value="1">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        // Propagate article button
        if ($allowPropagate && count($languages) > 2) {
            // Strip off language tag at the end
            $tagLength            = (int) strlen((string) $this->element['language']);
            $callbackFunctionStem = substr("jSelectArticle_" . $this->id, 0, -$tagLength);

            $html .= '<button type="button" class="btn btn-primary"'
                . ($value ? '' : ' hidden')
                . ' title="' . htmlspecialchars(Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP')) . '"'
                . ' data-show-when-value="1"'
                . ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
                . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON')
                . '</button>';
        }

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '</span>';
        }

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
     * /
    protected function getLabel1()
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }*/

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
        }

        return $title ?: $value;
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
        $layout->setComponent('com_content');
        $layout->setClient(1);

        return $layout;
    }
}
