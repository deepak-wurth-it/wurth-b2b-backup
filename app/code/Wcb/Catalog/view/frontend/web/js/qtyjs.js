define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($) {
    $('.quantity-plus').click(function () {
        var currentQTY = parseInt($(this).parent().parent().find(".qty-default").val());
        currentQTY = currentQTY + 1;
        $(this).parent().parent().find(".qty-default").val(currentQTY);
    });

    $('.quantity-minus').click(function () {
        var currentQTY = parseInt($(this).parent().parent().find(".qty-default").val());
        currentQTY = currentQTY - 1;
        if (currentQTY > 0) {
            $(this).parent().parent().find(".qty-default").val(currentQTY);
        }
    });            
});