define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/abstract'
], function (_, ko, Element) {
    'use strict';
    
    return Element.extend({
        defaults: {
            template: 'report/toolbar/filter/field',

            links: {
                value: '${ $.provider }:params.filters[${ $.column }]'
            },
            
            listens: {}
        }
    });
});
