<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Captcha;

use BadMethodCallException;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Captcha setup event
 *
 * @since   __DEPLOY_VERSION__
 */
class CaptchaSetupEvent  extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('subject', $arguments)) {
            throw new BadMethodCallException("Argument 'subject' of event {$this->name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the subject argument
     *
     * @param   Captcha  $value  The value to set
     *
     * @return  Captcha
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject($value)
    {
        if (!$value || !($value instanceof Captcha)) {
            throw new BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
        }

        return $value;
    }
}
