<?php

namespace CaptchaEU;

use Craft;

use craft\base\Component;
use craft\helpers\Html;
use Exception;
use Twig\Markup;
use yii\base\InvalidConfigException;

class ValidateService extends Component
{
    /**
     * @return bool
     * @throws Exception
     */
    public function validateRequest(): bool
    {
        $solution = Craft::$app->getRequest()->getParam('captcha_at_solution');
        
        $publicKey = $this->getPublicKey();
        $restKey = $this->getRestKey();
        $endpoint = $this->getEndpoint();
        return $this->validateSolution($solution, $publicKey, $restKey, $endpoint);
    }

    /**
     *
     * @param string $solution
     * @param string $publicKey
     * @param string $restKey
     * @param string $endpoint
     * @return bool
     * @throws Exception
     */
    public function validateSolution(?string $solution, string $publicKey, string $restKey, string $endpoint = 'global'): bool
    {
        $svc = new Service($endpoint, $restKey);
        $r = $svc->validate($solution);
   
        return $r;
    }

    protected function getRestKey(): string
    {
        return Plugin::$plugin->getSettings()->getRestKey();
    }

    protected function getPublicKey(): string
    {
        return Plugin::$plugin->getSettings()->getPublicKey();
    }

    public function getEndpoint(): string
    {
        return Plugin::$plugin->getSettings()->getEndpoint();
    }

    /**
     *
     * @param array $attributes html attributes to put on the widget
     * @return Markup
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     */
    public function intercept(array $attributes = []): String
    {
        $settings = Plugin::$plugin->getSettings();

        Craft::$app->view->registerJsFile($settings->getEndpoint() . "/sdk.js", ['async' => true, 'defer' => true]);
        
        return "";
    }
}
