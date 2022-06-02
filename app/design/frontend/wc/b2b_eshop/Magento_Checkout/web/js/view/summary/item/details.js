/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'escaper'
], function (Component, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details',
            allowedTags: ['b', 'strong', 'i', 'em', 'u'],
            prodStockData: '',
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getNameUnsanitizedHtml: function (quoteItem) {
            this.getStockData(quoteItem.item_id);
            var txt = document.createElement('textarea');

            txt.innerHTML = quoteItem.name;

            return escaper.escapeHtml(txt.value, this.allowedTags);
        },

        /**
         * @param {Object} quoteItem
         * @return {String}Magento_Checkout/js/region-updater
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
        getStockData: function(itemId) {
            var itemsData = window.checkoutConfig.quoteItemData;
            self = this;
            itemsData.forEach(function(item) {
                if (item.item_id == itemId) {
                    if(item.stock_data){
                        self.prodStockData = item.stock_data;
                    }
                }
            });
        },
        getStockImageUrl: function($image){
            return require.toUrl('images/stock/' + $image + '.svg');
        },
        getTruckImageUrl: function($image){
            return require.toUrl('images/stock/van-' + $image + '.svg');
        }
    });
});
