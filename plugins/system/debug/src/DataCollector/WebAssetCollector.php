<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetItem;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

/**
 * WebAssetCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetCollector extends AbstractDataCollector implements AssetProvider
{
	/**
	 * Collector name.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $name = 'webasset';

	/**
	 * Web asset manager instance.
	 *
	 * @var   WebAssetManager
	 * @since __DEPLOY_VERSION__
	 */
	private $wa;

	/**
	 * AbstractDataCollector constructor.
	 *
	 * @param   Registry         $params  Parameters.
	 * @param   WebAssetManager  $wa      Web asset manager instance
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(Registry $params, WebAssetManager $wa)
	{
		parent::__construct($params);

		$this->wa = $wa;
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns a hash where keys are control names and their values
	 * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return array
	 */
	public function getWidgets(): array
	{
		return [
			'webasset' => [
				'icon' => 'info-circle',
				'title' => 'WebAsset',
				'widget'  => 'PhpDebugBar.Widgets.WebAssetWidget',
				'map'     => $this->name,
				'default' => '{}',
			]
		];
	}

	/**
	 * Returns an array with the following keys:
	 *  - base_path
	 *  - base_url
	 *  - css: an array of filenames
	 *  - js: an array of filenames
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return array
	 */
	public function getAssets(): array
	{
		return [
			'js' => Uri::root(true) . '/media/plg_system_debug/widgets/webasset/widget.js'
		];
	}

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array Collected data
	 */
	public function collect(): array
	{
		$wr    = $this->wa->getRegistry();
		$state = $this->wa->getManagerState();

		// Prepare list of ALL available assets
		$wrRefl    = new \ReflectionClass($wr);
		$assetProp = $wrRefl->getProperty('assets');
		$assetProp->setAccessible(true);
		$registryAssets = $assetProp->getValue($wr);

		$availableAssets = [];

		foreach ($registryAssets as $type => $assets)
		{
			$availableAssets[$type] = [];

			foreach ($assets as $asset)
			{
				$availableAssets[$type][] = $this->extractAssetData($asset);
			}
		}

		// Prepare  list of active assets
		$activeAssets = [];

		foreach ($state['activeAssets'] as $type => $assetNames)
		{
			$activeAssets[$type] = [];

			foreach ($assetNames as $assetName => $itemState)
			{
				switch ($itemState)
				{
					case WebAssetManager::ASSET_STATE_ACTIVE:
						$stateStr = 'ACTIVE';
						break;
					case WebAssetManager::ASSET_STATE_DEPENDENCY:
						$stateStr = 'DEPENDENCY';
						break;
					default:
						$stateStr = '';
				}

				$activeAssets[$type][] = [
					'name'  => $assetName,
					'state' => $stateStr,
				];
			}
		}

		return [
			'active' => $activeAssets,
			'available' => $availableAssets,
			'registryFiles' => $state['registryFiles'],
		];
	}

	/**
	 * Extract data for DebugBar
	 *
	 * @param   WebAssetItem  $asset  Web asset item
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array
	 */
	private function extractAssetData(WebAssetItem $asset): array
	{
		return [
			'name'     => $asset->getName(),
			'uri'      => $asset->getUri(false),
			'version'  => $asset->getVersion(),
			'provider' => $asset->getOption('assetSource'),
			'isInline' => !!$asset->getOption('inline'),
			'dependencies' => $asset->getDependencies(),
		];
	}
}
