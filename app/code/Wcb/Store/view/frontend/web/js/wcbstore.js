define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/url',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Customer/js/customer-data'
], function ($, Component, ko, urlBuilder, getTotalsAction, customerData) {
    'use strict';
    var self;
    return Component.extend({
        storeOption: ko.observable(0),
        storeChange: ko.observable(0),
        selectedStore: ko.observable(0),
        image: ko.observable(0),
        address: ko.observable(0),
        phone: ko.observable(0),
        store_name: ko.observable(0),
        map_url: ko.observable(0),
        email: ko.observable(0),
        addShippingProduct: ko.observable(true),
        addStore: ko.observable(false),
        EnableDisableStore: ko.observable(false),
        storeActive: 0,
        EnableDisableStore: ko.observable(false),
        UpdateStorePickupUrl: urlBuilder.build('wcbstore/ajax/updatestorepickup'),
        initialize: function () {
            self = this;
            this._super();
            this.storeOption(self.stores_option);
            if(window.checkoutConfig.quoteData.pickup_store_id && window.checkoutConfig.quoteData.pickup_store_id != ''){
                this.addStore(true);
                this.addShippingProduct(false);
                this.EnableDisableStore(true);
            }

            //this.shippingProductAction();
        },
        storeChange: function (value) {

            var storeActive = this.storeActive;
            var UpdateStorePickupUrl = this.UpdateStorePickupUrl;
            $.each(self.stores_option, function (key, val) {
                if (val['entity_id'] == value) {
                    var media = urlBuilder.build('media');
                    var imgUrl = media + val['image'];
                    self.image(imgUrl);
                    self.store_name(val['name']);
                    self.address(val['address']);
                    self.phone(val['phone']);
                    self.email(val['contact_email']);
                    self.map_url(val['map_url']);
                    //var data = ko.toJSON(val);
                    var data = val;
                    var actionVal = parseInt('1');
                    data['action'] = actionVal;
                    if (storeActive) {
                        $('body').trigger('processStart');
                        $.post(UpdateStorePickupUrl, data, function (response) {

                            if (response.item_form != '') {
                                $("#cart-item-form-section").replaceWith(response.item_form);
                            }

                            //reload cart and total sections
                            var sections = ['cart'];
                            customerData.reload(sections, true);

                            var deferred = $.Deferred();
                            getTotalsAction([], deferred);

                            $('body').trigger('processStop');
                        })
                    }

                }
            });

        },
        shippingProductAction: function () {
            this.addStore(false);
            var actionVal = parseInt('2');
            this.storeActive = 0;
            var data = {"action": actionVal};
            this.EnableDisableStore(false);
            $('body').trigger('processStart');
            $.post(this.UpdateStorePickupUrl, data, function (response) {
                if (response.item_form != '') {
                    $("#cart-item-form-section").replaceWith(response.item_form);
                }

                //reload cart and total sections
                var sections = ['cart'];
                customerData.reload(sections, true);

                var deferred = $.Deferred();
                getTotalsAction([], deferred);

                $('body').trigger('processStop');
            })
            return true;
        },
        storeAction: function () {
            this.storeActive = 1;
            this.addShippingProduct(false);
            $('.click-collect .select').trigger('change');
            this.EnableDisableStore(true);
            return true;
        }
    });
});
