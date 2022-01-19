define(['jquery', 'Amasty_Promo/js/popup'], function ($) {
    'use strict';

    var mixin = {

        /**
         * @param {couponApplyListResult} response
         * @returns {void}
         */
        onSuccess: function (response) {
            this._super();

            if (!response.is_quote_items_changed) {
                $('[data-role=ampromo-overlay]').ampromoPopup('reload');
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
