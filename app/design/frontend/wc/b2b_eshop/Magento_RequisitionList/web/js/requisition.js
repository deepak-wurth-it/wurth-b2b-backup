/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.requisition = customerData.get('requisition');
        },

        /**
         * Is can create.
         *
         * @returns {Boolean}
         */
        isCanCreateList: function () {
            return !this.requisition().items ||
                this.requisition().items.length < this.requisition()['max_allowed_requisition_lists'];
        }
    });
});
