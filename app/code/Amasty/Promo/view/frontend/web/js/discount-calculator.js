define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils'
], function ($, quote, priceUtils) {
    var discountCalculator = {
        update: function (products, item) {
            var priceHtml = $(item.target).parent().find('span[data-price-type="basePrice"]').find('span[class="price"]');
            if (priceHtml.length) {
                var itemPrice = priceHtml.html().replace(/[^.,0-9]/gim, ''),
                    discount = 0,
                    productSku = $(item.target).closest('div.ampromo-item').attr('data-product-sku'),
                    product = null;
                itemPrice = itemPrice.replace(',','.');
                if (productSku in products) {
                    product = products[productSku];
                    var promoDiscount = String(product['discount']['discount_item']),
                        minimalPrice = product['discount']['minimal_price'];
                    if (promoDiscount === "" || promoDiscount === "100%" || promoDiscount === "null") {
                        discount = itemPrice;
                    } else if (promoDiscount.indexOf("%") !== -1) {
                        discount = this.getPercentDiscount(itemPrice, promoDiscount);
                    } else if (promoDiscount.indexOf("-") !== -1) {
                        discount = this.getFixedDiscount(itemPrice, promoDiscount);
                    } else {
                        discount = this.getFixedPrice(itemPrice, promoDiscount);
                    }

                    discount = this.getDiscountAfterMinimalPrice(minimalPrice, itemPrice, discount);
                    $(item.target).parent().find('span[data-price-type="newPrice"]').find('span[class="price"]')[0].innerHTML
                        = priceUtils.formatPrice(itemPrice - discount, quote.getPriceFormat());
                }
            }
        },

        getPercentDiscount: function (itemPrice, promoDiscount) {
            var percent = parseFloat(promoDiscount.replace("%", "", promoDiscount));

            return itemPrice * percent / 100;
        },

        getFixedDiscount: function (itemPrice, promoDiscount) {
            var discount = Math.abs(promoDiscount);
            if (discount > itemPrice) {
                discount = itemPrice;
            }

            return discount;
        },

        getFixedPrice: function (itemPrice, promoDiscount) {
            var discount = itemPrice - parseFloat(promoDiscount);
            if (discount < 0) {
                discount = 0;
            }

            return discount;
        },

        getDiscountAfterMinimalPrice: function (minimalPrice, itemPrice, discount) {
            itemPrice = Number(itemPrice);
            minimalPrice = Number(minimalPrice);
            if (itemPrice > minimalPrice && itemPrice - discount < minimalPrice) {
                discount = itemPrice- minimalPrice;
            }

            return discount;
        }
    };

    return discountCalculator;
});
