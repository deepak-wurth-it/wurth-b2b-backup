define([
        "jquery",
        "mage/url"
    ],

    function ($, urlBuilder) {
        "use strict";
        return function (config) {
            //console.log(config);
            var GetItemEShopSalesPriceAndDisc = urlBuilder.build('/wcbcatalog/ajax/GetItemEShopSalesPriceAndDisc');

            $.ajax({

                type: "POST",
                url: GetItemEShopSalesPriceAndDisc,
                data: {
                    skus: config.pid
                },
                cache: false,
                async: false,
                success: function (result) {
                    if (result.success) {
                        var finalResult = result.success;
                        if(finalResult.suggestedPriceAsTxtP){
                          $('#price_pdp').html(finalResult.suggestedPriceAsTxtP);
                          $("#price_format").css({ display: "block" });
                        }

                        console.log(result.success);
                    } else {

                    }
                }
            });

            

        };

    });
