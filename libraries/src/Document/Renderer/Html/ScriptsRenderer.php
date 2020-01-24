<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\WebAsset\WebAssetItemInterface;

/**
 * JDocument head renderer
 *
 * @since  4.0.0
 */
class ScriptsRenderer extends DocumentRenderer
{
	/**
	 * List of already rendered src
	 *
	 * @var array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $renderedSrc = [];

	/**
	 * Renders the document script tags and returns the results as a string
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   4.0.0
	 */
	public function render($head, $params = array(), $content = null)
	{
		// Get line endings
		$lnEnd        = $this->_doc->_getLineEnd();
		$tab          = $this->_doc->_getTab();
		$buffer       = '';
		$wam          = $this->_doc->getWebAssetManager();
		$assets       = $wam->getAssets('script', true);

		$inlineAssets   = [];
		$inlineRelation = [];

		// Take out inline and their relations to non inline
		foreach ($assets as $k => $asset)
		{
			if (!$asset->getOption('inline'))
			{
				continue;
			}

			// Remove from assets list
			unset($assets[$k]);

			// Add to list of inline assets
			$inlineAssets[$asset->getName()] = $asset;

			// Check whether position are requested with dependencies
			$position = $asset->getOption('position');
			$position = $position === 'before' || $position === 'after' ? $position : null;
			$deps     = $asset->getDependencies();

			if ($position && $deps)
			{
				// If we have multiple dependencies, then use First for position "before"
				// And Last for position "after"
				$handle = $position === 'before' ? reset($deps) : end($deps);
				$inlineRelation[$handle][$position][$asset->getName()] = $asset;
			}
		}

		// Merge with existing scripts, for rendering
		$assets = array_merge(array_values($assets), $this->_doc->_scripts);

		// Generate script file links
		foreach ($assets as $key => $item)
		{
			// Check whether we have an Asset instance, or old array with attributes
			$asset = $item instanceof WebAssetItemInterface ? $item : null;

			// Add src attribute for non Asset item
			if (!$asset)
			{
				$item['src'] = $key;
			}

			// Check for inline content "before"
			if ($asset && !empty($inlineRelation[$asset->getName()]['before']))
			{
				foreach ($inlineRelation[$asset->getName()]['before'] as $itemBefore)
				{
					$buffer .= $this->renderInlineElement($itemBefore);

					// Remove this item from inline queue
					unset($inlineAssets[$itemBefore->getName()]);
				}
			}

			$buffer .= $this->renderElement($item);

			// Check for inline content "after"
			if ($asset && !empty($inlineRelation[$asset->getName()]['after']))
			{
				foreach ($inlineRelation[$asset->getName()]['after'] as $itemBefore)
				{
					$buffer .= $this->renderInlineElement($itemBefore);

					// Remove this item from inline queue
					unset($inlineAssets[$itemBefore->getName()]);
				}
			}
		}

		// Generate script declarations for assets
		foreach ($inlineAssets as $item)
		{
			$buffer .= $this->renderInlineElement($item);
		}

		// Generate script declarations for old scripts
		foreach ($this->_doc->_script as $type => $contents)
		{
			// Test for B.C. in case someone still store script declarations as single string
			if (\is_string($contents))
			{
				$contents = [$contents];
			}

			foreach ($contents as $content)
			{
				$buffer .= $this->renderInlineElement(
					[
						'type' => $type,
						'content' => $content,
					]
				);
			}
		}

		// Output the custom tags - array_unique makes sure that we don't output the same tags twice
		foreach (array_unique($this->_doc->_custom) as $custom)
		{
			$buffer .= $tab . $custom . $lnEnd;
		}

		return ltrim($buffer, $tab);
	}

	/**
	 * Renders the element
	 *
	 * @param   WebAssetItemInterface|array  $item  The element
	 *
	 * @return  string  The resulting string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function renderElement($item) : string
	{
		$buffer = '';
		$asset  = $item instanceof WebAssetItemInterface ? $item : null;
		$src    = $asset ? $asset->getUri() : ($item['src'] ?? '');

		// Make sure we have a src, and it not already rendered
		if (!$src || !empty($this->renderedSrc[$src]) || ($asset && $asset->getOption('webcomponent')))
		{
			return '';
		}

		$lnEnd        = $this->_doc->_getLineEnd();
		$tab          = $this->_doc->_getTab();
		$mediaVersion = $this->_doc->getMediaVersion();

		// Get the attributes and other options
		if ($asset)
		{
			$attribs     = $asset->getAttributes();
			$version     = $asset->getVersion();
			$conditional = $asset->getOption('conditional');
		}
		else
		{
			$attribs     = $item;
			$version     = isset($attribs['options']['version']) ? $attribs['options']['version'] : '';
			$conditional = !empty($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;
		}

		// For prevent double rendering
		$this->renderedSrc[$src] = true;

		// Check if script uses media version.
		if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto'))
		{
			$src .= '?' . ($version === 'auto' ? $mediaVersion : $version);
		}

		$buffer .= $tab;

		// This is for IE conditional statements support.
		if (!\is_null($conditional))
		{
			$buffer .= '<!--[if ' . $conditional . ']>';
		}

		// Render script with attributes
		$buffer .= '<script src="' . $src . '"';
		$buffer .= $this->renderAttributes($attribs);
		$buffer .= '></script>';

		// This is for IE conditional statements support.
		if (!\is_null($conditional))
		{
			$buffer .= '<![endif]-->';
		}

		$buffer .= $lnEnd;

		return $buffer;
	}

	/**
	 * Renders the inline element
	 *
	 * @param   WebAssetItemInterface|array  $item  The element
	 *
	 * @return  string  The resulting string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function renderInlineElement($item) : string
	{
		$buffer = '';
		$lnEnd  = $this->_doc->_getLineEnd();
		$tab    = $this->_doc->_getTab();

		if ($item instanceof WebAssetItemInterface)
		{
			$attribs = $item->getAttributes();
			$content = $item->getOption('content');
		}
		else
		{
			$attribs = $item;
			$content = $item['content'] ?? '';

			unset($attribs['content']);
		}

		// Do not produce empty elements
		if (!$content)
		{
			return '';
		}

		// Add "nonce" attribute if exist
		if ($this->_doc->cspNonce)
		{
			$attribs['nonce'] = $this->_doc->cspNonce;
		}

		$buffer .= $tab . '<script';
		$buffer .= $this->renderAttributes($attribs);
		$buffer .= '>' . $lnEnd;

		// This is for full XHTML support.
		if ($this->_doc->_mime !== 'text/html')
		{
			$buffer .= $tab . $tab . '//<![CDATA[' . $lnEnd;
		}

		$buffer .= $content . $lnEnd;

		// See above note
		if ($this->_doc->_mime !== 'text/html')
		{
			$buffer .= $tab . $tab . '//]]>' . $lnEnd;
		}

		$buffer .= $tab . '</script>' . $lnEnd;

		return $buffer;
	}

	/**
	 * Renders the element attributes
	 *
	 * @param   array  $attributes  The element attributes
	 *
	 * @return  string  The attributes string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function renderAttributes(array $attributes) : string
	{
		$buffer = '';

		$defaultJsMimes         = array('text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript');
		$html5NoValueAttributes = array('defer', 'async');

		foreach ($attributes as $attrib => $value)
		{
			// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
			if ($attrib === 'options' || $attrib === 'src')
			{
				continue;
			}

			// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
			if (\in_array($attrib, array('type', 'mime')) && $this->_doc->isHtml5() && \in_array($value, $defaultJsMimes))
			{
				continue;
			}

			// B/C: If defer and async is false or empty don't render the attribute.
			if (\in_array($attrib, array('defer', 'async')) && !$value)
			{
				continue;
			}

			// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
			if ($attrib === 'mime')
			{
				$attrib = 'type';
			}
			// B/C defer and async can be set to yes when using the old method.
			elseif (\in_array($attrib, array('defer', 'async')) && $value === true)
			{
				$value = $attrib;
			}

			// Add attribute to script tag output.
			$buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

			if (!($this->_doc->isHtml5() && \in_array($attrib, $html5NoValueAttributes)))
			{
				// Json encode value if it's an array.
				$value = !is_scalar($value) ? json_encode($value) : $value;

				$buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
			}
		}

		return $buffer;
	}
}
