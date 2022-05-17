require([
    'jquery',
    'wcbglobal'
], function($,wcbglobal) {
    'use strict';

    $(function() { // document.ready shorthand
        console.log('web/js/app.js loaded');
         console.log(wcbglobal.isLogin());
    });
});
