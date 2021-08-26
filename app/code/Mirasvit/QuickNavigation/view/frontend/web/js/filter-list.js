define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mst.quickNavFilterList', {
        $container: null,

        _create: function () {
            this.$container = $('[data-element = container]', this.element);

            if (this.$container[0].scrollWidth > this.$container.width()) {
                this.element.addClass('_scrollable');
            }

            $('[data-element = prev]', this.element).on('click', function () {
                this.scrollToStart();
            }.bind(this));

            $('[data-element = next]', this.element).on('click', function () {
                this.scrollToEnd();
            }.bind(this));
        },

        scrollToStart: function () {
            this.scrollTo(0)
        },

        scrollToEnd: function () {
            this.scrollTo(this.$container[0].scrollWidth);
        },

        scrollTo: function (left) {
            this.$container.animate({scrollLeft: left}, 500)
        }
    });

    return $.mst.quickNavFilterList;
});
