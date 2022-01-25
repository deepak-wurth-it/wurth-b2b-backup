/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/abstract',
    'underscore',
    'jquery'
], function (RequisitionAction, _, $) {
    'use strict';

    return RequisitionAction.extend({
        defaults: {
            currentListId: null,
            modules: {
                gridMassaction: '${ $.gridMassactionName }'
            }
        },

        /**
         * Is list visible
         *
         * @param {Object} list
         */
        isListVisible: function (list) {
            return list.id !== this.currentListId;
        },

        /**
         * Perform new list action
         *
         * @returns {Promise}
         */
        performNewListAction: function () {
            if (!this._isActionValid({})) {
                return $.Deferred().reject().promise();
            }

            return this._super();
        },

        /**
         * Perform list action
         *
         * @param {Object} list
         * @returns {Promise}
         */
        performListAction: function (list) {
            if (!this._isActionValid()) {
                return $.Deferred().reject().promise();
            }

            return $.when(this.gridMassaction().applyAction({
                'list_id': list.id,
                'source_list_id': this.currentListId
            }));
        },

        /**
         * Is action valid
         *
         * @returns {Boolean}
         * @protected
         */
        _isActionValid: function () {
            return this.gridMassaction().validate();
        }
    });
});
