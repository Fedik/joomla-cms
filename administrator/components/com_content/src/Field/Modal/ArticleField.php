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
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . ' = :value')
                    ->bind(':value', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
                $title = $title ? htmlspecialchars($title) : '';
            } catch (\Throwable $e) {
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
