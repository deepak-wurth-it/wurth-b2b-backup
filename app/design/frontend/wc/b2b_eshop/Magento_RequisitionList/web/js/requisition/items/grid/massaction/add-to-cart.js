/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/items/grid/massaction',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_RequisitionList/js/modal/confirm/add-to-cart',
    'Magento_RequisitionList/js/requisition/items/grid/massaction/no-items-alert',
    'Magento_Customer/js/customer-data'
], function (MassActionComponent, $, _, $t, confirmComponent, noItemsAlert, customerData) {
    'use strict';

    var noValidItemsAlert = _.partial(noItemsAlert, {
        title: $t('Cannot Perform the Requested Action'),
        content: $t('This action cannot be performed because the selected product(s) require attention. You must resolve these issues before you can continue.') //eslint-disable-line max-len
    });

    return MassActionComponent.extend({
        defaults: {
            itemSelectorPattern: '.requisition-grid input[data-item-id=%id%]'
        },

        /**
         * Init component
         */
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
        },

        /**
         * Is add to cart enable
         *
         * @returns {Boolean}
         */
        isEnable: function () {
            return this.getSelections().length;
        },

        /**
         * Validate massaction
         *
         * @returns {Boolean}
         */
        validate: function () {
            var validItems, isValid;

            if (!this._super()) {
                return false;
            }

            validItems = _.filter(this.getSelections(), this._isItemActionValid, this);
            isValid = !_.isEmpty(validItems);

            if (!isValid) {
                noValidItemsAlert();
            }

            return isValid;
        },

        /**
         * Is item action valid
         *
         * @param {Number} itemId
         * @returns {Boolean}
         * @private
         */
        _isItemActionValid: function (itemId) {
            var itemElement = $(this.itemSelectorPattern.replace('%id%', itemId));

            return !itemElement.data('item-has-errors');
        },

        /**
         * Has confirmation
         *
         * @returns {Boolean}
         * @private
         */
        _hasConfirm: function () {
            return this._super() && this.cart()['summary_count'];
        },

        /**
         * Confirm action
         *
         * @returns {Promise}
         * @protected
         */
        _confirm: function (data) {
            if (!this._hasConfirm()) {
                return $.Deferred().resolve(data).promise();
            }

            return confirmComponent().confirm(data);
        }
    });
});
