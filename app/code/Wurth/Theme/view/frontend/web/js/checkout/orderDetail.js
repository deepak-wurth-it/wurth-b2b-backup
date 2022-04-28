define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Customer/js/model/customer'
    ],
    function (ko, $, Component, customer) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Wurth_Theme/checkout/orderDetail',
                currentUserEmail: ''
            },
            initObservable: function () {
                this._super();

                if(customer.customerData.email){
                    this.currentUserEmail = customer.customerData.email;
                }
                return this;
            }
        });
    }
);
