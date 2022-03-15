define([
    'jquery',
    'uiComponent',
    'mage/url',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data'
], function ($, Component, urlBuilder, getTotalsAction, customerData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            this.increDecreQty();
            this.removeItem();
        },
        removeItem: function () {
            let self = this;
            $(document).on('click','.remove-cart-item',function(){
                let item_id = $(this).attr("data-item-id");
                self.removeItemFormCart(item_id);
            });
        },
        increDecreQty: function(){
            let self = this;
            $(document).on('click','.increaseQty, .decreaseQty',function(){
                let inputElem = $(this).parents('td').find('input');
                let currentQty = inputElem.val();
                let qty = currentQty;
                let item_id = $(this).attr("data-item-id");
                if ($(this).hasClass('increaseQty')) {
                    qty = parseInt(currentQty) + parseInt(1);
                    inputElem.val(qty);
                    self.updateQty(item_id, qty);
                } else {
                    if (currentQty > 1) {
                        qty = parseInt(currentQty) - parseInt(1);
                        inputElem.val(parseInt(currentQty) - parseInt(1));
                        self.updateQty(item_id, qty);
                    }
                }
            });
            $(document).on('keyup','.cart-item-qty-box',function(){
                let qty = $(this).val();
                let item_id = $(this).attr("data-item-id");
                if(qty && qty != "0" && item_id){
                    self.updateQty(item_id, qty)
                }
            });

        },
        removeItemFormCart: function (item_id) {
            let self = this;
            let url = urlBuilder.build("theme/cart/removeitem");
            $.ajax({
                url: url,
                type: "POST",
                data: {item_id:item_id},
                showLoader: true,
            }).success(function (response) {
                self.reloadSections();
                if(response.item_form != ''){
                    $("#cart-item-form-section").replaceWith(response.item_form);
                }
            });
        },
        updateQty: function(item_id, qty) {
            let self = this;
            let url = urlBuilder.build("theme/cart/updateitem");
            $.ajax({
                url: url,
                type: "POST",
                data: {item_id:item_id, qty:qty},
                showLoader: true,
            }).success(function (response) {
                self.reloadSections();
                if(response.item_form != ''){
                    $("#cart-item-form-section").replaceWith(response.item_form);
                }
            });
        },
        reloadSections: function(){
            var sections = ['cart'];
            customerData.reload(sections, true);
            var deferred = $.Deferred();
            getTotalsAction([], deferred);
        }
    });
});
