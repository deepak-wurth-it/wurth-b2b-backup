require([
    'jquery',
    'priceBox',
    'Magento_Catalog/js/price-utils'
], function ($, priceBox, priceUtils) {

    $.widget('mage.ampromoPriceUpdater', {
        options: {
            productId: '',
            priceConfig: ''
        },

        _init: function () {
            var self = this,
                dataPriceBoxSelector = '.price-box-' + this.options.productId,
                dataProductIdSelector = '[data-product-id=' + this.options.productId + ']',
                priceBoxes = $(dataPriceBoxSelector + dataProductIdSelector);

            priceBoxes = priceBoxes.filter(function (index, elem) {
                return !$(elem).find('.price-from').length;
            });

            priceBoxes.priceBox({
                'productId': this.options.productId,
                'priceConfig': this.options.priceConfig
            });

            $('#giftcard-amount-' + this.options.productId).on("change", function () {
                var productIds = [];

                $('.price-new-price').each(function () {
                    productIds.push($(this).attr('data-product-id'));
                });

                productIds.forEach(function (item) {
                    var giftAmount = $('#giftcard-amount-' + item + ' :selected');
                    if (giftAmount.length) {
                        var price = giftAmount[0].text.replace(/[^0-9.]/g, ""),
                            inputPrice = $('#giftcard-amount-input-' + item);

                        inputPrice.keyup(function testKey()
                        {
                            var value = $(this).val();
                            if (!value.match(/^\d+(\.\d{0,2})?$/)) {
                                $(this).val(parseFloat(value).toFixed(2));
                            }
                        });

                        inputPrice.on('input', function () {
                            price = $(this).val();
                            $('.price-base-price.price-box-' + item + ' .price').html(priceUtils.formatPrice(price));
                            $('.price-new-price.price-box-' + item).trigger('reloadPrice');
                        });

                        if ($('#giftcard-amount-' + item).val() == 'custom') {
                            var floatPrice = parseFloat(inputPrice.val()),
                                inputMinValue,
                                inputMaxValue,
                                _ = undefined;

                            inputPrice.val(floatPrice.toFixed(2));
                            price = inputPrice.val();
                            $('.price-base-price.price-box-' + item + ' .price').html(priceUtils.formatPrice(price));

                            if (inputPrice.attr('min') && inputPrice.attr('max')) {
                                inputMinValue = Number(inputPrice.attr('min').replace(/\D+/g,""))/100;
                                inputMaxValue = Number(inputPrice.attr('max').replace(/\D+/g,""))/100;

                                inputPrice.attr('min', inputMinValue);
                                inputPrice.attr('max', inputMaxValue);
                                inputPrice.on('change', self.checkInputPrice.bind(_, inputMinValue, inputMaxValue, item));
                            } else if (inputPrice.attr('max')) {
                                inputMaxValue = Number(inputPrice.attr('max').replace(/\D+/g,""))/100;

                                inputPrice.attr('max', inputMaxValue);
                                inputPrice.attr('min', 0);
                                inputPrice.on('change', self.checkInputPrice.bind(_, _, inputMaxValue, item));
                            } else if (inputPrice.attr('min')) {
                                inputMinValue = Number(inputPrice.attr('min').replace(/\D+/g,""))/100;

                                inputPrice.attr('min', inputMinValue);
                                inputPrice.on('change', self.checkInputPrice.bind(_, inputMinValue, _, item));
                            }
                        }

                        if ($('#giftcard-amount-' + item).val() && $('#giftcard-amount-' + item).val() != 'custom') {
                            $('.price-base-price.price-box-' + item + ' .price').html(priceUtils.formatPrice(price));
                        }

                        $('.price-new-price.price-box-' + item).trigger('reloadPrice');
                    }
                })
            });
        },

        checkInputPrice: function (min, max, item) {
            var inputPrice = $('#giftcard-amount-input-' + item),
                priceBox = $('.price-base-price.price-box-' + item + ' .price');

            if (inputPrice.val() < min) {
                inputPrice.val(min);
                priceBox.html(priceUtils.formatPrice(min));
            }

            if (inputPrice.val() > max) {
                inputPrice.val(max);
                priceBox.html(priceUtils.formatPrice(max));
            }

            if (inputPrice.val() < 0) {
                inputPrice.val(0);
                priceBox.html(priceUtils.formatPrice(inputPrice.val(0)));
            }
        }
    });

    return $.mage.ampromoPriceUpdater;
});
