<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Web Asset Item class
 *
 * @since  4.0.0
 */
class WebAssetItem implements WebAssetItemInterface
{
	/**
	 * Asset name
	 *
	 * @var    string  $name
	 * @since  4.0.0
	 */
	protected $name = '';

	/**
	 * The URI for the asset
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $uri = '';

	/**
	 * Additional options for the asset
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $options = [];

	/**
	 * Attributes for the asset, to be rendered in the asset's HTML tag
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $attributes = [];

	/**
	 * Asset dependencies
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $dependencies = [];

	/**
	 * Asset version
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $version = 'auto';

	/**
	 * Item weight
	 *
	 * @var    float
	 *
	 * @since  4.0.0
	 */
	protected $weight = 0;

	/**
	 * Class constructor
	 *
	 * @param   string  $name          The asset name
	 * @param   string  $uri           The URI for the asset
	 * @param   array   $options       Additional options for the asset
	 * @param   array   $attributes    Attributes for the asset
	 * @param   array   $dependencies  Asset dependencies
	 *
	 * @since   4.0.0
	 */
	public function __construct(
		string $name,
		string $uri = null,
		array $options = [],
		array $attributes = [],
		array $dependencies = []
	)
	{
		$this->name    = $name;
		$this->uri     = $uri;
		$this->version = array_key_exists('version', $options) ? $options['version'] : '';

		if (array_key_exists('attributes', $options))
		{
			$this->attributes = (array) $options['attributes'];
			unset($options['attributes']);
		}
		else
		{
			$this->attributes = $attributes;
		}

		if (array_key_exists('dependencies', $options))
		{
			$this->dependencies = (array) $options['dependencies'];
			unset($options['dependencies']);
		}
		else
		{
			$this->dependencies = $dependencies;
		}

		if (!empty($options['weight']))
		{
			$this->weight = (float) $options['weight'];
			unset($options['weight']);
		}

		unset($options['version'], $options['type']);

		$this->options = $options;
	}

	/**
	 * Return Asset name
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Return Asset version
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Return dependencies list
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * Set the desired weight for the Asset in Graph.
	 * Final weight will be calculated by AssetManager according to dependency Graph.
	 *
	 * @param   float  $weight  The asset weight
	 *
	 * @return  self
	 *
	 * @since   4.0.0
	 */
	public function setWeight(float $weight): WebAssetItemInterface
	{
		$this->weight = $weight;

		return $this;
	}

	/**
	 * Return the weight of the Asset.
	 *
	 * @return  float
	 *
	 * @since   4.0.0
	 */
	public function getWeight(): float
	{
		return $this->weight;
	}

	/**
	 * Get the file path
	 *
	 * @param   boolean  $resolvePath  Whether need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getUri($resolvePath = true): string
	{
		$path = $this->uri;

		if ($resolvePath && $path)
		{
			switch (pathinfo($path, PATHINFO_EXTENSION)){
				case 'js':
					$path = $this->resolvePath($path, 'script');
					break;
				case 'css':
					$path = $this->resolvePath($path, 'stylesheet');
					break;
				default:
					break;
			}
		}

		return $path ?? '';
	}

	/**
	 * Get the option
	 *
	 * @param   string  $key      An option key
	 * @param   string  $default  A default value
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOption(string $key, $default = null)
	{
		if (array_key_exists($key, $this->options))
		{
			return $this->options[$key];
		}

		return $default;
	}

	/**
	 * Get all options
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Get the attribute
	 *
	 * @param   string  $key      An attributes key
	 * @param   string  $default  A default value
	 *
	 * @return mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAttribute(string $key, $default = null)
	{
		if (array_key_exists($key, $this->attributes))
		{
			return $this->attributes[$key];
		}

		return $default;
	}

	/**
	 * Get all attributes
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

	/**
	 * Get CSS files
	 *
	 * @param   boolean  $resolvePath  Whether need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
//	public function getStylesheetFiles($resolvePath = true): array
//	{
//		if ($resolvePath)
//		{
//			$files = [];
//
//			foreach ($this->css as $path => $attr)
//			{
//				$resolved = $this->resolvePath($path, 'stylesheet');
//				$fullPath = $resolved['fullPath'];
//
//				if (!$fullPath)
//				{
//					// File not found, But we keep going ???
//					continue;
//				}
//
//				$files[$fullPath] = $attr;
//				$files[$fullPath]['__isExternal'] = $resolved['external'];
//				$files[$fullPath]['__pathOrigin'] = $path;
//			}
//
//			return $files;
//		}
//
//		return $this->css;
//	}

	/**
	 * Get JS files
	 *
	 * @param   boolean  $resolvePath  Whether we need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
//	public function getScriptFiles($resolvePath = true): array
//	{
//		if ($resolvePath)
//		{
//			$files = [];
//
//			foreach ($this->js as $path => $attr)
//			{
//				$resolved = $this->resolvePath($path, 'script');
//				$fullPath = $resolved['fullPath'];
//
//				if (!$fullPath)
//				{
//					// File not found, But we keep going ???
//					continue;
//				}
//
//				$files[$fullPath] = $attr;
//				$files[$fullPath]['__isExternal'] = $resolved['external'];
//				$files[$fullPath]['__pathOrigin'] = $path;
//			}
//
//			return $files;
//		}
//
//		return $this->js;
//	}

	/**
	 * Resolve path
	 *
	 * @param   string  $path  The path to resolve
	 * @param   string  $type  The resolver method
	 *
	 * @return string
	 *
	 * @since  4.0.0
	 */
	protected function resolvePath(string $path, string $type): string
	{
		if ($type !== 'script' && $type !== 'stylesheet')
		{
			throw new \UnexpectedValueException('Unexpected [type], expected "script" or "stylesheet"');
		}

		$file     = $path;
		$external = $this->isPathExternal($path);

		if (!$external)
		{
			// Get the file path
			$file = HTMLHelper::_(
				$type,
				$path,
				[
					'pathOnly' => true,
					'relative' => !$this->isPathAbsolute($path),
				]
			);
		}

		return $file ?? '';
	}

	/**
	 * Check if the Path is External
	 *
	 * @param   string  $path  Path to test
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function isPathExternal(string $path): bool
	{
		return strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, '//') === 0;
	}

	/**
	 * Check if the Path is relative to /media folder or absolute
	 *
	 * @param   string  $path  Path to test
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function isPathAbsolute(string $path): bool
	{
		// We have a full path or not
		return is_file(JPATH_ROOT . '/' . $path);
	}
}
