define([
  "jquery",
  "mage/url"
], 
function($,urlBuilder) {
  "use strict";

  console.log('Hola');
  var updateUrl = urlBuilder.build('/wcbcatalog/ajax/singalproductprice');

  $.ajax({
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                url: updateUrl,
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
