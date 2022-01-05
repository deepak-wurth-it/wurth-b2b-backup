define(
    [
        'jquery',
        'uiComponent'
    ],
    function ($, Component) {
        'use strict';

        return function(optionConfig){
            $.ajax({
                url: optionConfig.ajaxUrl,
                method: 'POST',
                data: {
                    is_ajax: 1,
                    request_type: optionConfig.requestType
                },
                success: function (result) {
                    if(result.errors) {
                        //$('#' + optionConfig.requestType).remove();
                    }
                    $('#' + optionConfig.requestType).html(result.block);

                }
            });
        };
    });