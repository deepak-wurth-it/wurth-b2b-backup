define([
    'jquery',
    'jquery/ui'
    ], function($){
        $.widget('mage.plpJsWidget', {
            
           // Optional 
      options: {
        // Define variables
     },

     _create: function() {
        // Init code
        this.customJs();
     },

     customJs: function () {
        console.log('widget load on plp page');   
    },
        });
 
    return $.mage.plpJsWidget;
});