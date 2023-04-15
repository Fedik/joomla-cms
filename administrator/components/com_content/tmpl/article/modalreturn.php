<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Utilities\ArrayHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('content-select');

$icon     = 'icon-copy';
$title    = $this->item ? $this->item->title : '';
$content  = $this->item ? $this->item->alias : '';
$dataAttr = 'data-content-type="article" ';

if ($this->item) {
    $link      = RouteHelper::getArticleRoute($this->item->id, $this->item->catid, $this->item->language);
    $dataAttr .= ArrayHelper::toString([
        'data-id'     => $this->item->id,
        'data-title'  => $this->escape($this->item->title),
        'data-cat-id' => $this->item->catid,
        'data-uri'    => $this->escape($link),
    ]);
}

?>

<div class="px-4 py-5 my-5 text-center">
    <span class="fa-8x mb-4 <?php echo $icon; ?>" aria-hidden="true"></span>
    <h1 class="display-5 fw-bold"><?php echo $title; ?></h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">
            <?php echo $content; ?>
        </p>
    </div>

    <!-- The data is used by Content select script -->
    <div data-content-select-on-load <?php echo $dataAttr; ?>></div>
</div>
