define([
    'jquery',
    'uiComponent',
    'mage/url',
    'Magento_Ui/js/lib/validation/validator',
], function ($, Component, urlBuilder, validator) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            this.checkEmailAddressExists();
            this.customValidation();
            this.checkOib();
        },
        checkEmailAddressExists: function () {
            $(document).on('change', '#email_address', function() {
                let email_address = $(this).val();
                $.ajax({
                    url: urlBuilder.build("excustomer/index/isemailexists"),
                    type: "POST",
                    data: {email: email_address},
                    showLoader: true
                }).success(function (data) {
                    if (data.success === 'true') {
                        $('#save-button').prop('disabled', true);
                        $("#email_address_exist_error").html(data.message)
                        $("#email_address_exist_error").show();
                    }
                    return true;
                });
            });

        },
        checkOib: function(){
            let self = this;
            $('#vat_tax_id').on('change', function () {
                if(self.is_new_customer == "false"){
                    $("#company_name").val("");
                }
                var tax_value = $("#vat_tax_id").val();
                var minlength = 11;
                var customurl = urlBuilder.build("excustomer/account/verifycompany");
                if (tax_value.length >= minlength && tax_value != '' && $.isNumeric(tax_value)) {
                    $.ajax({
                        url: customurl,
                        type: "GET",
                        data: {CompanyOib: tax_value},
                        showLoader: true //use for display loader
                    }).done(function (data) {
                        // for new customer
                        if (data.success == true && self.is_new_customer == "true") {
                            $('#save-button').prop('disabled', true);
                            $("#vat_tax_id").val("");
                            $("#link-error-oib").html($.mage.__("Company OIB already exists. Please enter valid company OIB."));
                            $("#link-error-oib").show();
                        }
                        // For existing customer
                        if (data.success == false && self.is_new_customer == "false") {
                            $('#save-button').prop('disabled', true);
                            $("#vat_tax_id").val("");
                            $("#link-error-oib").html($.mage.__("Company OIB does not exists. Please enter valid company OIB."));
                            $("#link-error-oib").show();
                        }

                        // If OIB find display company name
                        if (data.success == true && self.is_new_customer == "false") {
                            $("#company_name").val(data.html);
                        }
                        return true;
                    });
                }
            });
        },
        customValidation: function() {
            $.validator.addMethod(
                "validate-ponumber-custom",
                function(value, element) {
                    return /^[- +()]*[0-9][- +()0-9]*$/i.test(value);
                },
                $.mage.__("Please enter valid phone number.")
            );
        }
    });
});
