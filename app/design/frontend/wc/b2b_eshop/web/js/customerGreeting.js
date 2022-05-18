define([
  "jquery", "Magento_Customer/js/customer-data"
], function($, customerData) {
  "use strict";
  return function (config, element) {
      var firstname = customerData.get('customer')().firstname;
      if (typeof (firstname) === "undefined") {
          customerData.reload('customer');
      }
      var check = setInterval(function () {
          var firstname = customerData.get('customer')().firstname;
          if (firstname) {
              $(element).text('Welcome, ' + firstname);
              clearInterval(check);
          }
      }, 300);
  };
});