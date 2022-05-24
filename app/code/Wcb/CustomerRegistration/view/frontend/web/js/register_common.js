define([
    'jquery',
    'uiComponent',
    'mage/url',
    'Magento_Ui/js/lib/validation/validator',
    'Magento_Customer/js/customer-data',
], function ($, Component, urlBuilder, validator, customerData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            this.checkEmailAddressExists();
            this.customValidation();
            this.checkOib();
            this.getActivates();
            this.customerLogin();
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
                var exist_customer_url = urlBuilder.build("excustomer/account/create");
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

                            $("#link-error-oib").html($.mage.__("Vaš OIB već postoji u bazi.Molimo da koristite registraciju za postoječe kupce na Vaš OIB već postoji u bazi.Molimo da koristite registraciju za postoječe kupce na <a href='"+exist_customer_url+"'>ovom linku</a>."));
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
                    return /^[1-9][0-9]*$/i.test(value);
                },
                $.mage.__("Please enter valid phone number.")
            );
        },
        getActivates: function(){
            $('#division').on('change', function () {
                let division = $('option:selected', this).attr('data-id');
                $.ajax({
                    url: urlBuilder.build("excustomer/index/getactivates"),
                    type: "POST",
                    data: {division: division},
                    showLoader: true
                }).success(function (data) {
                    $("#activates").html(data.option)
                });
            });
        },
        customerLogin: function(){
            $('#login-form').on('submit', function () {
                $("#send2").prop('disabled', true);
                if(!$('#login-form').validation('isValid')){
                    $("#send2").prop('disabled', false);
                    return false;
                }
                $.ajax({
                    url: urlBuilder.build("excustomer/account/loginpost"),
                    type: "POST",
                    data: $('#login-form').serialize(),
                    showLoader: true
                }).success(function (data) {
                    $("#send2").prop('disabled', false);
                    var sections = ['cart','checkout-data','customer'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                    if(data.status == "true") {
                        window.location.href = data.redirect_url;
                    }
                    if(data.status != "true") {
                        $("#send2").prop('disabled', false);
                    }
                });
                return false;
            });

        }
    });
});
