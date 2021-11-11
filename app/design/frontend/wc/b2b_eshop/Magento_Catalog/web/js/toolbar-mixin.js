define([
    'jquery',    
    'uiComponent'
], function($, Component) {
    'use strict'; 
    return function(target) {
    return $.widget('mage.productListToolbarForm', $.mage.productListToolbarForm, {        
        _processSelect: function (event) {
            var optionvalue =  event.currentTarget.options[event.currentTarget.selectedIndex].value;
            var optionvalueParts = optionvalue.split("&");
            this.mychangeUrl(
                event.data.paramName,
                optionvalueParts[0],
                event.data.default,
                optionvalueParts[1]
            );
          },
          mychangeUrl: function (paramName, paramValue, defaultValue,listdir) {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }
            paramData[paramName] = paramValue;
            console.log(paramData);

            if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                delete paramData[paramName];
            }            
            delete paramData['product_list_dir'];
            paramData = $.param(paramData);
            baseUrl = baseUrl + (paramData.length ? '?' + paramData : '');    
            if(paramData=='product_list_order=created_at') 
            {
                baseUrl = baseUrl + (paramData.length ? '&' + 'product_list_dir=' : '?' + 'product_list_dir=');
            }   
            else 
            {                           
            baseUrl = baseUrl + (paramData.length ? '&' + 'product_list_dir='+listdir : '?' + 'product_list_dir='+listdir);
            }
           // alert(baseUrl);
            baseUrl= decodeURIComponent( baseUrl.replace(/\+/g, '%20'));            
            location.href = baseUrl;
        },         

    });
  }
});