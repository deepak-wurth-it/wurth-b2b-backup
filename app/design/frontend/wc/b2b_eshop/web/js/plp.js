define([
    "jquery",
    "mage/url",
    "accordion"
], function ($,urlBuilder) {
    "use strict";
    return {

        GetMultiProductPrice : function (listPids) {
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
                        var itemNo, price;
                        var finalResult = result.success;

                        $(finalResult).each(function (index, element) {
                            console.log(index, element);
                            itemNo = element['ItemNo'];
                            console.log(itemNo);
                            price = element['SuggestedPrice'];
                            if (itemNo && price) {
                                $('#price_'.itemNo).html(price);
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
        },
        customjs: function () {

            $("#element").accordion({
                multipleCollapsible: true,
                collapsible: true,
                active: false,
                animate: {
                    duration: 400
                }
            });

            $(".itemAdd").click(function () {
                $('html,body').animate({
                        scrollTop: $(".execution").offset().top
                    },
                    'slow');
            });
        }
    };

});
