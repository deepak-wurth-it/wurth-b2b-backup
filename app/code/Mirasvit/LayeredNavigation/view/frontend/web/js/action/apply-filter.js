define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/action/apply-filter-noajax-mode',
    'Mirasvit_LayeredNavigation/js/action/apply-filter-confirmation-mode',
    'Mirasvit_LayeredNavigation/js/action/apply-filter-instant-mode'
], function ($, config, noAjaxMode, confirmationMode, instantMode) {
    "use strict";

    return {
        apply: function (url, $initiator = null, force = false) {
            url = this.fixUrl(url);

            if (!$initiator) {
                force = true;
            } else {
                const mode = $initiator.attr('data-mode');

                if (mode === 'instant') {
                    force = true;
                }
            }

            if (!config.isAjax()) {
                noAjaxMode(url);
            } else if (config.isConfirmationMode() && force === false) {//@todo
                confirmationMode(url, $initiator);
            } else {
                instantMode(url);
            }
        },

        applyForced: function (url) {
            this.apply(url, null, true);
        },

        fixUrl: function (url) {
            url = url.replace('&amp;', '&');
            url = url.replace('%2C', ',');

            return url;
        }
    }
});
