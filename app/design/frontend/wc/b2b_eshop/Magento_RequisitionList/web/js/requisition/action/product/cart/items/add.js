/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/product/add',
    'underscore',
    'jquery'
], function (RequisitionComponent, _, $) {
    'use strict';

    return RequisitionComponent.extend({
        defaults: {
            confirmConfig: {
                title: $.mage.__('Add items to requisition list'),
                content: '<p>' + $.mage.__('Some items in your Shopping Cart are already in the %1 requisition list.') + '</p>' + // eslint-disable-line max-len
                    '</br>' +
                    '<p>' + $.mage.__('Quantities for identical items will be combined.') + '</p>',
                buttonText: $.mage.__('Add items')
            }
        },

        /**
         * Get product data
         *
         * @returns Array
         * @protected
         */
        _getProductData: function () {
            return this.productData;
        },

        /**
         * Perform list action to send product data via ajax for validation
         *
         * @param {Object} list
         * @returns {Promise|void}
         */
        performListAction: function (list) {
            var data = {
                // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                list_id: list.id,
                list_name: list.name,
                form_key: $.mage.cookies.get('form_key'),
                // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                productData: this._getProductData()
            };

            if (this._getIsProductValidated()) {
                return this._super();
            }

            this._validateProduct(data, list);
        },

        /**
         * Check ajax response if products are already on the list then show confirmation, otherwise proceed
         *
         * @param {Object} res
         * @param {Object} list
         * @protected
         */
        _validateProductSuccess: function (res, list) {
            var content = this.confirmConfig.content.replace('%1', '"' + _.escape(list.name) + '"');

            if (res.success) {
                this._setIsProductValidated(true);
            }

            if (res.data.productExists) {
                this._showConfirmationModal(content, $.proxy(function () {
                    this.performListAction(list);
                }, this));
            } else {
                this.performListAction(list);
            }
        }
    });
});
