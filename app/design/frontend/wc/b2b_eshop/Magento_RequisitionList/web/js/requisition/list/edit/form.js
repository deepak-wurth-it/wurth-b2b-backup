/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent'
], function (UiComponent) {
    'use strict';

    return UiComponent.extend({
        defaults: {
            value: {}
        },

        /**
         * Init observable properties
         *
         * @returns {exports}
         */
        initObservable: function () {
            this._super()
                .observe('value');
            return this;
        },

        /**
         * Set values
         *
         * @param {Object} data
         * @return void
         */
        setValues: function (data) {
            //default order template name
            let currentdate = new Date();
            let datetime = "wt_" + currentdate.getDate() + "."
                + (currentdate.getMonth()+1)  + "."
                + currentdate.getFullYear() + " "
                + currentdate.getHours() + ":"
                + currentdate.getMinutes() + ":"
                + currentdate.getSeconds();

            data.name = datetime;
            this.value(data);
        },

        /**
         * Get values
         *
         * @returns {Object}
         */
        getValues: function () {
            return this.value();
        }
    });
});
