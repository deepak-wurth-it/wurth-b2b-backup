/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/product/add',
    'underscore',
    'jquery'
], function (ProductAddComponent) {
    'use strict';

    return ProductAddComponent.extend({
        defaults: {
            productFormSelector: '',
            configureModeHash: 'requisition_configure'
        },

        /**
         * Init component
         */
        initialize: function () {
            this._super();

            if (this.isConfigureMode()) {
                this._validateProductForm();
            }
        },

        /**
         * Validate product form
         *
         * @returns {Boolean}
         * @private
         */
        _validateProductForm: function () {
            if (!this._getProductForm().is(':visible')) {
                this._getProductForm().parent().show();
            }

            return this._getProductForm().valid();
        },

        /**
         * Is action valid
         *
         * @returns {Boolean}
         * @protected
         */
        _isActionValid: function () {
            return this._validateProductForm();
        },

        /**
         * Is configure mode
         *
         * @returns {Boolean}
         */
        isConfigureMode: function () {
            var hash = window.location.hash.replace('#', '');

            return hash == this.configureModeHash; //eslint-disable-line eqeqeq
        }
    });
});
