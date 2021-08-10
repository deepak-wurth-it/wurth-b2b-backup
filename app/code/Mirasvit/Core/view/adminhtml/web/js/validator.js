define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/modal'
], function($, Component, modal) {
    'use strict';

    return Component.extend({
        defaults: {
            url: null,
            modal: null,
            lastModule: null,
            cache: false
        },

        /**
         * Initializes Sticky component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super();
                _.bindAll(this,
                    'showModal'
                );

            return this;
        },

        /**
         * Run validation and show result.
         */
        validate: function(module) {
            if (!this.cache || module != this.lastModule || !this.modal) {
                this.lastModule = module;

                this.sendRequest(module)
                    .done(this.showModal)
                    .fail(function (response) {
                        console.log(response.responseText)
                    });

            } else if (this.modal) {
                this.modal.openModal();
            }
        },

        /**
         * Send request to start validation process.
         *
         * @returns {Promise}
         */
        sendRequest: function(module) {
            return $.ajax({
                showLoader: true,
                url: this.url,
                data: {module: module},
                type: 'POST'
            });
        },

        /**
         * Show modal window from response.
         */
        showModal: function(response) {
            var modalInstance = modal({
                autoOpen: true,
                modalClass: 'mst-validator-modal',
                responsive: true,
                clickableOverlay: true,
                title: 'Mirasvit Extensions Validator',
                type: 'slide',//popup
                buttons: []
            });

            // close handler
            $(modalInstance.options.modalCloseBtn).on('click', function() {
                modalInstance.closeModal();
            });

            // set content
            $('.mst-validator-modal .modal-content').html(response.content);

            if (response.isPassed) {
                modalInstance.setSubTitle('<span style="color: #3cb861">Success. All tests passed!</span>');
            } else {
                modalInstance.setSubTitle('<span style="color: #e41101">Some tests failed. Please, try to solve problems.</span>');
            }

            this.modal = modalInstance;
        }
    });
});