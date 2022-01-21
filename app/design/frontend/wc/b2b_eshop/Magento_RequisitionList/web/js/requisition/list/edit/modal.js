/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_RequisitionList/js/modal/modal-component',
    'jquery',
    'underscore',
    'jquery/validate',
    'mage/validation',
    'mage/translate'
], function (ModalComponent, $, _) {
    'use strict';

    return ModalComponent.extend({
        defaults: {
            options: {
                modalClass: 'modal-slide requisition-popup',
                focus: '.requisition-popup .input-text:first',
                buttons: [
                    {
                        'class': 'action primary confirm',
                        text: $.mage.__('Save'),
                        actions: ['actionDone']
                    },
                    {
                        'class': 'action secondary cancel',
                        text: $.mage.__('Cancel'),
                        actions: ['actionCancel']
                    }
                ],
                keyEventHandlers: {
                    /**
                     * Enter key press handler,
                     * Click on Save button
                     * @param {Object} event - event
                     */
                    enterKey: function (event) {
                        this.buttons[0].focus();
                        this.buttons[0].click();
                        event.preventDefault();
                    }
                }
            }
        },

        /**
         * Set values
         *
         * @param {Object} data
         * @returns void
         */
        setValues: function (data) {
            this.elems().forEach(function (elem) {
                if (_.isFunction(elem.setValues)) {
                    elem.setValues(data);
                }
            });
        },

        /**
         * Get values
         *
         * @returns {Object}
         */
        getValues: function () {
            var values = {};

            this.elems().forEach(function (elem) {
                if (_.isFunction(elem.getValues)) {
                    _.extend(values, elem.getValues());
                }
            });

            return values;
        },

        /**
         * Open modal
         *
         * @return {Promise}
         */
        openModal: function () {
            this._super();
            this.dfd = $.Deferred();

            return this.dfd.promise();
        },

        /**
         * Action done
         */
        actionDone: function () {
            var form = $(this.modal).find('form').validation();

            if (form.valid()) {
                this.dfd.resolve(this.getValues());
                this.closeModal();
            }
        },

        /**
         * Action cancel
         */
        actionCancel: function () {
            this.dfd.reject();
            this.closeModal();
        }
    });
});
