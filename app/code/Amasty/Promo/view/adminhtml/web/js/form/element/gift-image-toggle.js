define([
    'underscore',
    'Magento_Ui/js/form/element/single-checkbox'
], function (_, checkbox) {
    'use strict';

    return checkbox.extend({
        defaults: {
            rulesActions: [
                'ampromo_product',
                'ampromo_items',
                'ampromo_cart',
                'ampromo_spent',
                'ampromo_eachn'
            ],
            imports: {
                checkVisibility: '${$.provider}:data.simple_action'
            },
        },

        checkVisibility: function (value) {
            if (_.contains(this.rulesActions, value)) {
                this.visible(true);
            } else {
                this.visible(false);
            }
            return this;
        }
    });
});
