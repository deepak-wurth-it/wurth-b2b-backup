/**
 * Component implements overlay logic for layered navigation.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    function scrollToHeader() {
        var headerHeight = $('.page-header:first').height();

        $('html, body').animate({
            scrollTop: headerHeight
        }, 500, 'easeOutExpo');
    }

    const className = 'navigation-overlay';
    const $overlay = $('<div><i class="fa fa-spinner fa-spin"></i></div>').addClass(className);

    $('.columns').append($overlay);

    return {
        show: function () {
            $overlay.show();

            setTimeout(function () {
                $overlay.addClass('_show');
            }, 10);
        },

        hide: function () {
            $overlay.hide().removeClass('_show');
        }
    };
});
