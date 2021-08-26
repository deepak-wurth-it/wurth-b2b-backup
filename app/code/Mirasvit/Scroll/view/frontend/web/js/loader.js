define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Component implements overlay logic for layered navigation.
     */
    return {
        loaderClass: 'mst-scroll__loader',
        template:    '<div>' +
                         '<span class="loader-dot"></span>' +
                         '<span class="loader-dot"></span>' +
                         '<span class="loader-dot"></span>' +
                         '<span class="loader-dot"></span>' +
                         '</div>',

        show: function (target) {
            target = target instanceof $ ? target : $(target) || '.columns';

            $(this.template).addClass(this.loaderClass).insertAfter(target);
        },

        hide: function () {
            $('.' + this.loaderClass).remove();
        }
    };
});
