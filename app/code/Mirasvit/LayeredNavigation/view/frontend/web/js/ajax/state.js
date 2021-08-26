define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/action/apply-filter'
], function ($, config, applyFilter) {
    'use strict';

    /**
     * Widget resets applied filters individually or completely.
     */
    $.widget('mst.mNavigationState', {
        options: {
            clearSelector: 'a.filter-clear'
        },

        _create: function () {
            // bind event to clear all filters
            $(this.options.clearSelector).on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();

                const url = $(e.currentTarget).prop('href');

                applyFilter.applyForced(url);

            }.bind(this));

            // bind events to remove individual filters
            $('[data-element = filter]', this.element).on('click', function (e) {
                e.stopPropagation();
                e.preventDefault();

                const $item = $(e.currentTarget);
                $item.addClass('_removed');

                const $a = $('a', $item);

                const url = $a.prop('href');

                applyFilter.applyForced(url);

            }.bind(this));
        }
    });

    return $.mst.mNavigationState;
});
