define([
  "jquery",
  "mage/url"
], 
function($,urlBuilder) {
  "use strict";
  return function(config) {
  //console.log(config);
  var GetMultiProductPriceUrl = urlBuilder.build('/wcbcatalog/ajax/GetMultiProductPrice');
  var GetMultiProductStockUrl = urlBuilder.build('/wcbcatalog/ajax/GetMultiProductStock');

  $.ajax({
               
                type: "POST",
                url: GetMultiProductPriceUrl,
                data: {skus:config.listPids},
                cache: false,
                async: false,
                success: function(result)
                {   // $('#price-container #price').html(result);                        
                   // alert();
                    //console.log(JSON.parse(result.success));


                    if (result) {
                        var i;
                        var itemNo,price;
                        for (i = 0; i < result.length; ++i) {
                            console.log(result[i]);
                            itemNo = result[i]['Item No'];
                            price = result[i]['Suggested Price'];
                            if(itemNo && price){
                             $('#price_'.itemNo).html(price);
                            }
                        }
                       
                    } else {
                       
                    }
                }
            });
            
            
            $.ajax({
                type: "POST",
                url: GetMultiProductStockUrl,
                data: {skus:config.listPids},
                cache: false,
                async: false,
                success: function(result)
                {    //$('#price-container #price').html(result);
                    if (result) {
                        var i;
                        var itemNo,quantity;
                        for (i = 0; i < result.length; ++i) {
                            console.log(result[i]);
                            itemNo = result[i]['Item No'];
                            quantity = result[i]['Available Quantity'];
                            if(itemNo && quantity){
                             $('#stock_'.itemNo).html(quantity);
                            }
                        }
                        
                    } else {
                       
                    }
                }
            });
        };

});
