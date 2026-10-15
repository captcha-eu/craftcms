<?php

namespace CaptchaEU;

use Craft;
use craft\helpers\App;
use craft\helpers\Html;
use verbb\formie\base\Captcha;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;

$isCraft5 = version_compare(Craft::$app->getVersion(), '5.0.0', '>=');

if ($isCraft5 && class_exists('verbb\formie\models\FieldLayoutPage')) {
    class_alias('verbb\formie\models\FieldLayoutPage', 'CaptchaEU\FieldLayoutPageProxy');
} else {
    class FieldLayoutPageProxy {}
}

class FormieCaptchaEU extends Captcha
{
    public ?string $handle = 'FormieCaptchaEU';
    public ?string $restKey = null;
    public ?string $publicKey = null;
    public ?string $endPoint = null;
    public ?string $mode = 'invisible';
    public ?string $theme = 'light';

    public function getRefreshJsVariables(Form $form, $page = null): array
    {
        return [];
    }

    public function getName(): string
    {
        return Craft::t('formie', 'captcha.eu');
    }

    public function getIconUrl(): string
    {
        return Craft::$app->getAssetManager()->getPublishedUrl("@CaptchaEU/resources/icon.svg", true);
    }

    public function getDescription(): string
    {
        return Craft::t('formie', 'Captcha.eu Protects you from Bots and Spam without the need of any user input!');
    }

    public function getSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('_captcha_eu_cp/_formie-plugin-settings', [
            'integration' => $this,
        ]);
    }

    public function getFrontEndHtml(Form $form, $page = null): string
    {
        $publicKey = App::parseEnv($this->publicKey);

        if ($this->mode === 'widget') {
            $widget = Html::tag('div', '', [
                'class' => 'cpt_widget',
                'data-key' => $publicKey,
                'data-theme' => $this->theme ?: 'light',
            ]);

            $marker = Html::tag('div', $widget, [
                'class' => 'captcha_eu_via_formie_wrap',
                'data-captchaeu-formie' => 'widget',
                'data-key' => $publicKey,
            ]);
        } else {
            $marker = Html::tag('div', '', [
                'class' => 'captcha_eu_via_formie',
                'data-captchaeu-formie' => 'invisible',
                'data-key' => $publicKey,
                'data-all-pages' => $this->showAllPages ? '1' : '0',
            ]);
        }

        $script = Html::tag('script', '', ['src' => $this->getScriptUrl()]);

        return $marker . $script;
    }

    private function getScriptUrl(): string
    {
        return (string)Craft::$app->getAssetManager()->getPublishedUrl('@CaptchaEU/resources/captcha-eu-formie.js', true);
    }

    /**
     * Get JS variables for the front-end
     */
    public function getFrontEndJsVariables(Form $form, $page = null): ?array
    {
        return [
            'src' => App::parseEnv($this->endPoint) . "/" . "sdk.js",
        ];
    }

    public function validateSubmission(Submission $submission): bool
    {
        $isCraft5 = version_compare(Craft::$app->getVersion(), '5.0.0', '>=');
        $svc = new Service(App::parseEnv($this->endPoint), App::parseEnv($this->restKey));
        if($isCraft5) {
            $sol = $this->getCaptchaValue($submission,'captcha_at_solution');
        } else {
            $sol = Craft::$app->getRequest()->getBodyParam('captcha_at_solution');
        }

        return $svc->validate($sol);
    }
}