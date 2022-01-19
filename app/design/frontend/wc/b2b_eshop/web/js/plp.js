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
                  {  
                      if (result.success) {
                          var i;
                          var itemNo,price;
                          var finalResult = result.success;
                         
                         $(finalResult).each(function( index, element ) {
                              console.log(index, element);
                              itemNo = element['ItemNo'];
                              console.log(itemNo);
                              price = element['SuggestedPrice'];
                              if(itemNo && price){
                                $('#price_'.itemNo).html(price);
                              }
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
                  {    
                      if (result.success) {
                          var i;
                          var itemNo,quantity;
                          var finalResult = result.success;
                        
                          $(finalResult).each(function( index, element ) {
                            console.log(index, element);
                            itemNo = element['ItemNo'];
                            quantity = element['AvailableQuantity'];
                            if(itemNo && quantity){
                              $('#price_'.itemNo).html(quantity);
                            }
                      });
                          
                      } else {
                         
                      }
                  }
              });
          };
  
  });
  