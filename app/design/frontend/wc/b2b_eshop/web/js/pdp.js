define([
        "jquery",
        "mage/url"
    ],

    function ($) {
        "use strict";

        return function qtyincrement() {

            $(document).ready(function() {  
                var incrementPlus;
                var incrementMinus;
                var buttonPlus  = $(".increaseQty");
                var buttonMinus = $(".decreaseQty");
                var incrementPlus = buttonPlus.click(function() {
                    var $n = $(".qty")
                    $n.val(Number($n.val())+1 );
                });
                
                var incrementMinus = buttonMinus.click(function() {
                        var $n = $(".qty")
                    var amount = Number($n.val());
                    if (amount > 1) {
                        $n.val(amount-1);
                    }
                });
                
                });

           
        }
    },

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
