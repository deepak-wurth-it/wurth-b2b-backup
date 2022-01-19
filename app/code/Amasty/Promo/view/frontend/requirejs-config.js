var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/item/details/thumbnail': {  // Target module
                'Amasty_Promo/js/checkout/sidebar-image-update': true  // Extender module
            },
            'Magento_Checkout/js/view/summary/cart-items': {
                'Amasty_Promo/js/checkout/cart-items-counter-update': true
            },
            'Magento_Theme/js/view/messages' : {
                'Amasty_Promo/js/view/messages' : true
            },
            'Amasty_Coupons/js/model/cart/apply-response-processor': {
                'Amasty_Promo/js/model/coupon-apply-response-processor-mixin' : true
            }
        }
    }
};
