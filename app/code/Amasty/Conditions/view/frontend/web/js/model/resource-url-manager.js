define([
    'Magento_Checkout/js/model/resource-url-manager'
], function (resourceUrlManager) {
    'use strict';

    return {
        /**
         * Making url for total estimation request.
         *
         * @param {Object} quote - Quote model.
         * @returns {String} Result url.
         */
        getUrlForTotalsEstimationForNewAddress: function (quote) {
            if (window.checkoutConfig.isNegotiableQuote) {
                var params = {
                        quoteId: quote.getQuoteId()
                    },
                    urls = {
                        'negotiable': '/negotiable-carts/:quoteId/totals-information/?isNegotiableQuote=true'
                    };

                return resourceUrlManager.getUrl(urls, params);
            }

            return resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote);
        },
    };
});
