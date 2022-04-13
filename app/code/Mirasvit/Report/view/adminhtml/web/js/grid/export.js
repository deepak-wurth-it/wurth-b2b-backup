define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/grid/export'
], function (_, utils, $t, Export) {
    'use strict';

    return Export.extend({
        defaults: {
            imports: {
                params: '${ $.provider }:params'
            }
        },

        getParams: function () {
            var result = this.params;

            result['selected'] = false;
            
            return result;
        }
    });
});
