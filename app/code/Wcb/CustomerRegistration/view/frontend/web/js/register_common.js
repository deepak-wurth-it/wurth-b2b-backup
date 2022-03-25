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
            this.customValidation()
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
