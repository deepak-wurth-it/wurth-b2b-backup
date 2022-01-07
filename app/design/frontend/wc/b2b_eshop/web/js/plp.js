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
                        //console.log(JSON.parse(result.success));

                        //var data  = JSON.parse(result.success);
                        console.log(result.success);return;
                        data.forEach((element) => {
                            console.log(element);
                        });
                       
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
                        //var data  = JSON.parse(result.success);
                        console.log(result.success);return;
                        data.forEach((element) => {
                            console.log(element);
                        });
                        
                    } else {
                       
                    }
                }
            });
        };

});
