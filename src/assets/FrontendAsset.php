<?php

namespace CaptchaEU\assets;

use craft\web\AssetBundle;
use craft\web\View;

class FrontendAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = '@CaptchaEU/resources';
        $this->js = ['captcha-eu.js'];
        $this->jsOptions = ['position' => View::POS_END];

        parent::init();
    }
}
