<?php

namespace CaptchaEU;

use Craft;
use craft\helpers\App;
use verbb\formie\base\Captcha;
use verbb\formie\elements\Form;


use verbb\formie\elements\Submission;

class FormieCaptchaEU extends Captcha
{
    public ?string $handle = 'FormieCaptchaEU';
    public ?string $restKey = null;
    public ?string $publicKey = null;
    public ?string $endPoint = null;

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
        return '
            <script>
             window.CPTOnAllPages = ' .  ($this->showAllPages ? "true" : "false") . ';
             window.CaptchaEUSettings = {
                    publicSecret: "' . App::parseEnv($this->publicKey) . '"
             }


             if(!window.KROT_FORMS) {
                window.KROT_FORMS = [];
                window.CPTWatcher = setInterval(function() {
                  if(window.KROT) {
                      clearInterval(window.CPTWatcher);
                      KROTLoader();
                  }
               }, 500);
             }
             if(typeof(KROTLoader) == "undefined") {
                 function KROTLoader() {
                      window.KROT_FORMS.forEach(function(f) {
                        
                          f.addEventListener("onFormieCaptchaValidate", function(e) {
                            if(e.srcElement.form.submitAction != "submit") {
                                return;
                            }
                            if(!window.CPTOnAllPages) {
                                // Check if we are multi page
                                if(e.srcElement.form.settings.hasMultiplePages) {
                                    var hasCaptchaOnPage = e.srcElement.formTheme.$currentPage.querySelector(".captcha_eu_via_formie");
                                    if(!hasCaptchaOnPage) {
                                        return;
                                    }
                                    
                                }
                            }
                            e.preventDefault();
                            var submitHandler = e.detail.submitHandler;
                            // Add a hidden field
                            var hiddenField = document.createElement("input");
                            hiddenField.type = "hidden";
                            hiddenField.className = "captcha_at_hidden_field";
                            hiddenField.name = "captcha_at_solution";
                            f.appendChild(hiddenField);              
                            KROT.getSolution()
                                .then(function(sol) {
                                    hiddenField.value=JSON.stringify(sol);
                                    submitHandler.submitForm();
                                })

                          });
                      });
                 }
             }
             
             (function() {
               const forms = document.querySelectorAll(\'input[type="hidden"][name="handle"][value="' . $form->handle . '"]\');
               if(!window.CPTFormsAdded) { window.CPTFormsAdded = new Set(); }

                forms.forEach(form => {
                    const closestForm = form.closest("form");
                    
                    if (closestForm && !window.CPTFormsAdded.has(closestForm.id)) {
                        console.log(closestForm.id);
                        window.KROT_FORMS.push(closestForm);
                        CPTFormsAdded.add(closestForm.id);
                    }
                });

             })();
            </script><div class="captcha_eu_via_formie"></div>';
    }

    public function getFrontEndJsVariables(Form $form, $page = null): ?array
    {
        return [
            'src' => App::parseEnv($this->endPoint) . "/" . "sdk.js",
        ];
    }

    public function validateSubmission(Submission $submission): bool
    {
        $svc = new Service(App::parseEnv($this->endPoint), App::parseEnv($this->restKey));
        $sol = $this->getRequestParam('captcha_at_solution');

        return $svc->validate($sol);
    }
}
