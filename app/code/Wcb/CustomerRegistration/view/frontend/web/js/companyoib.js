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
        $('#vat_tax_id, #customer_code').on('change keyup', function () {
            var tax_value = $("#vat_tax_id").val();
            var customer_code = $("#customer_code").val();
            if (tax_value.length >= minlength && tax_value != ''  && tax_value != '' && customer_code != '' ) {
                $.ajax({
                    url: AjaxUrl,
                    type: "GET",
                    data: {CompanyOib:tax_value, customer_code:customer_code},
                    showLoader: true, //use for display loader,
                    success: function (data) {
                        if (data.success != null) {
                            $('#confirm-reg').prop('disabled', false);
                            $("#company_name").val(data.html);
                                          
                            if (data.compid == data.cid) { 
                                $("#confirm_customer_code").val(customer_code);
                            }
                        }                
                        if (data.success == 'false') { alert('ghfhf');
                            $('#confirm-reg').prop('disabled', true);
                            $("#company_name").val('');
                            $( "#link-error" ).append( "<p>Cusomer is not linked. Please enter valid customer code</p>" );  // set the message as html of span
                        } 
                        return true;
                    }
                });}
            });
        });
 
 
    };
    return main;
});