define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Customer/js/model/customer'
    ],
    function (ko, $, Component, customerData) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Wurth_Theme/checkout/companyDetail',
                company_name: '',
                company_address: '',
                company_city: '',
                postcode:''
            },
            initObservable: function () {
                this._super();
                let companyDetail = window.checkoutConfig.company_detail;
                if (companyDetail) {
                    this.company_name = companyDetail.name;
                    this.company_address = companyDetail.street.join(", ");
                    this.company_city = companyDetail.postcode + "/ " + companyDetail.city;
                }
                return this;
            }
        });
    }
);
