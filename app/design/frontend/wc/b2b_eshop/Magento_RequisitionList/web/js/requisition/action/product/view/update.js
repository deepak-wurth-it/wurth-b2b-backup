/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/product/view/add',
    'underscore',
    'jquery'
], function (ProductAddComponent, _) {
    'use strict';

    return ProductAddComponent.extend({

        /**
         * Update requisition list item
         *
         * @returns {*|Promise}
         */
        update: function () {
            return this.performListAction({});
        },

        /**
         * Get action data
         *
         * @returns {Object}
         * @protected
         */
        _getActionData: function (list) {
            return _.extend(this._super(list), {
                'item_id': this.itemId
            });
        }
    });
});
