<?php

namespace CaptchaEU;

use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 *
 * @author    Captcha.eu
 * @package   CaptchaEU
 * @since     1.0.0
 *
 * @property  ValidateService $validate
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Variables
{
    /**
     * {{ craft.captchaEU.publicKey }}
     *
     * @return string
     */
    public function publicKey(): string
    {
        return Plugin::$plugin->getSettings()->getSiteKey();
    }

    /**
     * {{ craft.captchaEU.validateRequest }}
     *
     * @return bool
     * @throws \Exception
     */
    public function validateRequest(): bool
    {
        return Plugin::$plugin->validate->validateRequest();
    }

    /**
     * {{ craft.captchaEU.intercept() }}
     *
     * @param array $attributes
     * @return Markup
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function intercept(array $attributes = []): String
    {
        return Plugin::$plugin->validate->intercept($attributes);
    }
}
