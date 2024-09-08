<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.testing
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\SampleData\Bigdata\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bigdata - Testing Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class BigdataPlugin extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Amount of steps this plugin will run.
     *
     * @var int
     *
     * @since   __DEPLOY_VERSION__
     */
    protected static $steps = 221;

    /**
     * Lorem ipsum dolor sit amet.
     *
     * @var string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $lorem = '';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        $steps   = self::$steps;
        $methods = [
            'onSampledataGetOverview' => 'onSampledataGetOverview',
        ];

        // Current API requires own event for each step :/
        for($i = 1; $i <= $steps; $i++) {
            $methods['onAjaxSampledataApplyStep' . $i] = 'onAjaxSampledataApplyStep';
        }

        return $methods;
    }

    /**
     * Get an overview of the proposed sampledata.
     *
     * @param  \Joomla\Event\Event  $event  Event instance
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onSampledataGetOverview(\Joomla\Event\Event $event): void
    {
        $this->loadLanguage();

        $data              = new \stdClass();
        $data->name        = $this->_name;
        $data->title       = Text::_('PLG_SAMPLEDATA_BIGDATA_OVERVIEW_TITLE');
        $data->description = Text::_('PLG_SAMPLEDATA_BIGDATA_OVERVIEW_DESC');
        $data->icon        = 'bolt';
        $data->steps       = self::$steps;

        $result   = $event->getArgument('result', []);
        $result[] = $data;
        $event->setArgument('result', $result);
    }

    /**
     * Generic listener
     *
     * @param  AjaxEvent  $event  Event instance
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onAjaxSampledataApplyStep(AjaxEvent $event): void
    {
        $app   = $this->getApplication();
        $input = $app->getInput();

        if ($input->get('type') !== $this->_name) {
            return;
        }

        $step = $input->getInt('step', 0);

        $response            = [];
        $response['success'] = true;
        $response['message'] = '';

        $createMenu = true;

        try
        {
            // Load lorem text
            $this->lorem = file_get_contents(__DIR__ . '/lorem.txt');

            // Create a category
            if ($step === 1) {
                $catTitle = 'Big data ' . date('Y-m-d H:i:s');
                $catIds   = $this->addCategories([[
                    'title'     => $catTitle,
                    'parent_id' => 1,
                ]], 'com_content');

                $app->setUserState('sampledata.bigdata.catids', $catIds);

                if ($createMenu) {
                    // Create a menu
                    $menutypes = $this->addMenus([[
                        'title'    => $catTitle,
                        'menutype' => 'bigd-' . date('YmdHis'),
                    ]]);

                    $app->setUserState('sampledata.bigdata.menutypes', $menutypes);

                    // Create a menu item for the category
                    $this->addMenuItems([[
                        'menutype'     => reset($menutypes),
                        'title'        => $catTitle,
                        'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $catIds[0],
                        'component_id' => ComponentHelper::getComponent('com_content')->id,
                    ]]);
                }
            }

            // Create an articles
            else {
                $amount   = 10;
                $articles = [];
                $catIds   = $app->getUserState('sampledata.bigdata.catids', []);
                $catId    = reset($catIds);

                if (!$catId) {
                    throw new \UnexpectedValueException('Category ID not found');
                }

                for($i = 1; $i <= $amount; $i++) {
                    $articles[] = [
                        'catid' => $catId,
                    ];
                }

                $articles = $this->addArticles($articles);

                // Create menu item for each article
                if ($createMenu) {
                    $menutypes = $app->getUserState('sampledata.bigdata.menutypes', []);
                    $menutype  = reset($menutypes);
                    $menuitems = [];

                    if (!$menutype) {
                        throw new \UnexpectedValueException('Menutype not found');
                    }

                    foreach ($articles as $artId => $article) {
                        $menuitems[] = [
                            'menutype'     => $menutype,
                            'title'        => $article['title'],
                            'link'         => 'index.php?option=com_content&view=article&id=' . $artId,
                            'component_id' => ComponentHelper::getComponent('com_content')->id,
                        ];
                    }

                    $this->addMenuItems($menuitems);
                }
            }

            $response['message'] = 'Step ' . $step . ' finished with great success!';
        } catch (\Throwable $e) {
            $response['success'] = false;
            $response['message'] = 'Step ' . $step . ' failed with error: ' . $e->getMessage();
        }

        $event->addResult($response);
    }

    /**
     * Adds categories.
     *
     * @param   array    $categories  Array holding the category arrays.
     * @param   string   $extension   Name of the extension.
     *
     * @return  array  IDs of the inserted categories.
     *
     * @throws  \Exception
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function addCategories(array $categories, string $extension): array
    {
        $app      = $this->getApplication();
        $catModel = $app->bootComponent('com_categories')->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);
        $catIds   = [];
        $user     = $app->getIdentity();

        foreach ($categories as $category) {
            $category = $this->checkDefaultValues($category);

            $category['description']     = $category['description'] ?? $this->text(30, 80);
            $category['created_user_id'] = $category['created_user_id'] ?? $user->id;
            $category['extension']       = $extension;
            $category['level']           = $category['level'] ?? 1;

            if (!$catModel->save($category)) {
                throw new \Exception($catModel->getError());
            }

            // Get ID from category we just added
            $catIds[] = $catModel->getState($catModel->getName() . '.id');
        }

        return $catIds;
    }

    /**
     * Adds articles.
     *
     * @param   array  $articles  Array holding the article arrays.
     *
     * @return  array[]  Array of the inserted items, id => article
     *
     * @throws  \Exception
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function addArticles(array $articles): array
    {
        $app        = $this->getApplication();
        $user       = $app->getIdentity();
        $mvcFactory = $app->bootComponent('com_content')->getMVCFactory();
        $ids        = [];

        foreach ($articles as $article) {
            /** @var \Joomla\Component\Content\Administrator\Model\ArticleModel $model */
            $model = $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

            $article = $this->checkDefaultValues($article);

            $article['introtext']       = $article['introtext'] ?? $this->text(30, 80);
            $article['fulltext']        = $article['fulltext'] ?? $this->text(30, 300);
            $article['created_user_id'] = $article['created_user_id'] ?? $user->id;
            $article['featured']        = $article['featured'] ?? 0;

            // Set images to empty if not set.
            if (!empty($article['images'])) {
                // JSON Encode it when set.
                $article['images'] = json_encode($article['images']);
            }

            if (!$model->save($article)) {
                $app->getLanguage()->load('com_content');
                throw new \Exception(Text::_($model->getError()));
            }

            // Get ID from category we just added
            $id = $model->getState($model->getName() . '.id');

            $article['id'] = $id;

            $ids[$id] = $article;
        }

        return $ids;
    }

    /**
     * Adds menus.
     *
     * @param   array  $menus  Array holding the menus arrays.
     *
     * @return  array  Menutypes of the inserted items: id => menutype
     *
     * @throws  \Exception
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function addMenus(array $menus): array
    {
        /** @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model */
        $factory   = $this->getApplication()->bootComponent('com_menus')->getMVCFactory();
        $model     = $factory->createModel('Menu', 'Administrator', ['ignore_request' => true]);
        $menuTypes = [];

        foreach ($menus as $menu) {
            $menu['title']    = $menu['title'] ?? $this->sentence();
            $menu['menutype'] = ApplicationHelper::stringURLSafe($menu['menutype'] ?? $menu['title']);

            if (!$model->save($menu)) {
                throw new \Exception(Text::_($model->getError()));
            }

            $mid = $model->getState('menu.id');

            $menuTypes[$mid] = $menu['menutype'];
        }

        return $menuTypes;
    }

    /**
     * Adds menu items.
     *
     * @param   array  $menuItems  Array holding the menus arrays.
     *
     * @return  array  Ids of the inserted items.
     *
     * @throws  \Exception
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function addMenuItems(array $menuItems): array
    {
        $app      = $this->getApplication();
        $user     = $app->getIdentity();
        $factory  = $app->bootComponent('com_menus')->getMVCFactory();
        $itemIds  = [];

        foreach ($menuItems as $item) {
            /** @var \Joomla\Component\Menus\Administrator\Model\ItemModel $model */
            $model = $factory->createModel('Item', 'Administrator', ['ignore_request' => true]);

            $item = $this->checkDefaultValues($item);

            $item['type']              = $item['type'] ?? 'component';
            $item['created_user_id']   = $item['created_user_id'] ?? $user->id;
            $item['browserNav']        = 0;
            $item['client_id']         = 0;
            $item['level']             = 1;
            $item['parent_id']         = $item['parent_id'] ?? 1;
            $item['home']              = 0;
            $item['template_style_id'] = $item['template_style_id'] ?? 0;

            if (!$model->save($item)) {
                throw new \Exception(Text::_($model->getError()));
            }

            $itemIds[] = $model->getstate('item.id');
        }

        return $itemIds;
    }

    /**
     * Check default values
     *
     * @param array $item  Content item
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function checkDefaultValues(array $item): array
    {
        $item['id']           = 0;
        $item['access']       = $item['access'] ?? 1;
        $item['state']        = $item['state'] ?? 1;
        $item['published']    = $item['published'] ?? 1;
        $item['language']     = $item['language'] ?? '*';
        $item['associations'] = [];
        $item['metakey']      = '';
        $item['metadesc']     = '';
        $item['xreference']   = '';
        $item['params']       = [];
        $item['title']        = $item['title'] ?? $this->sentence();
        $item['alias']        = $item['title'] . '-' . rand(0, time());

        return $item;
    }

    protected function text($min = 30, $max = 120): string
    {
        $s = $this->lorem;
        $s = explode('.', $s);
        shuffle($s);
        $s = implode('.', $s);
        $s = ucfirst(substr($s, 0, rand($min, $max))) . '.';

        return $s;
    }

    protected function sentence($min = 10, $max = 30): string
    {
        $s = $this->lorem;
        $s = explode('.', $s);
        shuffle($s);

        $w = trim(substr(trim($s[0]), 0, rand($min, $max)), ',') . '.';

        return $w;
    }
}
