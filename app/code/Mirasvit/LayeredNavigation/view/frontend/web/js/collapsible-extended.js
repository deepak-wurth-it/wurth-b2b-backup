define([
    'jquery'
], function ($) {
    'use strict';

    // mage/collapsible
    return function (widget) {
        $.widget('mage.collapsible', widget, {
            _scrollToTopIfVisible: function () {
                return true;
            },

            _refresh: function () {
                //if ($('._opened', this.element).length) {
                //    this.options.active = true;
                //}

                return this._super();
            }

        });
        return $.mage.collapsible;
    }
});
