define([
    'jquery',
    'jquery/ui'
    ], function($){
        $.widget('mage.qtyIncrementWidget', {
            
           // Optional 
      options: {
        // Define variables
     },

     _create: function() {
        // Init code
        this.qtyIncrement();
     },

     qtyIncrement: function() {
        // Do close modal here 
            var incrementPlus;
            var incrementMinus;
            var buttonPlus  = $(".increaseQty");
            var buttonMinus = $(".decreaseQty");
            var incrementPlus = buttonPlus.click(function() {
                var $n = $(".qty")
                $n.val(Number($n.val())+1 );
            });
            
            var incrementMinus = buttonMinus.click(function() {
                    var $n = $(".qty")
                var amount = Number($n.val());
                if (amount > 1) {
                    $n.val(amount-1);
                }
            });
            
     },
        });
 
    return $.mage.qtyIncrementWidget;
});