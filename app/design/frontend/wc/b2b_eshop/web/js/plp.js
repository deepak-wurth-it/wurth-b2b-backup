define([
  "jquery",
  "mage/url"
], 
function($,urlBuilder) {
  "use strict";
  return function(config) {
  console.log(config);
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
                    if (result) {
                       
                        //var xmlDoc = $.parseXML(result);
                        //console.log(xmlDoc);
                        //$('#price-container #price').html(xmlDoc);

                        // $('#customer-info').html(result);
                        // $('#customer-info').next('.authorization-link').hide();
                        // $('.customer-menu .authorization-link').html('');
                        // $('.customer-menu .authorization-link').append(signOutLink);
                        // window.isLoggedIn = true;
                    } else {
                        // $('#customer-info').next('.authorization-link').show();
                        // $('.authorization-link').html('');
                        // $('.authorization-link').append(registerLink);
                        // window.isLoggedIn = false;
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
                       
                        //var xmlDoc = $.parseXML(result);
                        //console.log(xmlDoc);
                        //$('#price-container #price').html(xmlDoc);

                        // $('#customer-info').html(result);
                        // $('#customer-info').next('.authorization-link').hide();

                        // $('.customer-menu .authorization-link').html('');
                        // $('.customer-menu .authorization-link').append(signOutLink);
                        // window.isLoggedIn = true;
                    } else {
                        // $('#customer-info').next('.authorization-link').show();
                        // $('.authorization-link').html('');

                        // $('.authorization-link').append(registerLink);
                        // window.isLoggedIn = false;
                    }
                }
            });
        };

});
