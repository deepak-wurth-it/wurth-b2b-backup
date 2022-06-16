define([
    "jquery",
    "mage/url"
], function ($, urlBuilder) {
    "use strict";
    return {

        GetMultiProductPriceAndStock: function (listPids) {
            let getMultiPriceAndStockDataUrl = urlBuilder.build('/wcbsearch/index/GetMultiPriceAndStockData');
            let self = this;
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
                                    $('#suggestedSalesPriceInclDiscAsTxtP_' + itemNo).html(productPrice).show();
                                    $("#price_soap_" + itemNo).show();
                                    $("#price_loader_" + itemNo).hide();
                                }
                            });
                        }
                        //Stock Changes
                        if(result.stockData){
                            let stockResult = JSON.parse(result.stockData);
                            $(stockResult).each(function (index, element) {
                                let itemNo = element.ItemNo.replace(/\s+/g, "");
                                let availableDate = element.AvailableonDate.split(".").reverse().join(".");// reverse date
                                let days = self.getDifferentDays(result.currentDate, availableDate);

                                $(".qtyinfo_" + itemNo).attr("data-availQty", element.AvailableQuantity)
                                $(".qtyinfo_" + itemNo).attr("data-availDate", availableDate)
                                $(".delivery_day_"  + itemNo).html(days);
                                self.updateStockDaysAndColor(itemNo);
                            });
                        }
                    }
                }
            });
        },
        getDifferentDays: function(currentDate, availableDate){
            let daysDiff = '';
            if(currentDate && availableDate){
                let date1 = new Date(currentDate);
                let date2 = new Date(availableDate);
                let start = Math.floor(date1.getTime() / (3600 * 24 * 1000)); //days as integer from..
                let end = Math.floor(date2.getTime() / (3600 * 24 * 1000)); //days as integer from..
                daysDiff = end - start; // exact dates
            }
            return daysDiff;
        },
        updateStockDaysAndColor: function(itemNo){
            if($(".qtyinfo_" + itemNo).length == 0){
                return [];
            }
            $(".boxcolor_"  + itemNo).removeClass("greenBox yellowBox blueBox redBox");
            $(".boxvan_"  + itemNo).removeClass("blueVan redVan");

            let qty = $(".qtyinfo_" + itemNo).val();
            let mimimumQty = $(".qtyinfo_" + itemNo).attr('data-mimimumqty');
            let totalQty = parseInt(qty) * parseInt(mimimumQty);
            let availabelQty = $(".qtyinfo_" + itemNo).attr("data-availQty");
            let boxColor = '';
            let vanColor = '';

            if (totalQty < availabelQty) {
                boxColor = 'greenBox';
                $(".delivery_day_"  + itemNo).hide();
            }
            if (totalQty == availabelQty) {
                boxColor = 'yellowBox';
                $(".delivery_day_"  + itemNo).hide();
            }
            if (totalQty > availabelQty) {
                boxColor = 'blueBox';
                vanColor = 'blueVan';
                $(".delivery_day_"  + itemNo).show();
            }
            if (availabelQty == 0) {
                boxColor = 'redBox';
                vanColor = 'redVan';
                $(".delivery_day_"  + itemNo).show();
            }
            $(".boxcolor_"  + itemNo).addClass( boxColor );
            $(".boxvan_"  + itemNo).addClass( vanColor );
        }
    };
});
