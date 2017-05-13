<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Single Asset item class.
 *
 * @since   __DEPLOY_VERSION__
 */
class JAssetItem
{
	/**
	 * Asset name
	 *
	 * @var  string  $name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $name;

	/**
	 * Asset version
	 *
	 * @var  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $version;

	/**
	 * Whether attach the version to the scripts/stylesheets
	 *
	 * @var bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $versionAttach = false;

	/**
	 * Asset data file owner info.
	 * Just for debug, where it come from.
	 *
	 * @var array $owner
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $owner;

	/**
	 * Asset JavaScript files
	 *
	 * @var  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $js = array();

	/**
	 * Asset StyleSheet files
	 *
	 * @var  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $css = array();

	/**
	 * Asset dependency
	 *
	 * @var  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $dependency = array();

	/**
	 * Attributes of JavaScript/StyleSheet files
	 *
	 * @var array $attribute
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $attribute = array();

	/**
	 * Item weight
	 *
	 * @var float
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $weight = 0;

	/**
	 * Mark incative asset
	 *
	 * @var int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const ASSET_STATE_INACTIVE = 0;

	/**
	 * Mark ative asset. Just loaded, but WITHOUT dependency resolved
	 *
	 * @var int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const ASSET_STATE_ACTIVE = 1;

	/**
	 * Mark ative asset. Loaded WITH all dependency
	 *
	 * @var int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const ASSET_STATE_RESOLVED = 2;

	/**
	 * Mark ative asset that is loaded as Dependacy to another asset
	 *
	 * @var int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	const ASSET_STATE_DEPENDANCY = 3;

	/**
	 * Asset state
	 *
	 * @var bool $state
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $state = self::ASSET_STATE_INACTIVE;

	/**
	 * Deafult defer mode for attached JavaScripts
	 *
	 * @var bool $jsDeferMode
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $jsDeferMode = false;

	/**
	 * Class constructor
	 *
	 * @param   string  $name     The asset name
	 * @param   string  $version  The asset version
	 * @param   array   $owner    Asset data file-owner info.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($name, $version = null, array $owner = array())
	{
		$this->name    = $name;
		$this->version = $version;
		$this->owner   = $owner;
	}

	/**
	 * Return asset name
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return asset version
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Set JavaScript files
	 *
	 * @param   array  $js  Array of JavaScript files
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setJs(array $js)
	{
		$this->js = $js;

		return $this;
	}

	/**
	 * Return JavaScript files
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getJs()
	{
		return $this->js;
	}

	/**
	 * Allow to change default defer behaviour forJavaScript files
	 *
	 * @param   bool  $defer  Default "defer" mode for all javascrip files
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deferJavaScript($defer = true)
	{
		$this->jsDeferMode = (bool) $defer;

		return $this;
	}

	/**
	 * Set StyleSheet files
	 *
	 * @param   array  $css  Array of StyleSheet files
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCss(array $css)
	{
		$this->css = $css;

		return $this;
	}

	/**
	 * Return StyleSheet files
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCss()
	{
		return $this->css;
	}

	/**
	 * Set dependency
	 *
	 * @param   array  $dependency  The array of the names of the asset dependency
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDependency(array $dependency)
	{
		$this->dependency = $dependency;

		return $this;
	}

	/**
	 * Return dependency
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDependency()
	{
		return $this->dependency;
	}

	/**
	 * Set Attributes for asset file
	 *
	 * @param   string  $file        JavaScript/StyleSheet asset file
	 * @param   array   $attributes  Attributes array
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAttributes($file, array $attributes = array())
	{
		if (empty($this->attribute[$file]))
		{
			$this->attribute[$file] = array();
		}

		$this->attribute[$file] = array_merge($this->attribute[$file], $attributes);

		return $this;
	}

	/**
	 * Return Attributes for asset file
	 *
	 * @param   string  $file  JavaScript/StyleSheet asset file
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAttributes($file)
	{
		if (!empty($this->attribute[$file]))
		{
			return $this->attribute[$file];
		}

		return array();
	}

	/**
	 * Set asset Weight
	 *
	 * @param   float  $weight  The asset weight
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setWeight($weight)
	{
		$this->weight = (float) $weight;

		return $this;
	}

	/**
	 * Return asset Weight
	 *
	 * @return  float
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Set asset State
	 *
	 * @param   int  $state  The asset state
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setState($state)
	{
		$this->state = (int) $state;

		return $this;
	}

	/**
	 * Get asset State
	 *
	 * @return  int
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Check asset state
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive()
	{
		return $this->state !== self::ASSET_STATE_INACTIVE;
	}

	/**
	 * Set Version Attach property
	 *
	 * @param   bool  $value  True for attach the version parameter to the file
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function versionAttach($value)
	{
		$this->versionAttach = (bool) $value;

		return $this;
	}

	/**
	 * Check Version Attach property
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isVersionAttach()
	{
		return $this->versionAttach;
	}

	/**
	 * Attach active asset to the Document
	 *
	 * @param   JDocument  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  static
	 *
	 * @throws  RuntimeException If try attach inactive asset
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function attach(JDocument $doc)
	{
		if (!$this->isActive())
		{
			throw new RuntimeException('The inactive asset cannot be attached');
		}

		$version = false;

		// Calculate the version hash based on the asset version,
		if ($this->isVersionAttach())
		{
			$jversion = new JVersion;
			$version  = $jversion->generateMediaVersion($this->getVersion(), false);
		}

		$this->attachCss($doc, $version)->attachJs($doc, $version);

		return $this;
	}

	/**
	 * Attach StyleSheet files to the document
	 *
	 * @param   JDocument  $doc      Document for attach StyleSheet/JavaScript
	 * @param   mixed      $version  Version to be attached, or false
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function attachCss(JDocument $doc, $version = false)
	{
		foreach ($this->getCss() as $path)
		{
			$file       = $path;
			$isExternal = $this->isPathExternal($path);

			if (!$isExternal)
			{
				// Check for Placeholders
				$path = $this->replacePlaceholders($path);

				// Get the file path
				$file = JHtml::_('stylesheet', $path, array(), $this->isPathRelative($path), true);
			}

			if ($file)
			{
				$attribute = $this->getAttributes($path);
				$type      = empty($attribute['type']) ? 'text/css' : $attribute['type'];
				$media     = empty($attribute['media']) ? null : $attribute['media'];

				unset($attribute['type'], $attribute['media']);

				($version === false || $isExternal)
					? $doc->addStyleSheet($file, $type, $media, $attribute)
					: $doc->addStyleSheetVersion($file, $version, $type, $media, $attribute);
			}
		}

		return $this;
	}

	/**
	 * Attach JavaScript files to the document
	 *
	 * @param   JDocument  $doc      Document for attach StyleSheet/JavaScript
	 * @param   mixed      $version  Version to be attached, or false
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function attachJs(JDocument $doc, $version = false)
	{
		foreach ($this->getJs() as $path)
		{
			$file       = $path;
			$isExternal = $this->isPathExternal($path);

			if (!$isExternal)
			{
				// Check for Placeholders
				$path = $this->replacePlaceholders($path);

				// Get the file path
				$file = JHtml::_('script', $path, false, $this->isPathRelative($path), true);
			}

			if ($file)
			{
				$attribute = $this->getAttributes($path);
				$type      = empty($attribute['type']) ? 'text/javascript' : $attribute['type'];
				$defer     = empty($attribute['defer']) ? $this->jsDeferMode : (bool) $attribute['defer'];

				unset($attribute['type'], $attribute['defer']);

				// @TODO: Pass $attribute to addScript() when JDocument will support it
				($version === false || $isExternal)
					? $doc->addScript($file, $type, $defer)
					: $doc->addScriptVersion($file, $version, $type, $defer);
			}
		}

		return $this;
	}

	/**
	 * Replace Placeholders to the real values.
	 * Supported placeholders:
	 * 	[LANGUAGE_TAG]  Will be replaced to current language tag, eg: en-GB
	 *
	 * @param   string  $string  String to check for the placeholders
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function replacePlaceholders($string)
	{
		if (strpos($string, '[') === false)
		{
			// Nothing here
			return $string;
		}

		// Replace known placeholders
		// @TODO: Is it a good idea to allow to register custom placeholders, in the future ???
		$string = str_replace(array(
			'[LANGUAGE_TAG]'
		), array(
			JFactory::getLanguage()->getTag()
		), $string);

		return $string;
	}

	/**
	 * Check if the Path is External
	 *
	 * @param   string  $path  Path to test
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function isPathExternal($path)
	{
		return strpos($path, 'http') === 0 || strpos($path, '//') === 0;
	}

	/**
	 * Check if the Path is relative to /media folder
	 *
	 * @param   string  $path  Path to test
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function isPathRelative($path)
	{
		if (is_file(JPATH_ROOT . '/' . $path))
		{
			// We have a full path
			return false;
		}

		return true;
	}
}
