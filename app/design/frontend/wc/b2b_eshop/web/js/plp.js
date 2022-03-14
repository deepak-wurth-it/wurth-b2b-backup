define([
    "jquery",
    "mage/url",
    "accordion"
], function ($, urlBuilder) {
    "use strict";
    return {

        GetMultiProductPrice: function (listPids) {
            var GetMultiProductPriceUrl = urlBuilder.build('/wcbcatalog/ajax/GetMultiProductPrice');

            $.ajax({

                type: "POST",
                url: GetMultiProductPriceUrl,
                data: {
                    skus: listPids
                },
                cache: false,
                async: false,
                success: function (result) {
                    if (result.success) {
                        var i;
                        var itemNo, price, suggestedPriceAsTxtP, suggestedDiscountAsTxtP, suggestedSalesPriceInclDiscAsTxtP;
                        var finalResult = result.success;

                        $(finalResult).each(function (index, element) {
                            itemNo = element['ItemNo'];
                            itemNo = itemNo.replace(/\s+/g, "");
                            //itemNo = itemNo.replaceAll("^\"|\"$", "");
                            if (itemNo) {

                                suggestedPriceAsTxtP = element.SuggestedPrice;
                               // console.log(suggestedPriceAsTxtP);
                                //if (suggestedPriceAsTxtP) {

                                   // suggestedPriceAsTxtP = suggestedPriceAsTxtP.replaceAll("^\"|\"$", "");
                                //}


                                suggestedDiscountAsTxtP = element.SuggestedDiscount;
                                //if (suggestedDiscountAsTxtP) {

                                   // suggestedDiscountAsTxtP = suggestedDiscountAsTxtP.replaceAll("^\"|\"$", "");
                               // }

                                suggestedSalesPriceInclDiscAsTxtP = element.SuggestedPriceInclDiscount;
                               // if (suggestedSalesPriceInclDiscAsTxtP) {

                                    //suggestedSalesPriceInclDiscAsTxtP = suggestedSalesPriceInclDiscAsTxtP.replaceAll("^\"|\"$", "");
                                //}

                                $('#suggestedPriceAsTxtP' + itemNo).html(suggestedPriceAsTxtP);
                                $('#suggestedDiscountAsTxtP' + itemNo).html(suggestedDiscountAsTxtP);
                                $('#suggestedSalesPriceInclDiscAsTxtP' + itemNo).html(suggestedSalesPriceInclDiscAsTxtP);
                                $("#price_soap" + itemNo).css({
                                    display: "block"
                                });
                                $("#price_loader" + itemNo).css({
                                    display: "none"
                                });
                            }
                        });

                    } else {

                    }
                }
            });
        },
        GetMultiProductStock: function (listPids) {
            var GetMultiProductStockUrl = urlBuilder.build('/wcbcatalog/ajax/GetMultiProductStock');

            $.ajax({
                type: "POST",
                url: GetMultiProductStockUrl,
                data: {
                    skus: listPids
                },
                cache: false,
                async: false,
                success: function (result) {
                    if (result.success) {
                        var i;
                        var itemNo, quantity;
                        var finalResult = result.success;

                        $(finalResult).each(function (index, element) {
                            console.log(index, element);
                            itemNo = element['ItemNo'];
                            quantity = element['AvailableQuantity'];
                            if (itemNo && quantity) {
                                $('#price_'.itemNo).html(quantity);
                            }
                        });

                    } else {

                    }
                }
            });
        }

    };

});
