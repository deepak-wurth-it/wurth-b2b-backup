/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    './abstract'
], function (RequisitionComponent) {
    'use strict';

    return RequisitionComponent.extend({
        /**
         * Edit requisition list
         *
         * @param {Object} data
         * @returns {Promise}
         */
        edit: function (data) {
            return this.editModule().edit(data).done(function () {
                window.location.reload();
            });
        }
    });
});
