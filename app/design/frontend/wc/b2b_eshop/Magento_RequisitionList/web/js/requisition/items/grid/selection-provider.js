/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore'
], function (Component, $, _) {
    'use strict';

    return Component.extend({
        defaults: {
            selected: [],
            selectionSelector: ''
        },

        /**
         * Init component
         */
        initialize: function () {
            this._super();
            this._observeSelection();
            this._updateSelection();
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super().observe(['selected']);

            return this;
        },

        /**
         * Observe selection elements
         *
         * @private
         */
        _observeSelection: function () {
            $(document).on('change', this.selectionSelector, function () {
                _.defer(this._updateSelection.bind(this));
            }.bind(this));
        },

        /**
         * Update selection
         *
         * @returns {Array}
         * @private
         */
        _updateSelection: function () {
            var selected = [],
                selectedElems = $(this.selectionSelector).filter(':checked');

            selectedElems.each(function (index, elem) {
                selected.push($(elem).attr('data-item-id'));
            });
            this.selected(selected);

            return selected;
        }
    });
});
