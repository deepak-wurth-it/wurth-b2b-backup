define([
        "jquery",
        "mage/url",
        "wcbglobal"
    ],
    function ($, urlBuilder,wcbglobal) {
        "use strict";
        
        return {
            soapPrice: function(productCode) {
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
                    success: function(result) {
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
        
            qtyincrement: function() {
                //alert();
                $(document).ready(function() {
                    var incrementPlus;
                    var incrementMinus;
                    var buttonPlus = $(".increaseQty");
                    var buttonMinus = $(".decreaseQty");
                    var incrementPlus = buttonPlus.click(function() {
                        var $n = $(".qty")
                        $n.val(Number($n.val()) + 1);
                    });
        
                    var incrementMinus = buttonMinus.click(function() {
                        var $n = $(".qty")
                        var amount = Number($n.val());
                        if (amount > 1) {
                            $n.val(amount - 1);
                        }
                    });
        
                });
            }
        
        
        };

    });
