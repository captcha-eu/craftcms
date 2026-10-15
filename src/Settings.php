<?php

namespace CaptchaEU;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

class Settings extends Model
{
    public const MODE_INVISIBLE = 'invisible';
    public const MODE_WIDGET = 'widget';

    public string $publicKey = '';
    public string $restKey = '';
    public bool $validateContactForm = false;
    public bool $validateUsersRegistration = false;
    public string $endpoint = 'https://www.captcha.eu';

    public string $mode = self::MODE_INVISIBLE;
    public string $theme = 'light';

    public function getPublicKey(): string
    {
        return App::parseEnv($this->publicKey);
    }

    public function getRestKey(): string
    {
        return App::parseEnv($this->restKey);
    }

    public function getEndpoint(): ?string
    {
        return App::parseEnv($this->endpoint);
    }

    public function getMode(): string
    {
        return $this->mode ?: self::MODE_INVISIBLE;
    }

    public function isWidgetMode(): bool
    {
        return $this->getMode() === self::MODE_WIDGET;
    }

    public function getTheme(): string
    {
        return $this->theme ?: 'light';
    }

    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['publicKey', 'restKey', 'endpoint'],
            ],
        ];
    }

    public function rules(): array
    {
        return [
            ['publicKey', 'string'],
            ['restKey', 'string'],
            ['endpoint', 'string'],
            [['publicKey', 'restKey', 'endpoint'], 'required'],
            ['validateContactForm', 'boolean'],
            ['validateUsersRegistration', 'boolean'],
            ['mode', 'in', 'range' => [self::MODE_INVISIBLE, self::MODE_WIDGET]],
            ['theme', 'in', 'range' => ['light', 'dark', 'auto']],
        ];
    }
}