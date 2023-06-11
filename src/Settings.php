<?php

namespace CaptchaEU;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

class Settings extends Model
{
    /**
     * @var string
     */
    public string $publicKey = '';

    /**
     * @var string
     */
    public string $restKey = '';

    /**
     * Validate ContactForm
     *
     * @var bool
     */
    public bool $validateContactForm = false;

    /**
     * Validate UsersRegistration
     *
     * @var bool
     */
    public bool $validateUsersRegistration = false;

    
    /**
     * @var string
     */
    public string $endpoint = 'https://www.captcha.eu';

    
    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return App::parseEnv($this->publicKey);
    }

    /**
     * @return string
     */
    public function getRestKey(): string
    {
        return App::parseEnv($this->restKey);
    }

    /**
     * @return ?string
     */
    public function getEndpoint(): ?string
    {
        return App::parseEnv($this->endpoint);
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
        ];
    }
}
