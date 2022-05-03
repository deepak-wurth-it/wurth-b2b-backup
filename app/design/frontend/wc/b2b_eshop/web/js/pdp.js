define([
        "jquery",
        "mage/url",
        "wcbglobal"
    ],
    function ($, urlBuilder, wcbglobal) {
        "use strict";

        return {
            soapPrice: function (productCode) {
                var GetItemEShopSalesPriceAndDisc = urlBuilder.build('/wcbcatalog/ajax/GetItemEShopSalesPriceAndDisc');
                console.log(wcbglobal.isLogin());
                $.ajax({

                    type: "POST",
                    url: GetItemEShopSalesPriceAndDisc,
                    data: {
                        skus: productCode
                    },
                    cache: false,
                    async: false,
                    success: function (result) {
                        if (result.success) {
                            var finalResult = result.success;

                            if (finalResult.suggestedPriceAsTxtP) {
                                $('#suggestedPriceAsTxtP').html(finalResult.suggestedPriceAsTxtP);
                                $('#suggestedDiscountAsTxtP').html(finalResult.suggestedDiscountAsTxtP);
                                $('#suggestedSalesPriceInclDiscAsTxtP').html(finalResult.suggestedSalesPriceInclDiscAsTxtP);
                                $("#price_soap").css({
                                    display: "block"
                                });
                                $("#price_loader").css({
                                    display: "none"
                                });
                            }

                            console.log(result.success);
                        } else {

                        }
                    }

                });
            },
            soapStock: function (productCode) {
                var GetItemAvailabilityOnLocation = urlBuilder.build('/wcbcatalog/ajax/GetItemAvailabilityOnLocation');
                //console.log(wcbglobal.isLogin());
                $.ajax({

                    type: "POST",
                    url: GetItemAvailabilityOnLocation,
                    data: {
                        sku: productCode
                    },
                    cache: false,
                    async: false,
                    success: function (result) {
                        if (result.success) {
                            var finalResult = result.success;

                            if (finalResult.remain_days) {
                               $('#deliverydayP').html(finalResult.remain_days);
                            }

                            console.log(result.success);
                        } else {

                        }
                    }

                });
            }
        };

    });
