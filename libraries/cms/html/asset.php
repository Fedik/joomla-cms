<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML Asset helper.
 *
 * @since  5.0
 */
class JHtmlAsset
{
	/**
	 * Make the asset active
	 *
	 * @param  string|JAssetItem  $asset  Asset instance or name
	 *
	 * @return void
	 */
	public static function load($asset)
	{
		$name    = $asset;
		$factory = JFactory::getAssetFactory();

		if ($asset instanceof JAssetItem)
		{
			$name = $asset->getName();
			$factory->addAsset($asset);
		}

		$factory->setAssetState($name, JAssetItem::ASSET_STATE_ACTIVE);
	}

	/**
	 * Make the asset inactive
	 *
	 * @param  string|JAssetItem  $asset  Asset instance or name
	 *
	 * @return void
	 */
	public static function unload($asset)
	{
		$name = ($asset instanceof JAssetItem) ? $asset->getName() : $asset;

		JFactory::getAssetFactory()->setAssetState($name, JAssetItem::ASSET_STATE_INACTIVE);
	}

	/**
	 * Add asset to the collection of known assets
	 *
	 * @param  JAssetItem  $asset
	 *
	 * @return void
	 */
	public static function add(JAssetItem $asset)
	{
		JFactory::getAssetFactory()->addAsset($asset);
	}
}
