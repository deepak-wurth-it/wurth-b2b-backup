/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_RequisitionList/js/requisition/action/abstract',
    'underscore',
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (RequisitionComponent, _, $, confirm, alert) {
    'use strict';

    return RequisitionComponent.extend({
        defaults: {
            messageContainerSelector: '.page.messages',
            isProductValidated: false,
            confirmConfig: {
                title: $.mage.__('Add item to requisition list'),
                content: '<p>' + $.mage.__('The item "%1" is already in the "%2" requisition list.') + '</p>' +
                    '</br>' +
                    '<p>' + $.mage.__('Quantities for identical items will be combined.') + '</p>',
                buttonText: $.mage.__('Add item')
            }
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
         * Get action data
         *
         * @returns {Object}
         * @protected
         */
        _getActionData: function (list) {
            return _.extend(this._super(list), {
                'list_name': list.name,
                'product_data': JSON.stringify(this._getProductData())
            });
        },

        /**
         * Get product data
         *
         * @returns {Object}
         * @protected
         */
        _getProductData: function () {
            var productData = {
                sku: this.sku
            },
            productOptions = this._getProductOptions();

            if (productOptions) {
                productData.options = productOptions;

                if (productOptions.qty) {
                    productData.qty = productOptions.qty;
                }
            }

            return productData;
        },

        /**
         * Get product form
         *
         * @returns {*|jQuery|HTMLElement}
         * @protected
         */
        _getProductForm: function () {
            return $(this.productFormSelector);
        },

        /**
         * Get product options
         *
         * @returns string
         * @protected
         */
        _getProductOptions: function () {
            return this._getProductForm().serialize();
        },

        /**
         * Return loaded files information
         *
         * @returns {Object}
         * @protected
         */
        _getLoadedFiles: function () {
            var files = {};

            $.each(this._getProductForm().find('input[type="file"]'), function (index, input) {
                if (input.value) {
                    files[input.name] = input.files;
                }
            });

            return files;
        },

        /**
         * Get product validated flag
         *
         * @returns {Boolean}
         * @protected
         */
        _getIsProductValidated: function () {
            return this.isProductValidated;
        },

        /**
         * Set product validated flag
         *
         * @param {Boolean} value
         * @protected
         */
        _setIsProductValidated: function (value) {
            this.isProductValidated = value;
        },

        /**
         * Validate if product already exists in requisition list
         *
         * @param {Object} data
         * @param {Object} list
         * @protected
         */
        _validateProduct: function (data, list) {
            $.ajax({
                url: this.validationUrl,
                data: data,
                type: 'post',
                dataType: 'json',

                /** @inheritdoc */
                beforeSend: function () {
                    $(document.body).trigger('processStart');
                },

                /** @inheritdoc */
                success: $.proxy(function (res) {
                    this._validateProductSuccess(res, list);
                }, this),

                /** @inheritdoc */
                error: $.proxy(function (res) {
                    this._validateProductError(res);
                }, this),

                /** @inheritdoc */
                complete: function () {
                    $(document.body).trigger('processStop');
                }
            });
        },

        /**
         * Check Ajax response if product already exists on the list and show confirmation, otherwise proceed
         *
         * @param {Object} res
         * @param {Object} list
         * @protected
         */
        _validateProductSuccess: function (res, list) {
            var content = this.confirmConfig.content;

            if (!res || !res.success || !res.data) {
                this._validateProductError(res);

                return;
            }

            this._setIsProductValidated(true);

            if (res.data.productExists) {
                content = content.replace('%1', _.escape(this.productName));
                content = content.replace('%2', _.escape(list.name));

                this._showConfirmationModal(content, $.proxy(function () {
                    this.performListAction(list);
                }, this));
            } else {
                this.performListAction(list);
            }
        },

        /**
         * Handle validation request Ajax error
         *
         * @param {Object} res
         * @protected
         */
        _validateProductError: function (res) {
            if (!res.responseJSON.hideAlert) {
                alert({
                    title: $.mage.__('Error'),
                    content: $.mage.__(res.responseJSON.message),
                    actions: {
                        /** Click action */
                        always: function () {
                            location.reload();
                        }
                    }
                });
            }

            /**
             * Scroll to page's error message container if it isn't present in the viewport
             */
            function scrollToErrorMessageContainerIfItIsNotInViewport() {
                var messageContainer = document.querySelector(this.messageContainerSelector),
                    isMessageContainerWithinViewport;

                if (!messageContainer) {
                    return;
                }

                isMessageContainerWithinViewport = messageContainer.getBoundingClientRect().top >= 0;

                if (!isMessageContainerWithinViewport) {
                    messageContainer.scrollIntoView();
                }
            }

            $(document).on('customer-data-reload', function (event, sections) {
                if (sections.indexOf('messages') !== -1) {
                    // defer scroll to allow message(s) to render
                    setTimeout(scrollToErrorMessageContainerIfItIsNotInViewport.bind(this), 0);

                    // unbind the event
                    $(document).off('customer-data-reload', scrollToErrorMessageContainerIfItIsNotInViewport);
                }
            }.bind(this));
        },

        /**
         * Show add to requisition list confirmation modal
         * @param {String} content
         * @param {Function} callback
         * @protected
         */
        _showConfirmationModal: function (content, callback) {
            confirm({
                title: $.mage.__(this.confirmConfig.title),
                content: $.mage.__(content),
                modalClass: 'confirm-requisition-popup',
                actions: {
                    confirm: callback,
                    always: $.proxy(function () {
                        this._setIsProductValidated(false);
                    }, this)
                },
                buttons: [{
                    text: $.mage.__(this.confirmConfig.buttonText),
                    class: 'action primary add',

                    /**
                     * Close modal and call confirm callback
                     * @param {Event} event
                     */
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }, {
                    text: $.mage.__('Cancel'),
                    class: 'action secondary cancel',

                    /**
                     * Close modal and call cancel callback
                     * @param {Event} event
                     */
                    click: function (event) {
                        this.closeModal(event);
                    }
                }]
            });
        }
    });
});
