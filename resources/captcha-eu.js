(function () {
    'use strict';

    var SOLUTION_FIELD = 'input[name="captcha_at_solution"]';
    var SDK_FIELD = 'input[name="captcha_at_hidden_field"]';
    var SUBMIT_BUTTONS = 'button:not([type="button"]):not([type="reset"]), input[type="submit"]';

    // wait until the SDK is available
    function whenKROTReady(callback) {
        var timer = setInterval(function () {
            if (window.KROT !== undefined) {
                clearInterval(timer);
                callback();
            }
        }, 200);
    }

    // widget mode
    function initWidget(form, target) {
        var buttons = form.querySelectorAll(SUBMIT_BUTTONS);
        buttons.forEach(function (button) { button.disabled = true; });

        var poll = setInterval(function () {
            var sdkField = form.querySelector(SDK_FIELD) || document.querySelector(SDK_FIELD);
            if (sdkField && sdkField.value !== '') {
                target.value = sdkField.value;
                buttons.forEach(function (button) { button.disabled = false; });
                clearInterval(poll);
            }
        }, 200);
    }

    // invisible mode
    function initInvisible(form, target) {
        var busy = false;

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (busy) {
                return;
            }
            busy = true;

            KROT.getSolution()
                .then(function (solution) {
                    target.value = JSON.stringify(solution);
                    
                    HTMLFormElement.prototype.submit.call(form);
                })
                .catch(function () {
                    busy = false;
                });
        });
    }

    function init() {
        var markers = document.querySelectorAll('[data-captchaeu]');
        if (!markers.length) {
            return;
        }

        whenKROTReady(function () {
            var first = markers[0];

            KROT.setup(first.getAttribute('data-key'));
            if (first.getAttribute('data-host')) {
                KROT.KROT_HOST = first.getAttribute('data-host');
            }
            if (document.querySelector('[data-captchaeu="widget"]')) {
                KROT.init();
            }

            markers.forEach(function (marker) {
                var form = marker.closest('form');
                var target = form && form.querySelector(SOLUTION_FIELD);

                if (!form || !target || form.dataset.captchaeuBound) {
                    return;
                }
                form.dataset.captchaeuBound = '1';

                if (marker.getAttribute('data-captchaeu') === 'widget') {
                    initWidget(form, target);
                } else {
                    initInvisible(form, target);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
