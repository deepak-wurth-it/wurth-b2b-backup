define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
 
    function main(config, element) {
        var $element = $(element);
        var AjaxUrl = config.AjaxUrl;
        var CurrentProduct = config.CurrentProduct;
         
        $(document).ready(function(){
            setTimeout(function(){
                $.ajax({
                    context: '#ajaxresponse',
                    url: AjaxUrl,
                    type: "POST",
                    data: {currentproduct:CurrentProduct},
                }).done(function (data) {
                    $('#ajaxresponse').html(data.output);
                    return true;
                });
            },2000);
        });
 
 
    };
    return main;
});