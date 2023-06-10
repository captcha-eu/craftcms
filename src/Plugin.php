<?php

namespace captchaeu\craftcaptchaeu;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use verbb\formie\events\RegisterIntegrationsEvent;
use verbb\formie\services\Integrations;
use yii\base\Event;

/**
 * captcha-eu plugin
 *
 * @method static Plugin getInstance()
 * @author captcha-eu <hello@captcha.eu>
 * @copyright captcha-eu
 * @license MIT
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

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

        Event::on(
           View::class,
           View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
           function(RegisterTemplateRootsEvent $event) {
               $event->roots['_captcha_eu_cp'] = __DIR__ . '/../templates';
           }
          );


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
}
