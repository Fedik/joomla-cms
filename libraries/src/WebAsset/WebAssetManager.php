<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\WebAsset\Exception\InvalidActionException;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\CMS\WebAsset\Exception\UnsatisfiedDependencyException;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

/**
 * Web Asset Manager class
 *
 * @since  4.0.0
 */
class WebAssetManager implements WebAssetManagerInterface, DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * Mark inactive asset
	 *
	 * @var    integer
	 *
	 * @since  4.0.0
	 */
	const ASSET_STATE_INACTIVE = 0;

	/**
	 * Mark active asset. Just enabled, but WITHOUT dependency resolved
	 *
	 * @var    integer
	 *
	 * @since  4.0.0
	 */
	const ASSET_STATE_ACTIVE = 1;

	/**
	 * Mark active asset that is enabled as dependency to another asset
	 *
	 * @var    integer
	 *
	 * @since  4.0.0
	 */
	const ASSET_STATE_DEPENDENCY = 2;

	/**
	 * The WebAsset Registry instance
	 *
	 * @var    WebAssetRegistry
	 *
	 * @since  4.0.0
	 */
	protected $registry;

	/**
	 * A list of active assets (including their dependencies).
	 * Array of Name => State
	 *
	 * @var    array
	 *
	 * @since  4.0.0
	 */
	protected $activeAssets = [];

	/**
	 * Internal marker to check the manager state,
	 * to prevent use of the manager after an assets are rendered
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $locked = false;

	/**
	 * Whether append asset version to asset path
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $useVersioning = true;

	/**
	 * Internal marker to keep track when need to recheck dependencies
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $dependenciesIsActual = false;

	/**
	 * Class constructor
	 *
	 * @param   WebAssetRegistry  $registry  The WebAsset Registry instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(WebAssetRegistry $registry)
	{
		$this->registry = $registry;
	}

	/**
	 * Get associated registry instance
	 *
	 * @return   WebAssetRegistry
	 *
	 * @since  4.0.0
	 */
	public function getRegistry(): WebAssetRegistry
	{
		return $this->registry;
	}

	/**
	 * Adds support for magic method calls
	 *
	 * @param   string  $method     A method name
	 * @param   string  $arguments  An arguments for a method
	 *
	 * @return mixed
	 *
	 * @throws  \BadMethodCallException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __call($method, $arguments)
	{
		if (0 === strpos($method, 'enable'))
		{
			$type = strtolower(substr($method, 6));

			if (empty($arguments[0]))
			{
				throw new \BadMethodCallException('Asset name are required');
			}

			return $this->enableAsset($type, $arguments[0]);
		}

		if (0 === strpos($method, 'disable'))
		{
			$type = strtolower(substr($method, 7));

			if (empty($arguments[0]))
			{
				throw new \BadMethodCallException('Asset name are required');
			}

			return $this->disableAsset($type, $arguments[0]);
		}

		throw new \BadMethodCallException(sprintf('Undefined method %s in class %s', $method, get_class($this)));
	}

	/**
	 * Enable an asset item to be attached to a Document
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function enableAsset(string $type, string $name): WebAssetManagerInterface
	{
		if ($this->locked)
		{
			throw new InvalidActionException('WebAssetManager are locked, you came late');
		}

		// Check whether asset exists
		$this->registry->get($type, $name);

		if (empty($this->activeAssets[$type]))
		{
			$this->activeAssets[$type] = [];
		}

		// Asset already enabled
		if (!empty($this->activeAssets[$type][$name]))
		{
			// Set state to active, in case it was ASSET_STATE_DEPENDENCY
			$this->activeAssets[$type][$name] = static::ASSET_STATE_ACTIVE;

			return $this;
		}

		$this->activeAssets[$type][$name] = static::ASSET_STATE_ACTIVE;

		// To re-check dependencies
		$this->dependenciesIsActual = false;

		return $this;
	}

	/**
	 * Deactivate an asset item, so it will not be attached to a Document
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return  self
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function disableAsset(string $type, string $name): WebAssetManagerInterface
	{
		if ($this->locked)
		{
			throw new InvalidActionException('WebAssetManager are locked, you came late');
		}

		// Check whether asset exists
		$this->registry->get($type, $name);

		unset($this->activeAssets[$type][$name]);

		// To re-check dependencies
		$this->dependenciesIsActual = false;

		return $this;
	}

	/**
	 * Get a state for the Asset
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return  integer
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since  4.0.0
	 */
	public function getAssetState(string $type, string $name): int
	{
		// Check whether asset exists first
		$this->registry->get($type, $name);

		// Make sure that all dependencies are active
		if (!$this->dependenciesIsActual)
		{
			$this->enableDependencies();
		}

		if (!empty($this->activeAssets[$type][$name]))
		{
			return $this->activeAssets[$type][$name];
		}

		return static::ASSET_STATE_INACTIVE;
	}

	/**
	 * Check whether the asset are enabled
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return  boolean
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since  4.0.0
	 */
	public function isAssetActive(string $type, string $name): bool
	{
		return $this->getAssetState($type, $name) !== static::ASSET_STATE_INACTIVE;
	}

	/**
	 * Get all assets that was enabled
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   bool    $sort  Whether need to sort the assets to follow the dependency Graph
	 *
	 * @return  WebAssetItem[]
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  UnsatisfiedDependencyException When Dependency cannot be found
	 *
	 * @since  4.0.0
	 */
	public function getAssets(string $type, bool $sort = false): array
	{
		// Make sure that all dependencies are active
		if (!$this->dependenciesIsActual)
		{
			$this->enableDependencies();
		}

		if (empty($this->activeAssets[$type]))
		{
			return [];
		}

		if ($sort)
		{
			return $this->calculateOrderOfActiveAssets($type);
		}

		$assets = [];

		foreach (array_keys($this->activeAssets[$type]) as $name)
		{
			$assets[$name] = $this->registry->get($type, $name);
		}

		return $assets;
	}

	/**
	 * Lock the manager to prevent further modifications
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function lock(): self
	{
		$this->locked = true;

		return $this;
	}

	/**
	 * Update Dependencies state for all active Assets or only for given
	 *
	 * @param   WebAssetItem  $asset  The asset instance to which need to enable dependencies
	 *
	 * @return  self
	 *
	 * @since  4.0.0
	 */
	protected function enableDependencies(WebAssetItem $asset = null): self
	{
		if ($asset)
		{
			// Get all dependencies of given asset recursively
			$allDependencies = $this->getDependenciesForAsset($asset, true);

			foreach ($allDependencies as $depItem)
			{
				// Set dependency state only when it is inactive, to keep a manually activated Asset in their original state
				if (empty($this->activeAssets[$depItem->getType()][$depItem->getName()]))
				{
					$this->activeAssets[$depItem->getType()][$depItem->getName()] = static::ASSET_STATE_DEPENDENCY;
				}
			}
		}
		else
		{
			// Re-Check for dependencies for all active assets
			// Firstly, filter out only active assets
			foreach ($this->activeAssets as $type => $activeAsset)
			{
				$this->activeAssets[$type] = array_filter(
					$activeAsset,
					function ($state) {
						return $state === WebAssetManager::ASSET_STATE_ACTIVE;
					}
				);
			}

			// Secondary, check for dependencies of each active asset
			// This need to be separated from previous step because we may have "cross type" dependency
			foreach ($this->activeAssets as $type => $activeAsset)
			{
				foreach (array_keys($activeAsset) as $name)
				{
					$asset = $this->registry->get($type, $name);
					$this->enableDependencies($asset);
				}
			}

			$this->dependenciesIsActual = true;
		}

		return $this;
	}

	/**
	 * Attach active assets to the document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @throws InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  4.0.0
	 */
	public function attachActiveAssetsToDocument(Document $doc): WebAssetManagerInterface
	{
		if ($this->locked)
		{
			throw new InvalidActionException('WebAssetManager are locked, you came late');
		}

		// Trigger the event
		if ($this->getDispatcher())
		{
			$event = AbstractEvent::create(
				'onWebAssetBeforeAttach',
				[
					'eventClass' => 'Joomla\\CMS\\Event\\WebAsset\\WebAssetBeforeAttachEvent',
					'subject'  => $this,
					'document' => $doc,
				]
			);
			$this->getDispatcher()->dispatch($event->getName(), $event);
		}

		// Resolve an Order of Assets and their Dependencies
		$assets = $this->getAssets(true);

		// Prevent further use of manager if an attach  already happened
		$this->locked = true;

		// Pre-save existing Scripts, and attach them after requested assets.
		$jsBackup = $doc->_scripts;
		$doc->_scripts = [];

		// Attach active assets to the document
		foreach ($assets as $asset)
		{
			// Add StyleSheets of the asset
			foreach ($asset->getStylesheetFiles(true) as $path => $attr)
			{
				unset($attr['__isExternal'], $attr['__pathOrigin']);
				$version = $this->useVersioning ? ($asset->getVersion() ?: 'auto') : false;
				$doc->addStyleSheet($path, ['version' => $version], $attr);
			}

			// Add Scripts of the asset
			foreach ($asset->getScriptFiles(true) as $path => $attr)
			{
				unset($attr['__isExternal'], $attr['__pathOrigin']);
				$version = $this->useVersioning ? ($asset->getVersion() ?: 'auto') : false;
				$doc->addScript($path, ['version' => $version], $attr);
			}

			// Allow to Asset to add a Script options
			if ($asset instanceof WebAssetAttachBehaviorInterface)
			{
				$asset->onAttachCallback($doc);
			}
		}

		// Merge with previously added scripts
		$doc->_scripts = array_replace($doc->_scripts, $jsBackup);

		return $this;
	}

	/**
	 * Calculate weight of active Assets, by its Dependencies
	 *
	 * @param   string  $type  The asset type, script or style
	 *
	 * @return  WebAssetItem[]
	 *
	 * @since  4.0.0
	 */
	protected function calculateOrderOfActiveAssets($type): array
	{
		// See https://en.wikipedia.org/wiki/Topological_sorting#Kahn.27s_algorithm
		$graphOrder    = [];
		$activeAssets  = $this->getAssets($type, false);

		// Get Graph of Outgoing and Incoming connections
		$connectionsGraph = $this->getConnectionsGraph($activeAssets);
		$graphOutgoing    = $connectionsGraph['outgoing'];
		$graphIncoming    = $connectionsGraph['incoming'];

		// Make a copy to be used during weight processing
		$graphIncomingCopy = $graphIncoming;

		// Find items without incoming connections
		$emptyIncoming = array_keys(
			array_filter(
				$graphIncoming,
				function ($el) {
					return !$el;
				}
			)
		);

		// Loop through, and sort the graph
		while ($emptyIncoming)
		{
			// Add the node without incoming connection to the result
			$item = array_shift($emptyIncoming);
			$graphOrder[] = $item;

			// Check of each neighbor of the node
			foreach (array_reverse($graphOutgoing[$item]) as $neighbor)
			{
				// Remove incoming connection of already visited node
				unset($graphIncoming[$neighbor][$item]);

				// If there no more incoming connections add the node to queue
				if (empty($graphIncoming[$neighbor]))
				{
					$emptyIncoming[] = $neighbor;
				}
			}
		}

		// Sync Graph order with FIFO order
		$fifoWeights      = [];
		$graphWeights     = [];
		$requestedWeights = [];

		foreach (array_keys($this->activeAssets[$type]) as $index => $name)
		{
			$fifoWeights[$name] = $index * 10 + 10;
		}

		foreach (array_reverse($graphOrder) as $index => $name)
		{
			$graphWeights[$name]     = $index * 10 + 10;
			$requestedWeights[$name] = $activeAssets[$name]->getWeight() ?: $fifoWeights[$name];
		}

		// Try to set a requested weight, or make it close as possible to requested, but keep the Graph order
		while ($requestedWeights)
		{
			$item   = key($requestedWeights);
			$weight = array_shift($requestedWeights);

			// Skip empty items
			if ($weight === null)
			{
				continue;
			}

			// Check the predecessors (Outgoing vertexes), the weight cannot be lighter than the predecessor have
			$topBorder = $weight - 1;

			if (!empty($graphOutgoing[$item]))
			{
				$prevWeights = [];

				foreach ($graphOutgoing[$item] as $pItem)
				{
					$prevWeights[] = $graphWeights[$pItem];
				}

				$topBorder = max($prevWeights);
			}

			// Calculate a new weight
			$newWeight = $weight > $topBorder ? $weight : $topBorder + 1;

			// If a new weight heavier than existing, then we need to update all incoming connections (children)
			if ($newWeight > $graphWeights[$item] && !empty($graphIncomingCopy[$item]))
			{
				// Sort Graph of incoming by actual position
				foreach ($graphIncomingCopy[$item] as $incomingItem)
				{
					// Set a weight heavier than current, then this node to be processed in next iteration
					if (empty($requestedWeights[$incomingItem]))
					{
						$requestedWeights[$incomingItem] = $graphWeights[$incomingItem] + $newWeight;
					}
				}
			}

			// Set a new weight
			$graphWeights[$item] = $newWeight;
		}

		asort($graphWeights);

		// Get Assets in calculated order
		$resultAssets  = [];

		foreach (array_keys($graphWeights) as $name)
		{
			$resultAssets[$name] = $activeAssets[$name];
		}

		return $resultAssets;
	}

	/**
	 * Build Graph of Outgoing and Incoming connections for given assets.
	 *
	 * @param   WebAssetItem[]  $assets  Asset instances
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function getConnectionsGraph(array $assets): array
	{
		$graphOutgoing = [];
		$graphIncoming = [];

		foreach ($assets as $asset)
		{
			$name = $asset->getName();
			$graphOutgoing[$name] = array_combine($asset->getDependencies(), $asset->getDependencies());

			if (!array_key_exists($name, $graphIncoming))
			{
				$graphIncoming[$name] = [];
			}

			foreach ($asset->getDependencies() as $depName)
			{
				$graphIncoming[$depName][$name] = $name;
			}
		}

		return [
			'outgoing' => $graphOutgoing,
			'incoming' => $graphIncoming,
		];
	}

	/**
	 * Return dependencies for Asset as array of WebAssetItem objects
	 *
	 * @param   WebAssetItem  $asset          Asset instance
	 * @param   boolean       $recursively    Whether to search for dependancy recursively
	 * @param   WebAssetItem  $recursionRoot  Initial item to prevent loop
	 *
	 * @return  WebAssetItem[]
	 *
	 * @throws  UnsatisfiedDependencyException When Dependency cannot be found
	 *
	 * @since   4.0.0
	 */
	protected function getDependenciesForAsset(WebAssetItem $asset, $recursively = false, WebAssetItem $recursionRoot = null): array
	{
		$assets        = [];
		$recursionRoot = $recursionRoot ?? $asset;
		$type          = $asset->getType();

		foreach ($asset->getDependencies() as $depName)
		{
			$depType = $type;

			// Check for "depname#type" case
			if ($pos = strrpos($depName, '#'))
			{
				$depType = substr($depName, $pos + 1);
				$depName = substr($depName, 0, $pos);
			}

			// Skip already loaded in recursion
			if ($recursionRoot->getName() === $depName && $recursionRoot->getType() === $depType)
			{
				continue;
			}

			if (!$this->registry->exists($depType, $depName))
			{
				throw new UnsatisfiedDependencyException(
					sprintf('Unsatisfied dependency "%s" for an asset "%s"', $depName, $asset->getName())
				);
			}

			$dep = $this->registry->get($depType, $depName);

			$assets[$depType.$depName] = $dep;

			if (!$recursively)
			{
				continue;
			}

			$parentDeps = $this->getDependenciesForAsset($dep, true, $recursionRoot);
			$assets     = array_replace($assets, $parentDeps);
		}

		return $assets;
	}

	/**
	 * Whether append asset version to asset path
	 *
	 * @param   bool  $useVersioning  Boolean flag
	 *
	 * @return  self
	 *
	 * @since   4.0.0
	 */
	public function useVersioning(bool $useVersioning): self
	{
		$this->useVersioning = $useVersioning;

		return $this;
	}

	/**
	 * Dump available assets to simple array, with some basic info
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function debugAssets(): array
	{
		// Get all active assets in final order
		$assets = $this->getAssets(true);
		$result = [];

		foreach ($assets as $asset)
		{
			$result[$asset->getName()] = [
				'name'   => $asset->getName(),
				'deps'   => implode(', ', $asset->getDependencies()),
				'state'  => $this->getAssetState($asset->getName()),
			];
		}

		return $result;
	}
}
