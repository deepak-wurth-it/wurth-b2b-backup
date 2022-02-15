define([
    "jquery",
    "mage/url",
    "jquery/ui"

], function ($, urlBuilder) {
    "use strict";

    return {

        isLogin: function (productCode) {
            var loginCheckUrl = urlBuilder.build('/wcbglobal/customer/logincheck');
            var httpRequest = new XMLHttpRequest();
            httpRequest.open('GET', loginCheckUrl);
            httpRequest.send();
            return httpRequest.response;
        }

    };
});
