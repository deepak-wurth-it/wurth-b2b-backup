define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/action/apply-filter'
], function ($, config, applyFilter) {
    'use strict';

    /**
     * Work with default filters.
     */
    $.widget('mst.navLabelRenderer', {
        _create: function () {
            $('[data-element = filter]', this.element).each(function (idx, item) {
                const $item = $(item);

                $item.on('click', function (e) {
                    if ($(e.target).prop('tagName') !== 'INPUT') {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    if ($item.prop('tagName') !== 'A') {
                        if ($(e.target).prop('tagName') !== 'INPUT') {
                            this.toggleCheckbox($item);
                        }
                    }

                    this.toggleSwatch($item);
                    this.showHighLight($item);

                    const url = $item.prop('tagName') === 'A'
                        ? $item.prop('href')
                        : $('a', $item).prop('href');

                    applyFilter.apply(url, $item);
                }.bind(this))
            }.bind(this));
        },

        showHighLight: function ($el) {
            if ($el.hasClass('_checked')) {
                $el.addClass("_highlight");
            } else {
                $el.removeClass("_highlight");
            }
        },

        toggleCheckbox: function ($el) {
            const $checkbox = $('input[type=checkbox]', $el);

            if ($checkbox.prop('disabled')){
                return true;
            }

            $checkbox.prop('checked', !$checkbox.prop('checked'));
        },

        toggleSwatch: function ($el) {
            const $checkbox = $('input[type=checkbox]', $el);

            if ($checkbox.prop('disabled')){
                return true;
            }

            if ($el.hasClass('_checked')) {
                $el.removeClass('_checked');
            } else {
                $el.addClass('_checked');
            }
        }
    });

    return $.mst.navLabelRenderer;
});
