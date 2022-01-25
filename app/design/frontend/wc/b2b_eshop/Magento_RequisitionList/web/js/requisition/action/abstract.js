/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition',
    'jquery',
    'underscore',
    'mage/dataPost'
], function (RequisitionComponent, $, _, dataPost) {
    'use strict';

    return RequisitionComponent.extend({
        defaults: {
            template: 'Magento_RequisitionList/requisition-list/action',
            title: '',
            action: '',
            'action_data': {},
            modules: {
                editModule: '${ $.editModuleName }'
            }
        },

        /**
         * Get action title
         *
         * @returns {String}
         */
        getTitle: function () {
            return this.title;
        },

        /**
         * Get mobile label
         *
         * @returns {String}
         */
        getMobileLabel: function () {
            return this.mobileLabel || this.getTitle();
        },

        /**
         * Is list visible
         */
        isListVisible: function () {
            return true;
        },

        /**
         * Perform list action
         *
         * @param {Object} list
         * @returns {Promise}
         */
        performListAction: function (list) {
            var dfd = $.Deferred(),
                postData, files;

            if (!this._isActionValid()) {
                return dfd.reject().promise();
            }

            postData = {
                action: this.action,
                data: this._getActionData(list)
            };
            files = this._getLoadedFiles();

            if (Object.keys(files).length) {
                postData = _.extend(postData, {
                    'files': files
                });
            }

            dataPost().postData(postData);

            return dfd.resolve().promise();
        },

        /**
         * Perform new list action
         *
         * @returns {Promise}
         */
        performNewListAction: function () {
            return this._createList()
                .then(this.performListAction.bind(this));
        },

        /**
         * Create new list
         *
         * @returns {Promise}
         */
        _createList: function () {
            return this.editModule().edit({});
        },

        /**
         * Is action valid
         *
         * @returns {Boolean}
         * @protected
         */
        _isActionValid: function () {
            return true;
        },

        /**
         * Get action data
         *
         * @param {Object} list
         * @returns {Object}
         * @protected
         */
        _getActionData: function (list) {
            return _.extend(this['action_data'], {
                'list_id': list.id,
                'list_name': list.name
            });
        },

        /**
         * Return loaded files information
         *
         * @returns {Object}
         * @protected
         */
        _getLoadedFiles: function () {
            return {};
        }
    });
});
