define([
    "jquery",
    "mage/url"
], function ($, urlBuilder) {
    "use strict";
    return {

        GetMultiProductPriceAndStock: function (listPids) {
            let getMultiPriceAndStockDataUrl = urlBuilder.build('/wcbsearch/index/GetMultiPriceAndStockData');
            $.ajax({
                type: "POST",
                url: getMultiPriceAndStockDataUrl,
                data: {
                    skus: listPids
                },
                cache: false,
                async: false,
                success: function (result) {
                    if (result.success) {
                        // Price changes
                        if(result.priceData){
                            let priceResult = JSON.parse(result.priceData);
                            $(priceResult).each(function (index, element) {
                                let productPrice = element.SuggestedPrice;
                                let itemNo = element.ItemNo.replace(/\s+/g, "");
                                if (element.SuggestedDiscount) {
                                    productPrice = element.SuggestedPriceInclDiscount;
                                }
                                if(productPrice){
                                    $('#suggestedSalesPriceInclDiscAsTxtP_' + itemNo).html(productPrice);
                                    $('#suggestedSalesPriceInclDiscAsTxtP_' + itemNo).show();
                                    $("#price_soap_" + itemNo).show();
                                    $("#price_loader_" + itemNo).hide();
                                }
                            });
                        }
                        //Stock Changes
                        /* if(result.stockData){
                             let stockResult = JSON.parse(result.stockData);
                             $(stockResult).each(function (index, element) {
                                 let itemNo = element.ItemNo.replace(/\s+/g, "");

                             });
                         }*/

                    }
                }
            });
        }
    };
});
