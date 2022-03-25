define(['jquery', 'uiComponent', 'ko','mage/url'], function ($, Component, ko,urlBuilder) {
    'use strict';
    var self;
    return Component.extend({
        storeOption : ko.observable(0),
        storeChange : ko.observable(0),
        selectedStore:ko.observable(0),
        image:ko.observable(0),
        address:ko.observable(0),
        phone:ko.observable(0),
        store_name:ko.observable(0),
        map_url:ko.observable(0),
        email:ko.observable(0),
        addShippingProduct:ko.observable(true),
        addStore:ko.observable(false),
        storeActive : 0,
        UpdateStorePickupUrl : urlBuilder.build('wcbstore/ajax/updatestorepickup'),
        initialize: function () {
            self = this;
            this._super();
            this.storeOption(self.stores_option);
            this.shippingProductAction();
            
        },
        storeChange: function(value) {

            var storeActive = this.storeActive;
            var UpdateStorePickupUrl = this.UpdateStorePickupUrl;
            $.each(self.stores_option, function(key,val) {   
                if(val['entity_id'] == value){
                    var media = urlBuilder.build('media');
                    var imgUrl = media+val['image'];
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
                    if(storeActive){
						$.post(UpdateStorePickupUrl, data, function(returnedData) {
							//console.log(returnedData);
						})
				    } 
                
                }           
            });
          
         },
         shippingProductAction: function() {
            this.addStore(false);
            var actionVal = parseInt('2');
            this.storeActive = 0 ;
            var data  = {"action":actionVal};
            $.post(this.UpdateStorePickupUrl, data, function(returnedData) {
                            //console.log(returnedData);
              })
            return true;
         },
         storeAction: function() {
            this.storeActive = 1 ;
            this.addShippingProduct(false);
            $('.click-collect .select').trigger('change');
            return true;
         }
    });
});


