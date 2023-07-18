<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var array       $displayData
 * @var \Throwable  $error
 * @var boolean     $debug
 * @var array       $backtrace
 * @var array       $context
 */
extract($displayData);

?>
<div class="card m-1">
    <div class="card-body">
        <div class="p-2">
            <p><strong><?php echo Text::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></strong></p>
            <blockquote>
                <span class="badge bg-secondary"><?php echo $error->getCode(); ?></span> <?php echo $this->escape($error->getMessage()); ?>
            </blockquote>
            <?php if ($debug): ?>
                <div class="overflow-auto">
                    <?php echo LayoutHelper::render('joomla.error.backtrace', ['backtrace' => $backtrace]); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
