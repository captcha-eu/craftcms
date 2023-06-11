<?php

namespace CaptchaEU;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\contactform\models\Submission;
use craft\elements\User;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use verbb\formie\events\RegisterIntegrationsEvent;

use verbb\formie\services\Integrations;
use yii\base\Event;
use yii\base\ModelEvent;

/**
 * captcha-eu plugin
 *
 * @method static Plugin getInstance()
 * @author captcha-eu <hello@captcha.eu>
 * @property  ValidateService $validate
 * @copyright captcha-eu
 * @license MIT
 */
class Plugin extends BasePlugin
{
    public static $plugin;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public ?ValidateService $validate;
    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        Craft::setAlias('@CaptchaEU', __DIR__ . "/../");
        parent::init();
        self::$plugin = $this;
        $this->validate = new ValidateService();
        Event::on(
           View::class,
           View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
           function(RegisterTemplateRootsEvent $event) {
               $event->roots['_captcha_eu_cp'] = __DIR__ . '/../templates';
           }
          );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('captchaEU', Variables::class);
            }
        );
        // Handle form submissions of the craftcms/contact-form plugin
        if (class_exists(Submission::class) && $this->settings->validateContactForm) {
            Event::on(Submission::class, Submission::EVENT_BEFORE_VALIDATE, function(ModelEvent $event) {
                $submission = $event->sender;
                if (!$this->validate->validateRequest()) {
                    $submission->addError('captchaEU', "Captcha.eu failed");
                    $event->isValid = false;
                }
            });
        }
        
        // Handle user registration forms
        if ($this->settings->validateUsersRegistration && Craft::$app->getRequest()->getIsSiteRequest()) {
            Event::on(User::class, User::EVENT_BEFORE_VALIDATE, function(ModelEvent $event) {
                /** @var User $user */
                $user = $event->sender;

                // Only new users
                if ($user->id === null && $user->uid === null && $user->contentId === null) {
                    if (!$this->validate->validateRequest()) {
                        $user->addError('captchaEU', "Captcha.eu failed");
                        $event->isValid = false;
                    }
                }
            });
        }
        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        // Register event handlers here ...
        Event::on(Integrations::class, Integrations::EVENT_REGISTER_INTEGRATIONS, function(RegisterIntegrationsEvent $event) {
            $event->captchas[] = FormieCaptchaEU::class;
            // ...
        });
    }
    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            '_captcha_eu_cp/_plugin-settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}
