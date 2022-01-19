define(
    [
        'underscore',
        'Magento_Checkout/js/model/quote'
    ],
    function (_, quote) {
        'use strict';

        var mixin = _.extend({
            getItemsQty: function () {
                if (quote.totals().items_qty != this.totals.items_qty) {
                    this.totals = quote.totals();
                }
                return this._super();
            }
        });
        return function (target) {
            return target.extend(mixin);
        };
    }
);
