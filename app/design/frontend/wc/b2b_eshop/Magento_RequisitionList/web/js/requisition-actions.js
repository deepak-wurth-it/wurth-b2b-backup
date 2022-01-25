/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/dataPost',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal',
    'jquery-ui-modules/widget',
    'jquery/validate',
    'mage/translate',
    'mage/mage',
    'escaper'
], function ($, dataPost, confirm, modal, widget, validate, translate, mage, escaper) {
    'use strict';

    $.widget('mage.requisitionActions', {
        options: {
            form: '#form-requisition-list',
            button: {
                removeList: '[data-action="remove-list"]',
                removeItem: '[data-action="remove-item"]',
                removeSelected: '[data-action="remove-selected"]',
                update: '[data-action="update-list"]',
                updateItem: '[data-action="edit-item"]'
            },
            input: {
                selectAll: '[data-role="select-all"]',
                select: '[data-role="select-item"]',
                qty: '[data-role="requisition-item-qty"]',
                remove: '[data-action="requisition-item-check"]:checked',
                selectionSelector: '[data-action="requisition-item-check"]'
            },
            confirmMessage: {
                removeList: '',
                removeSelected: $.mage.__(
                    'Are you sure you would like to remove selected items from the requisition list?'
                )
            },
            titleNames: {
                removeList: $.mage.__(
                    'Delete Requisition List?'
                )
            },
            deleteUrl: '',
            isAjax: false
        },

        /**
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * @private
         */
        _bind: function () {
            var self = this,
                events = {};

            /**
             * @param {jQuery.Event} event
             */
            events['click ' + this.options.button.removeList] =  function (event) {
                event.stopPropagation();

                confirm({
                    title: self.options.titleNames.removeList,
                    modalClass: 'requisition-popup modal-slide',
                    content: $.mage.__(
                        'Are you sure you want to delete "%1" list? This action cannot be undone.'
                    ).replace('%1', this.prepareNameForHtml($(event.currentTarget).data('delete-list').listName)),
                    buttons: [{
                        text: $.mage.__('Delete'),
                        'class': 'action primary confirm',

                        /**
                         * @param {jQuery.Event} e
                         */
                        click: function (e) {
                            this.closeModal(e, true);
                        }
                    }, {
                        text: $.mage.__('Cancel'),
                        'class': 'action secondary cancel',

                        /**
                         * @param {juery.Event} e
                         */
                        click: function (e) {
                            this.closeModal(e);
                        }
                    }],
                    actions: {
                        /** Confirm */
                        confirm: function () {
                            var url = $(event.currentTarget).data('delete-list').deleteUrl;

                            self._request(url);
                        },

                        /** Always */
                        always: function (e) {
                            e.stopImmediatePropagation();
                        }
                    }
                });
            };

            /**
             * @param {jQuery.Event} event
             */
            events['click ' + this.options.button.update] =  function (event) {
                var data = {},
                    url;

                event.stopPropagation();

                if ($(this.options.form).valid()) {
                    url = $(event.currentTarget).data('update-list').updateUrl;

                    $(self.options.input.qty).each(function (index, object) {
                        data[$(object).attr('name')] = $(object).val();
                    });
                    self._request(url, data);
                }
            };

            /**
             * @param {jQuery.Event} event
             */
            events['click ' + this.options.button.removeItem] =  function (event) {
                var data = {};

                event.stopPropagation();
                data.selected = $(event.currentTarget).data('delete-list').itemId;

                confirm({
                    modalClass: 'requisition-popup modal-slide',
                    content: self.options.confirmMessage.removeSelected,
                    buttons: [{
                        text: $.mage.__('Delete'),
                        'class': 'action primary confirm',

                        /**
                         * @param {jQuery.Event} e
                         */
                        click: function (e) {
                            this.closeModal(e, true);
                        }
                    }, {
                        text: $.mage.__('Cancel'),
                        'class': 'action secondary cancel',

                        /**
                         * @param {jQuery.Event} e
                         */
                        click: function (e) {
                            this.closeModal(e);
                        }
                    }],
                    actions: {
                        /** Confirm */
                        confirm: function () {
                            var url = $(event.currentTarget).data('delete-list').deleteUrl;

                            self._request(url, data);
                        },

                        /**
                         * @param {jQuery.Event} e
                         */
                        always: function (e) {
                            e.stopImmediatePropagation();
                        }
                    }
                });
            };

            /**
             * @param {jQuery.Event} event
             */
            events['click ' + this.options.button.updateItem] =  function (event) {
                var url;

                event.stopPropagation();
                url = $(event.currentTarget).data('update-item').editItemUrl;
                self._request(url);
            };

            /**
             * @param {jQuery.Event} event
             */
            events['change ' + this.options.input.selectAll] =  function (event) {
                var selectAll = $(event.currentTarget);

                $(self.options.input.select).filter(':enabled')
                    .prop('checked', selectAll.prop('checked'))
                    .trigger('change');
            };

            /**
             * Check discrete select event trigger selectAll check/uncheck
             */
            events['change ' + this.options.input.select] =  function () {
                var isAllItemsChecked = $(self.options.input.selectionSelector).filter(':unchecked').length === 0;

                $(self.options.input.selectAll).prop('checked', isAllItemsChecked);
            };

            this._on(this.element, events);
        },

        /**
         * @private
         */
        _request: function (action, data) {
            dataPost().postData({
                action: action,
                data: data || {}
            });
        },

        /**
         * Prepare the given name to be rendered as HTML
         *
         * @param {String} name
         * @return {String}
         */
        prepareNameForHtml: function (name) {
            return escaper.escapeHtml(name);
        }
    });

    return $.mage.requisitionActions;
});
