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
use Joomla\Component\Categories\Administrator\Model\CategoryModel;
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
    protected static $steps = 10;

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
        $app   = $event->getApplication();
        $input = $app->getInput();

        if ($input->get('type') !== $this->_name) {
            return;
        }

        $step = $input->getInt('step', 0);

        $response            = [];
        $response['success'] = true;
        $response['message'] = 'Step ' . $step;

        sleep(1);

        $event->addResult($response);
    }
}
