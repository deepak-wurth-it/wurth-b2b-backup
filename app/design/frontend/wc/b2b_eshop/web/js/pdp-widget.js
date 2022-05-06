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
                var $n = $(this).prev(".qty")
                $n.val(Number($n.val())+1 );
                var amount = Number($n.val());
                $("#cart-79-qty").attr( 'data-qty',amount );
                if (amount > 1) {
                    buttonMinus.removeClass("button-disable");
                    }
            });

            var incrementMinus = buttonMinus.click(function() {              var $n = $(this).next(".qty")
                var amount = Number($n.val()); 
                if (amount > 1) {                                   
                $("#cart-79-qty").attr( 'data-qty',amount-1 ); 
                    $n.val(amount-1);
                }
                if (amount <= 1) {
                buttonMinus.addClass("button-disable");
                }
                else{
                    buttonMinus.removeClass("button-disable"); 
                }
            });

     },
        });

    return $.mage.qtyIncrementWidget;
});
