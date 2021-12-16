define([
  "jquery",
  "mage/url"
], 
function($,urlBuilder) {
  "use strict";

  console.log('Hola');
  var singalproductpriceUrl = urlBuilder.build('/wcbcatalog/ajax/singalproductprice');
  var singalproductstockUrl = urlBuilder.build('/wcbcatalog/ajax/singalproductstock');

  $.ajax({
                type: "POST",
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                url: singalproductpriceUrl,
                data:{'sku': "001 512"},
                success: function(result)
                {    $('#price-container #price').html(result);
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
                dataType: "json",
                contentType: "application/json; charset=utf-8",
                data:{'sku': "899 102310"},
                url: singalproductstockUrl,
                success: function(result)
                {    $('#price-container #price').html(result);
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

});
