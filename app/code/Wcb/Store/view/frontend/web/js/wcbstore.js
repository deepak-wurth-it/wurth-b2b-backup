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
        initialize: function () {
            self = this;
            this._super();
            this.storeOption(self.stores_option);
            $('#select-delivery input.checkbox').on('change', function() {
                $('#select-delivery input.checkbox').not(this).prop('checked', false);  
            }); 
        },
        storeChange: function(value) {
            //console.log(value);
            //console.log(self.stores_option);
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
                    var UpdateStorePickupUrl = urlBuilder.build('wcbstore/ajax/updatestorepickup');
                    $.post(UpdateStorePickupUrl, data, function(returnedData) {
						console.log(returnedData);
						// This callback is executed if the post was successful     
					})
                }           
            });
            // this.selectedStore.subscribe(function(newValue) {
            //     console.log(newValue);
            // });
         }
    });
});


