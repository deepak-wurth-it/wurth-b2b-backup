define(
    [
        'jquery',
        'underscore'
    ],
    function ($, _) {
        'use strict';

        var mixin = _.extend({
            updateImageConfig: function (item) {
                if (item.extension_attributes && item.extension_attributes.amasty_promo) {
                    var imageData = item.extension_attributes.amasty_promo;
                    window.checkoutConfig.imageData[item.item_id] = {
                        alt   : imageData.image_alt,
                        height: imageData.image_height,
                        src   : imageData.image_src,
                        width : imageData.image_width
                    };
                }
            },
            getSrc: function (item) {
                if (!this.imageData[item.item_id]) {
                    this.updateImageConfig(item);
                }
                return this._super();
            },
            getWidth: function (item) {
                if (!this.imageData[item.item_id]) {
                    this.updateImageConfig(item);
                }
                return this._super();
            },
            getHeight: function (item) {
                if (!this.imageData[item.item_id]) {
                    this.updateImageConfig(item);
                }
                return this._super();
            },
            getAlt: function (item) {
                if (!this.imageData[item.item_id]) {
                    this.updateImageConfig(item);
                }
                return this._super();
            }
        });
        return function (target) {
            return target.extend(mixin);
        };
    }
);
