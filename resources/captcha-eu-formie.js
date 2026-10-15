(function () {
    'use strict';

    if (window.CaptchaEUFormie) {
        window.CaptchaEUFormie.init();
        return;
    }

    var SOLUTION_FIELD = 'input[name="captcha_at_solution"]';
    var SDK_FIELD = 'input[name="captcha_at_hidden_field"]';
    var WIDGET_SUBMIT_BUTTONS = '[type=submit]:not(.fui-next):not([data-submit-action="back"])';

    // wait until the SDK is available
    function whenKROTReady(callback) {
        var timer = setInterval(function () {
            if (window.KROT !== undefined) {
                clearInterval(timer);
                callback();
            }
        }, 200);
    }

    // the hidden field the server-side validator reads
    function getSolutionField(form) {
        var field = form.querySelector(SOLUTION_FIELD);

        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.className = 'captcha_at_hidden_field';
            field.name = 'captcha_at_solution';
            form.appendChild(field);
        }

        return field;
    }

    function bindInvisible(form, onAllPages) {
        form.addEventListener('onFormieCaptchaValidate', function (event) {
            var formie = event.target.form;

            if (!formie || formie.submitAction !== 'submit') {
                return;
            }

            // multi-page forms
            if (!onAllPages && formie.settings.hasMultiplePages) {
                var onThisPage = event.target.formTheme.$currentPage.querySelector('.captcha_eu_via_formie');
                if (!onThisPage) {
                    return;
                }
            }

            event.preventDefault();

            var submitHandler = event.detail.submitHandler;
            var field = getSolutionField(form);

            KROT.getSolution().then(function (solution) {
                field.value = JSON.stringify(solution);
                submitHandler.submitForm();
            });
        });
    }

    function bindWidget(form) {
        var buttons = form.querySelectorAll(WIDGET_SUBMIT_BUTTONS);
        buttons.forEach(function (button) { button.disabled = true; });

        var field = getSolutionField(form);
        var poll = setInterval(function () {
            var sdkField = document.querySelector(SDK_FIELD);

            if (sdkField && sdkField.value !== '') {
                field.value = sdkField.value;
                buttons.forEach(function (button) { button.disabled = false; });
                clearInterval(poll);
            }
        }, 200);
    }

    function init() {
        var markers = document.querySelectorAll('[data-captchaeu-formie]');

        if (!markers.length) {
            return;
        }

        window.CaptchaEUSettings = { publicSecret: markers[0].getAttribute('data-key') };

        whenKROTReady(function () {
            var pending = [];

            markers.forEach(function (marker) {
                var form = marker.closest('form');

                if (form && !form.dataset.captchaeuFormieBound) {
                    pending.push({ marker: marker, form: form });
                }
            });

            var hasWidget = pending.some(function (item) {
                return item.marker.getAttribute('data-captchaeu-formie') === 'widget';
            });

            if (hasWidget) {
                KROT.init();
            }

            pending.forEach(function (item) {
                if (item.form.dataset.captchaeuFormieBound) {
                    return;
                }
                item.form.dataset.captchaeuFormieBound = '1';

                if (item.marker.getAttribute('data-captchaeu-formie') === 'widget') {
                    bindWidget(item.form);
                } else {
                    bindInvisible(item.form, item.marker.getAttribute('data-all-pages') === '1');
                }
            });
        });
    }

    window.CaptchaEUFormie = { init: init };
    init();
})();