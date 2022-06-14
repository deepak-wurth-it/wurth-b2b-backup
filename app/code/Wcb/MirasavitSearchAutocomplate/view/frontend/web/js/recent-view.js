define(['jquery',
        'uiComponent',
        'ko',
        'mage/url'
    ], function ($, Component, ko, urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Wcb_MirasavitSearchAutocomplate/recent-view',
                recentItems: []
            },
            initialize: function () {
                this._super();
                // this.getRecentProductsData();
            },
            getRecentViewProduct: function () {
                let mageStorage = window.localStorage.getItem('mage-cache-storage');
                let recentViewProductStorage = window.localStorage.getItem('recently_viewed_product');
                let productIds = [];

                // getting product with login
                if (mageStorage) {
                    mageStorage = JSON.parse(mageStorage);
                    if (mageStorage.recently_viewed_product) {
                        if (mageStorage.recently_viewed_product.items) {
                            productIds = Object.keys(mageStorage.recently_viewed_product.items);
                        }
                    }
                }

                // getting product guest
                if (recentViewProductStorage) {
                    recentViewProductStorage = JSON.parse(recentViewProductStorage);
                    for (let id in recentViewProductStorage) {
                        productIds.push(recentViewProductStorage[id].product_id);
                    }
                }
                return productIds;
            },
            getRecentProductsData: function () {
                let recentAllProducts = this.getRecentViewProduct();
                if(recentAllProducts.length == 0) {
                    return;
                }
                recentAllProducts = recentAllProducts.slice(-6);// get last five product

                let recentProductUrl = urlBuilder.build('wcbsearchautocomplete/ajax/getrecentproduct');
                let self = this;
                $.ajax({
                    url: recentProductUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: { productIds: recentAllProducts },
                    async: false,
                    success: function(response) {
                        if(response.success) {
                            self.recentItems = response.data;
                        }
                    }
                });
                this.recentItems = self.recentItems;
            }
        });
    }
);
