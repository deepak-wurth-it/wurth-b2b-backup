/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/product/add',
    'jquery',
    'mage/dataPost',
    'underscore'
], function (RequisitionComponent, $, dataPost, _) {
    'use strict';

    return RequisitionComponent.extend({

        /** @inheritdoc */
        _getProductOptions: function () {
            return this.options;
        },

        /**
         * Perform list action to prepare and send data via ajax for validation
         *
         * @param {Object} list
         * @returns {Promise|void}
         */
        performListAction: function (list) {
            var data = this._getActionData(list);

            if (this._getIsProductValidated()) {
                return this._super();
            }

            data = _.extend(data, {
                'form_key': $.mage.cookies.get('form_key')
            });

            data.isFromCartPage = true;

            this._validateProduct(data, list);
        }
    });
});
