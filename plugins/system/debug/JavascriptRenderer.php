<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer as DebugBarJavascriptRenderer;

/**
 * Custom JavascriptRenderer for DebugBar
 *
 * @since  __DEPLOY_VERSION__
 */
class JavascriptRenderer extends DebugBarJavascriptRenderer
{
	/**
	 * Class constructor.
	 *
	 * @param \DebugBar\DebugBar $debugBar
	 * @param string $baseUrl
	 * @param string $basePath
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(DebugBar $debugBar, $baseUrl = null, $basePath = null)
	{
		parent::__construct($debugBar, $baseUrl, $basePath);

		// Disable features that loaded by Joomla! API
		$this->setEnableJqueryNoConflict(false);
		$this->disableVendor('jquery');
		$this->disableVendor('fontawesome');
	}

	/**
	 * Renders the html to include needed assets
	 *
	 * Only useful if Assetic is not used
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function renderHead()
	{
		list($cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead) = $this->getAssets(null, self::RELATIVE_URL);
		$html = '';

		foreach ($cssFiles as $file)
		{
			$html .= sprintf('<link rel="stylesheet" type="text/css" href="%s">' . "\n", $file);
		}

		foreach ($inlineCss as $content)
		{
			$html .= sprintf('<style type="text/css">%s</style>' . "\n", $content);
		}

		foreach ($jsFiles as $file)
		{
			$html .= sprintf('<script type="text/javascript" src="%s" defer></script>' . "\n", $file);
		}

		foreach ($inlineJs as $content)
		{
			$html .= sprintf('<script type="module">%s</script>' . "\n", $content);
		}

		foreach ($inlineHead as $content)
		{
			$html .= $content . "\n";
		}

		return $html;
	}

	/**
	 * Returns the code needed to display the debug bar
	 *
	 * AJAX request should not render the initialization code.
	 *
	 * @param boolean $initialize Whether or not to render the debug bar initialization code
	 * @param boolean $renderStackedData Whether or not to render the stacked data
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function render($initialize = true, $renderStackedData = true)
	{
		$js = '';

		if ($initialize)
		{
			$js = $this->getJsInitializationCode();
		}

		if ($renderStackedData && $this->debugBar->hasStackedData())
		{
			foreach ($this->debugBar->getStackedData() as $id => $data)
			{
				$js .= $this->getAddDatasetCode($id, $data, '(stacked)');
			}
		}

		$suffix = !$initialize ? '(ajax)' : null;
		$js .= $this->getAddDatasetCode($this->debugBar->getCurrentRequestId(), $this->debugBar->getData(), $suffix);

		if ($this->useRequireJs)
		{
			return "<script type=\"module\">\nrequire(['debugbar'], function(PhpDebugBar){ $js });\n</script>\n";
		}
		else
		{
			return "<script type=\"module\">\n$js\n</script>\n";
		}
	}
}
