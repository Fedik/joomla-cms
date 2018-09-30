<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\Behavior;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

/**
 * The Behaviors for a System Assets
 *
 * @since  __DEPLOY_VERSION__
 */
class SystemBehavior implements SubscriberInterface
{
	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onWebAssetBeforeAttach' => 'onBeforeAttach',
		];
	}

	/**
	 * On Before Attach listener
	 *
	 * @param GenericEvent $event
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onBeforeAttach(GenericEvent $event)
	{
		/** @var \Joomla\CMS\WebAsset\WebAssetRegistry $registry */
		/** @var \Joomla\CMS\Document\Document $document */
		$registry = $event->getArgument('subject');
		$document = $event->getArgument('document');

		// Check whether Core asset are enabled, and add Core options
		$coreAsset = $registry->getAsset('core');
		if ($coreAsset && $coreAsset->isActive())
		{
			// Add core and base uri paths so javascript scripts can use them.
			$document->addScriptOptions(
				'system.paths',
				[
					'root' => Uri::root(true),
					'rootFull' => Uri::root(),
					'base' => Uri::base(true),
				]
			);
			$document->addScriptOptions('csrf.token', Session::getFormToken());
		}
	}
}
