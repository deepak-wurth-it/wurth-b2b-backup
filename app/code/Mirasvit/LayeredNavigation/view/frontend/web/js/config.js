define([
    'jquery'
], function ($) {
    'use strict';

    return {
        isAjax: function () {
            return window.mstNavAjax;
        },

        isInstantMode: function () {
            return window.mstInstantlyMode;
        },

        isConfirmationMode: function () {
            return window.mstNavConfirmationMode;
        },

        isSeoFilterEnabled: function () {
            return window.mstSeoFilterEnabled;
        },

        isHighlightEnabled: function () {
            return window.mstHighlightEnabled;
        },

        getFriendlyClearUrl: function () {
            return window.mstFriendlyClearUrl;
        },

        getAjaxCallEvent: function () {
            return 'mst-nav__ajax-call';
        },

        getAjaxProductListWrapperId: function () {
            return '#m-navigation-product-list-wrapper';
        }
    };
});
