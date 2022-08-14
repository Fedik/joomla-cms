<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Captcha\CaptchaItemInterface;
use Joomla\CMS\Captcha\Google\HttpBridgePostRequestMethod;
use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\IpHelper;

/**
 * Invisible reCAPTCHA Plugin.
 *
 * @since  3.9.0
 */
class PlgCaptchaRecaptcha_Invisible extends CMSPlugin implements SubscriberInterface, CaptchaItemInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onCaptchaSetup' => 'onCaptchaSetup',
            'onPrivacyCollectAdminCapabilities' => 'onPrivacyCollectAdminCapabilities',
        ];
    }

    /**
     * Register Captcha instance
     *
     * @param CaptchaSetupEvent $event
     *
     * @return void
     * @since   __DEPLOY_VERSION__
     */
    public function onCaptchaSetup(CaptchaSetupEvent $event)
    {
        /** @var Captcha $subject */
        $subject = $event['subject'];
        $subject->registerCaptcha($this);
    }

    /**
     * Return Captcha name
     *
     * @return string
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return 'recaptcha_invisible';
    }

    /**
     * Reports the privacy related capabilities for this plugin to site administrators.
     *
     * @return  array
     *
     * @since   3.9.0
     */
    public function onPrivacyCollectAdminCapabilities()
    {
        $this->loadLanguage();

        return array(
            Text::_('PLG_CAPTCHA_RECAPTCHA_INVISIBLE') => array(
                Text::_('PLG_RECAPTCHA_INVISIBLE_PRIVACY_CAPABILITY_IP_ADDRESS'),
            ),
        );
    }

    /**
     * Load captcha assets
     *
     * @return void
     * @since   __DEPLOY_VERSION__
     */
    private function loadAssets()
    {
        $pubkey = $this->params->get('public_key', '');

        if ($pubkey === '') {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PUBLIC_KEY'));
        }

        $apiSrc = 'https://www.google.com/recaptcha/api.js?onload=JoomlainitReCaptchaInvisible&render=explicit&hl='
            . $this->app->getLanguage()->getTag();

        // Load assets, the callback should be first
        $this->app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_captcha_recaptchainvisible', 'plg_captcha_recaptcha_invisible/recaptcha.min.js', [], ['defer' => true])
            ->registerAndUseScript('plg_captcha_recaptchainvisible.api', $apiSrc, [], ['defer' => true], ['plg_captcha_recaptchainvisible'])
            ->registerAndUseStyle('plg_captcha_recaptchainvisible', 'plg_captcha_recaptcha_invisible/recaptcha_invisible.css');
    }

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name        Input name
     * @param   array   $attributes  The class of the field
     *
     * @return  string  The HTML to be embedded in the form
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function display(string $name = '', array $attributes = []): string
    {
        $this->loadAssets();

        $id    = $attributes['id'] ?? '';
        $class = $attributes['class'] ?? '';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ele = $dom->createElement('div');
        $ele->setAttribute('id', $id);
        $ele->setAttribute('class', (!$class ? 'g-recaptcha' : ($class . ' g-recaptcha')));
        $ele->setAttribute('data-sitekey', $this->params->get('public_key', ''));
        $ele->setAttribute('data-badge', $this->params->get('badge', 'bottomright'));
        $ele->setAttribute('data-size', 'invisible');
        $ele->setAttribute('data-tabindex', $this->params->get('tabindex', '0'));
        $ele->setAttribute('data-callback', $this->params->get('callback', ''));
        $ele->setAttribute('data-expired-callback', $this->params->get('expired_callback', ''));
        $ele->setAttribute('data-error-callback', $this->params->get('error_callback', ''));
        $dom->appendChild($ele);

        return $dom->saveHTML($ele);
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct.
     *
     * @param   string  $code  Answer provided by user
     *
     * @return  bool    If the answer is correct, false otherwise
     *
     * @since   __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function checkAnswer(string $code = null): bool
    {
        $input      = Factory::getApplication()->input;
        $privatekey = $this->params->get('private_key');
        $remoteip   = IpHelper::getIp();

        $response  = $code ?: $input->get('g-recaptcha-response', '', 'string');

        // Check for Private Key
        if (empty($privatekey)) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_PRIVATE_KEY'));
        }

        // Check for IP
        if (empty($remoteip)) {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_NO_IP'));
        }

        // Discard spam submissions
        if (trim($response) == '') {
            throw new \RuntimeException(Text::_('PLG_RECAPTCHA_INVISIBLE_ERROR_EMPTY_SOLUTION'));
        }

        return $this->getResponse($privatekey, $remoteip, $response);
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   FormField         $field    Captcha field instance
     * @param   SimpleXMLElement  $element  XML form definition
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     *
     * @throws  \RuntimeException
     */
    public function setupField(FormField $field, SimpleXMLElement $element): void
    {
        // Hide the label for the invisible recaptcha type
        $element['hiddenLabel'] = 'true';
    }


    /**
     * Get the reCaptcha response.
     *
     * @param   string  $privatekey  The private key for authentication.
     * @param   string  $remoteip    The remote IP of the visitor.
     * @param   string  $response    The response received from Google.
     *
     * @return  boolean  True if response is good | False if response is bad.
     *
     * @since   3.9.0
     * @throws  \RuntimeException
     */
    private function getResponse($privatekey, $remoteip, $response)
    {
        $reCaptcha = new \ReCaptcha\ReCaptcha($privatekey, new HttpBridgePostRequestMethod());
        $response = $reCaptcha->verify($response, $remoteip);

        if (!$response->isSuccess()) {
            foreach ($response->getErrorCodes() as $error) {
                throw new \RuntimeException($error);
            }

            return false;
        }

        return true;
    }

    /**
     * Initialise the captcha
     *
     * @param   string  $id  The id of the field.
     *
     * @return  boolean True on success, false otherwise
     *
     * @since   3.9.0
     * @throws  \RuntimeException
     * @deprecated
     */
    public function onInit($id = 'dynamic_recaptcha_invisible_1')
    {
        $this->loadAssets();

        return true;
    }

    /**
     * Gets the challenge HTML
     *
     * @param   string  $name   The name of the field. Not Used.
     * @param   string  $id     The id of the field.
     * @param   string  $class  The class of the field.
     *
     * @return  string  The HTML to be embedded in the form.
     *
     * @since  3.9.0
     * @deprecated
     */
    public function onDisplay($name = null, $id = 'dynamic_recaptcha_invisible_1', $class = '')
    {
        return $this->display($name, [
            'id'    => $id,
            'class' => $class,
        ]);
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct
     *
     * @param   string  $code  Answer provided by user. Not needed for the Recaptcha implementation
     *
     * @return  boolean  True if the answer is correct, false otherwise
     *
     * @since   3.9.0
     * @throws  \RuntimeException
     * @deprecated
     */
    public function onCheckAnswer($code = null)
    {
        return $this->checkAnswer($code);
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   \Joomla\CMS\Form\Field\CaptchaField  $field    Captcha field instance
     * @param   \SimpleXMLElement                    $element  XML form definition
     *
     * @return void
     *
     * @since 3.9.0
     * @deprecated
     */
    public function onSetupField(\Joomla\CMS\Form\Field\CaptchaField $field, \SimpleXMLElement $element)
    {
        $this->setupField($field, $element);
    }
}
