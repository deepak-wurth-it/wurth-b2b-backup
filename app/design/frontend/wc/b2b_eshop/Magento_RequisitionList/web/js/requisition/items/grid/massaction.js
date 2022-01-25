/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/dataPost',
    'mage/translate',
    'Magento_RequisitionList/js/requisition/items/grid/massaction/no-items-alert',
    'Magento_Ui/js/modal/confirm'
], function (Component, $, _, dataPost, $t, noItemsAlert, confirm) {
    'use strict';

    return Component.extend({
        defaults: {
            selectProvider: '',
            hasConfirm: false,
            confirmConfig: {
                confirmLabel: $t('Ok'),
                declineLabel: $t('Cancel')
            },
            modules: {
                selections: '${ $.selectProvider }'
            }
        },

        /**
         * Apply action
         *
         * @param {Object} data
         * @returns {exports}
         */
        applyAction: function (data) {
            if (!this.validate()) {
                return this;
            }
            data.selected = this.getSelections();

            return this._confirm(data).done(this._applyAction.bind(this));
        },

        /**
         * Confirm action
         *
         * @returns {Promise}
         * @protected
         */
        _confirm: function (data) {
            var dfd = $.Deferred(),
                config;

            if (!this._hasConfirm()) {
                return dfd.resolve(data).promise();
            }

            config = _.extend(this.confirmConfig, {
                buttons: [
                    {
                        text: this.confirmConfig.confirmLabel,
                        'class': 'action primary confirm',

                        /**
                         * @param {jQuery.Event} event
                         */
                        click: function (event) {
                            dfd.resolve(data);
                            this.closeModal(event, true);
                        }
                    },
                    {
                        text: this.confirmConfig.declineLabel,
                        'class': 'action secondary cancel',

                        /**
                         * @param {jQuery.Event} event
                         */
                        click: function (event) {
                            dfd.reject(data);
                            this.closeModal(event);
                        }
                    }]
            });

            confirm(config);

            return dfd.promise();
        },

        /**
         * Has confirmation
         *
         * @returns {Boolean}
         * @private
         */
        _hasConfirm: function () {
            return this.hasConfirm;
        },

        /**
         * Apply action
         *
         * @param {Object} data
         * @protected
         */
        _applyAction: function (data) {
            dataPost().postData({
                action: this.action,
                data: data
            });

            return this;
        },

        /**
         * Validate massaction
         *
         * @returns {Boolean}
         */
        validate: function () {
            var isValid = !_.isEmpty(this.getSelections());

            if (!isValid) {
                noItemsAlert();
            }

            return isValid;
        },

        /**
         * Get selections
         *
         * @returns {Array}
         */
        getSelections: function () {
            return this.selections().selected();
        }
    });
});
