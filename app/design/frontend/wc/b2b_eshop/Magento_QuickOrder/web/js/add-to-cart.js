/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal',
    'jquery-ui-modules/widget'
], function ($, getTotalsAction, customerData, modal) {
    'use strict';

    $.widget('mage.quickOrderAddToCart', {

        options: {
            buttonAddToCart: 'button.tocart'
        },

        /**
         * Initialization of widget.
         *
         * @private
         */
        _init: function () {
            this.element.on('submit', function () {
                if ($(this.element).valid() === false) {
                    //$(this.options.buttonAddToCart).prop('disabled', true);
                } else {
                    $('body').trigger('processStart');
                    this.addToCartWithAjax($(this.element), false);
                }
                return false;

            }.bind(this));
            this.popupAddtoCart();
        },
        popupAddtoCart: function () {
            let self = this;
            $(document).on("click", "#add_to_cart_upload", function () {
                $('body').trigger('processStart');
                self.addToCartWithAjax($("#form-addbysku"), true);
            })
        },
        addToCartWithAjax: function (currentForm, openPopup) {
            let form = currentForm;
            let actionUrl = form.attr('action');
            let self = this;
            $.ajax({
                type: "POST",
                url: actionUrl,
                data: form.serialize() + "&isAjax=1",
                showLoader: true,
                success: function (data) {
                    form.trigger("reset");
                    if(data.item_form != ''){
                        if($("#cart-item-form-section").length > 0){
                            $("#cart-item-form-section").replaceWith(data.item_form);
                        }else{
                            $(".cart-empty").replaceWith(data.item_form);
                        }
                    }

                    //cart reload
                    var sections = ['cart'];
                    customerData.reload(sections, true);

                    // The totals summary block reloading
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);

                    // Open import success popup
                    if (openPopup) {
                        self.openImportProductPopup();
                    }
                    $('body').trigger('processStop');
                }
            });
        },
        openImportProductPopup: function () {
            $('#open-file').modal('closeModal');
            $('.form-addbysku .deletable-item:not(:last)').remove();
            var optionsImportPopup = {
                type: 'popup',
                title: '',
                modalClass: 'open-file',
                responsive: true,
                innerScroll: true,
                buttons: false
            };
            var popup = modal(optionsImportPopup, $('#import-popup'));
            $('#import-popup').modal('openModal');
        }
    });

    return $.mage.addToCart;
});
