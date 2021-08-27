define([
    "jquery",
    "domReady!"
], function ($) {
    'use strict';

    $.widget('mst.navSizeLimiter', {
        options: {
            limit:    5,
            textLess: '',
            textMore: ''
        },

        _create: function () {
            var $toggle = $('[data-element = sizeToggle]', this.element);

            $toggle.on('click', function () {
                if ($toggle.html() === this.options.textMore) {
                    $('[data-element = filter]', this.element).show();
                    $toggle.html(this.options.textLess);
                } else {
                    $('[data-element = filter]', this.element).css('display', '');
                    $toggle.html(this.options.textMore);
                }
            }.bind(this));

            $(this.element).on('search', function (e, status) {
                if (status) {
                    $toggle.hide()
                } else {
                    $toggle.show()
                }
            });
        }
    });

    return $.mst.navSizeLimiter;
});
