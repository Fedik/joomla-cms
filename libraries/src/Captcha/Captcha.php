<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @since  2.5
 */
class Captcha implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Captcha Plugin object
     *
     * @var    CMSPlugin|CaptchaItemInterface
     * @since  2.5
     */
    private $captcha;

    /**
     * Editor Plugin name
     *
     * @var    string
     * @since  2.5
     */
    private $name;

    /**
     * Array of instances of this class.
     *
     * @var    Captcha[]
     * @since  2.5
     */
    private static $instances = [];

    /**
     * List of registered Captcha
     *
     * @var    array
     * @since   __DEPLOY_VERSION__
     */
    private static $registry = [];

    /**
     * Class constructor.
     *
     * @param   string  $captcha  The plugin to use.
     * @param   array   $options  Associative array of options.
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function __construct($captcha, $options)
    {
        $this->name = $captcha;

        if (!empty($options['dispatcher']) && $options['dispatcher'] instanceof DispatcherInterface) {
            $this->setDispatcher($options['dispatcher']);
        } else {
            $this->setDispatcher(Factory::getApplication()->getDispatcher());
        }

        // Get registered items
        $items = $this->getRegistry();

        if (!empty($items[$this->name])) {
            $this->captcha = $items[$this->name];
        } else {
            // Try to load a legacy one
            $this->_load($options);
        }
    }

    /**
     * Returns the global Captcha object, only creating it
     * if it doesn't already exist.
     *
     * @param   string  $captcha  The plugin to use.
     * @param   array   $options  Associative array of options.
     *
     * @return  Captcha|null  Instance of this class.
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public static function getInstance($captcha, array $options = array())
    {
        $signature = md5(serialize(array($captcha, $options)));

        if (empty(self::$instances[$signature])) {
            self::$instances[$signature] = new Captcha($captcha, $options);
        }

        return self::$instances[$signature];
    }

    /**
     * Return list of registered Captcha
     *
     * @return array
     * @since   __DEPLOY_VERSION__
     */
    public function getRegistry() : array
    {
        // Initial setup of captcha(s)
        if (empty(self::$registry['initialised'])) {
            PluginHelper::importPlugin('captcha');

            $event = new CaptchaSetupEvent('onCaptchaSetup', ['subject' => $this]);
            $this->getDispatcher()->dispatch($event->getName(), $event);

            self::$registry['initialised'] = true;
        }

        return self::$registry;
    }

    /**
     * Register Captcha element
     *
     * @param CaptchaItemInterface $captcha
     *
     * @return void
     * @since   __DEPLOY_VERSION__
     */
    public function registerCaptcha(CaptchaItemInterface $captcha)
    {
        self::$registry[$captcha->getName()] = $captcha;
    }

    /**
     * Fire the onInit event to initialise the captcha plugin.
     *
     * @param   string  $id  The id of the field.
     *
     * @return  boolean  True on success
     *
     * @since   2.5
     * @throws  \RuntimeException
     * @deprecated  Without replacement
     */
    public function initialise($id)
    {
        $arg = ['id' => $id];

        $this->update('onInit', $arg);

        return true;
    }

    /**
     * Get the HTML for the captcha.
     *
     * @param   string  $name   The control name.
     * @param   string  $id     The id for the control.
     * @param   string  $class  Value for the HTML class attribute
     *
     * @return  string  The return value of the function "onDisplay" of the selected Plugin.
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function display($name, $id, $class = '')
    {
        // Check if captcha is already loaded.
        if ($this->captcha === null) {
            return '';
        }

        if ($this->captcha instanceof CaptchaItemInterface) {
            return $this->captcha->display($name, [
                'id'    => $id ?: $name,
                'class' => $class,
            ]);
        }

        // Initialise the Captcha.
        if (!$this->initialise($id)) {
            return '';
        }

        $arg = [
            'name'  => $name,
            'id'    => $id ?: $name,
            'class' => $class,
        ];

        $result = $this->update('onDisplay', $arg);

        return $result;
    }

    /**
     * Checks if the answer is correct.
     *
     * @param   string  $code  The answer.
     *
     * @return  bool    Whether the provided answer was correct
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function checkAnswer($code)
    {
        // Check if captcha is already loaded
        if ($this->captcha === null) {
            return false;
        }

        if ($this->captcha instanceof CaptchaItemInterface) {
            return $this->captcha->checkAnswer($code);
        }

        $arg = ['code'  => $code];

        $result = $this->update('onCheckAnswer', $arg);

        return $result;
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   \Joomla\CMS\Form\Field\CaptchaField  $field    Captcha field instance
     * @param   \SimpleXMLElement                    $element  XML form definition
     *
     * @return void
     */
    public function setupField(\Joomla\CMS\Form\Field\CaptchaField $field, \SimpleXMLElement $element)
    {
        if ($this->captcha === null) {
            return;
        }

        if ($this->captcha instanceof CaptchaItemInterface) {
            $this->captcha->setupField($field, $element);
            return;
        }

        $arg = [
            'field' => $field,
            'element' => $element,
        ];

        $result = $this->update('onSetupField', $arg);

        return $result;
    }

    /**
     * Method to call the captcha callback if it exist.
     *
     * @param   string  $name   Callback name
     * @param   array   &$args  Arguments
     *
     * @return  mixed
     *
     * @since   4.0.0
     */
    private function update($name, &$args)
    {
        if (method_exists($this->captcha, $name)) {
            return call_user_func_array(array($this->captcha, $name), array_values($args));
        }

        return null;
    }

    /**
     * Load the Captcha plugin.
     *
     * @param   array  $options  Associative array of options.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    private function _load(array $options = array())
    {
        // Build the path to the needed captcha plugin
        $name = InputFilter::getInstance()->clean($this->name, 'cmd');
        $path = JPATH_PLUGINS . '/captcha/' . $name . '/' . $name . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException(Text::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
        }

        // Require plugin file
        require_once $path;

        // Get the plugin
        $plugin = PluginHelper::getPlugin('captcha', $this->name);

        if (!$plugin) {
            throw new \RuntimeException(Text::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
        }

        // Check for already loaded params
        if (!($plugin->params instanceof Registry)) {
            $params = new Registry($plugin->params);
            $plugin->params = $params;
        }

        // Build captcha plugin classname
        $name = 'PlgCaptcha' . $this->name;
        $dispatcher     = $this->getDispatcher();
        $this->captcha = new $name($dispatcher, (array) $plugin, $options);
    }
}
