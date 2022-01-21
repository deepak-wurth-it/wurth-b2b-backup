/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate',
    'mage/template',
    'text!Magento_RequisitionList/template/modal/confirm/add-to-cart.html',
    'underscore'
], function (UiElement, $, confirm, $t, mageTemplate, contentTemplate, _) {
    'use strict';

    return UiElement.extend({
        defaults: {
            confirmConfig: {
                title: $t('The shopping cart isn\'t empty'),
                modalClass: 'requisition-popup modal-slide'
            },
            confirmContentData: {
                mainText: $t('You have items in your shopping cart. Would you like to merge items in this order with items of this shopping cart or replace them?'), //eslint-disable-line max-len
                secondaryText: $t('Select Cancel to stay on the current page.')
            }
        },

        /**
         * Confirm action
         *
         * @param {Object} data
         * @returns {Promise}
         */
        confirm: function (data) {
            var deferred = $.Deferred(),
                config = _.extend(this.confirmConfig, {
                    content: this._getContentText(),
                    buttons: [
                        {
                            text: $t('Merge'),
                            'class': 'action primary confirm',

                            /**
                             * @param {jQuery.Event} event
                             */
                            click: function (event) {
                                deferred.resolve(data);
                                this.closeModal(event, true);
                            }
                        },
                        {
                            text: $t('Replace'),
                            'class': 'action replace',

                            /**
                             * @param {jQuery.Event} event
                             */
                            click: function (event) {
                                data['is_replace'] = true;
                                deferred.resolve(data);
                                this.closeModal(event);
                            }
                        },
                        {
                            text: $t('Cancel'),
                            'class': 'action secondary cancel',

                            /**
                             * @param {jQuery.Event} event
                             */
                            click: function (event) {
                                deferred.reject(data);
                                this.closeModal(event);
                            }
                        }
                    ]
                });

            confirm(config);

            return deferred.promise();
        },

        /**
         * Get content text
         *
         * @returns {String}
         */
        _getContentText: function () {
            return mageTemplate(contentTemplate, this.confirmContentData);
        }
    });
});
