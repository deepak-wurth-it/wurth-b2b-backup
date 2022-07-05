var config = {
    map: {
        '*': {
            'plp': 'js/plp',
            'pdp': 'js/pdp',
            'checkoutjs': 'Magento_Checkout/js/chekout',
            'qtyIncrementWidget': 'js/pdp-widget',
            'plpJsWidget': 'js/plp-widget',
            'dataTables': 'js/jquery.dataTables.min',

        }
    },
    paths: {
        fancybox: 'Plazathemes_InstagramGallery::plazathemes/instagramgallery/js/jquery.fancybox.js'
    },
    shim: {
        fancybox: {
            deps: ['jquery']
        },
        dataTables: {
            deps: ['jquery']
        },
    },

    deps: ['js/app']
};


