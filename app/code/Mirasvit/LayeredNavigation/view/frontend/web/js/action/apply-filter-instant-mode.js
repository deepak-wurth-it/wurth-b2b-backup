define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config'
], function ($, config) {
    'use strict';

    /**
     * Method triggers the event listen by navigation which requests
     * new data (based on applied filters) and reloads a page content.
     */
    return function (url, force) {
        $(document).trigger(config.getAjaxCallEvent(), [url, force]);
    };
});
