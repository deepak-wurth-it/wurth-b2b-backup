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
                          console.log(finalResult);
                          for (i = 0; i <= finalResult.length; i++) {
                              console.log(finalResult);
                              itemNo = finalResult[i]['Item No.'];
                              price = finalResult[i]['Suggested Price'];
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
                  {    
                      if (result.success) {
                          var i;
                          var itemNo,quantity;
                          var finalResult = result.success;
                          console.log(finalResult);
                          for (i = 0; i <= finalResult.length; i++) {
                              console.log(finalResult);
                              itemNo = finalResult[i]['Item No.'];
                              quantity = finalResult[i]['Available Quantity'];
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
  