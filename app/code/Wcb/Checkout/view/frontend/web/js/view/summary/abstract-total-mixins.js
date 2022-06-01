/**
 * Abstract Total Mixin
 */

define([], function () {
    'use strict';

    /**
     * Mixin for abstract-total UI Component.
     */
    var mixin = {

        /**
         * Show Order Summary in the Shipping Step of Checkout SideBar
         *
         * @return {boolean}
         */
        isFullMode: function () {
            if (!this.getTotals()) {
                return false;
            }
            return true;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
