<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\GuidedTours\Extension;

use Joomla\CMS\Event\ConfigurableSubscriberInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;
use Joomla\Component\Guidedtours\Administrator\Model\TourModel;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guided Tours plugin to add interactive tours to the administrator interface.
 *
 * @since  4.3.0
 */
final class GuidedTours extends CMSPlugin implements SubscriberInterface, ConfigurableSubscriberInterface
{
    /**
     * A mapping for the step types
     *
     * @var    string[]
     * @since  4.3.0
     */
    protected $stepType = [
        GuidedtoursComponent::STEP_NEXT        => 'next',
        GuidedtoursComponent::STEP_REDIRECT    => 'redirect',
        GuidedtoursComponent::STEP_INTERACTIVE => 'interactive',
    ];

    /**
     * A mapping for the step interactive types
     *
     * @var    string[]
     * @since  4.3.0
     */
    protected $stepInteractiveType = [
        GuidedtoursComponent::STEP_INTERACTIVETYPE_FORM_SUBMIT    => 'submit',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_TEXT           => 'text',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_OTHER          => 'other',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_BUTTON         => 'button',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_CHECKBOX_RADIO => 'checkbox_radio',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_SELECT         => 'select',
    ];

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAjaxGuidedtours'   => 'startTour',
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

    /**
     * Method allows to set up custom event listeners.
     *
     * @param  \Joomla\Event\DispatcherInterface  $dispatcher  The dispatcher instance.
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function configureListeners(DispatcherInterface $dispatcher): void
    {
        if ($this->getApplication()->isClient('administrator')) {
            $dispatcher->addSubscriber($this);
        }
    }

    /**
     * Retrieve and starts a tour and its steps through Ajax.
     *
     * @return null|object
     *
     * @since   4.3.0
     */
    public function startTour(Event $event)
    {
        $tourId  = (int) $this->getApplication()->getInput()->getInt('id');
        $tourUid = $this->getApplication()->getInput()->getString('uid', '');
        $tourUid = $tourUid !== '' ? urldecode($tourUid) : '';

        $tour = null;

        // Load plugin language files
        $this->loadLanguage();

        if ($tourId > 0) {
            $tour = $this->getTour($tourId);
        } elseif ($tourUid !== '') {
            $tour = $this->getTour($tourUid);
        }

        $event->setArgument('result', $tour ?? new \stdClass());

        return $tour;
    }

    /**
     * Listener for the `onBeforeCompileHead` event
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function onBeforeCompileHead()
    {
        $app  = $this->getApplication();
        $doc  = $app->getDocument();
        $user = $app->getIdentity();

        if ($user != null && $user->id > 0) {
            // Load plugin language files
            $this->loadLanguage();

            Text::script('JCANCEL');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_BACK');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COMPLETE');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_NEXT');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_START');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_STEP_NUMBER_OF');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_TOUR_ERROR');

            $doc->addScriptOptions('com_guidedtours.token', Session::getFormToken());

            // Load required assets
            $doc->getWebAssetManager()
                ->usePreset('plg_system_guidedtours.guidedtours');

            // Temporary solution to auto-start the welcome tour
            if ($app->getInput()->getCmd('option', 'com_cpanel') === 'com_cpanel') {
                $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

                $tourModel = $factory->createModel(
                    'Tour',
                    'Administrator',
                    ['ignore_request' => true]
                );

                if ($tourModel->isAutostart('joomla-welcome')) {
                    $tour = $this->getTour('joomla-welcome');

                    $doc->addScriptOptions('com_guidedtours.autotour', $tour->id);

                    // Set autostart to '0' to avoid it to autostart again
                    $tourModel->setAutostart($tour->id, 0);
                }
            }
        }
    }

    /**
     * Get a tour and its steps or null if not found
     *
     * @param   integer|string  $tourId  The ID or Uid of the tour to load
     *
     * @return null|object
     *
     * @since   4.3.0
     */
    private function getTour($tourId)
    {
        $app = $this->getApplication();

        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        /** @var TourModel $tourModel */
        $tourModel = $factory->createModel(
            'Tour',
            'Administrator',
            ['ignore_request' => true]
        );

        $item = $tourModel->getItem($tourId);

        return $this->processTour($item);
    }

    /**
     * Return a tour and its steps or null if not found
     *
     * @param   CMSObject  $item  The tour to load
     *
     * @return null|object
     *
     * @since   5.0.0
     */
    private function processTour($item)
    {
        $app = $this->getApplication();

        $user    = $app->getIdentity();
        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        if (empty($item->id) || $item->published < 1 || !\in_array($item->access, $user->getAuthorisedViewLevels())) {
            return null;
        }

        // We don't want to show all parameters, so take only a subset of the tour attributes
        $tour = new \stdClass();

        $tour->id        = $item->id;
        $tour->autostart = $item->autostart;

        $stepsModel = $factory->createModel(
            'Steps',
            'Administrator',
            ['ignore_request' => true]
        );

        $stepsModel->setState('filter.tour_id', $item->id);
        $stepsModel->setState('filter.published', 1);
        $stepsModel->setState('list.ordering', 'a.ordering');
        $stepsModel->setState('list.direction', 'ASC');

        $steps = $stepsModel->getItems();

        $tour->steps = [];

        $temp = new \stdClass();

        $temp->id          = 0;
        $temp->title       = $this->getApplication()->getLanguage()->_($item->title);
        $temp->description = $this->getApplication()->getLanguage()->_($item->description);
        $temp->url         = $item->url;

        // Replace 'images/' to '../images/' when using an image from /images in backend.
        $temp->description = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $temp->description);

        $tour->steps[] = $temp;

        foreach ($steps as $i => $step) {
            $temp = new \stdClass();

            $temp->id               = $i + 1;
            $temp->title            = $this->getApplication()->getLanguage()->_($step->title);
            $temp->description      = $this->getApplication()->getLanguage()->_($step->description);
            $temp->position         = $step->position;
            $temp->target           = $step->target;
            $temp->type             = $this->stepType[$step->type];
            $temp->interactive_type = $this->stepInteractiveType[$step->interactive_type];
            $temp->params           = $step->params;
            $temp->url              = $step->url;
            $temp->tour_id          = $step->tour_id;
            $temp->step_id          = $step->id;

            // Replace 'images/' to '../images/' when using an image from /images in backend.
            $temp->description = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $temp->description);

            $tour->steps[] = $temp;
        }

        return $tour;
    }
}
