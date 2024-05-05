<?php

namespace Joomla\CMS\Plugin;

use Joomla\Event\DispatcherInterface;

interface PrivateListenersProviderInterface
{
    /**
     * Registers the listeners.
     *
     * @param   DispatcherInterface  $dispatcher  A dispatcher instance to which the listeners should be registered.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function registerPrivateListeners(DispatcherInterface $dispatcher): void;
}
