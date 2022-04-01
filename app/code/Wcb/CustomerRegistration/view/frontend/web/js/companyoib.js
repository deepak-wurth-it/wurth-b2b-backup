define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    function main(config, element) {
        var $element = $(element);
        var AjaxUrl = config.AjaxUrl;
        var minlength = 11;

        $(document).ready(function(){
            //$('#vat_tax_id, #customer_code').on('change keyup', function () {
            $('#customer_code').on('change', function () {
                $("#confirm_customer_code").val("");
                var tax_value = $("#vat_tax_id").val();
                var customer_code = $("#customer_code").val();
                if (tax_value.length >= minlength && tax_value != ''  && tax_value != '' && customer_code != '' ) {
                    $.ajax({
                        url: AjaxUrl,
                        type: "GET",
                        data: {CompanyOib:tax_value, customer_code:customer_code},
                        showLoader: true, //use for display loader,
                        success: function (data) {
                            if (data.success == 'true') {
                                $("#confirm_customer_code").val(customer_code);
                            }
                            if (data.success == 'false') {
                                $('#confirm-reg').prop('disabled', true);
                                $("#confirm_customer_code").val("");
                                $( "#customer_code-error" ).html(data.message);
                                $( "#customer_code-error" ).show();
                            }
                        }
                    });}
            });
        });
    };
    return main;
});
