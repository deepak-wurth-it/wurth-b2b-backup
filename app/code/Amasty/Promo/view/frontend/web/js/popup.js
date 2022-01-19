define([
    'jquery',
    'underscore',
    'Amasty_Promo/js/discount-calculator',
    'Amasty_Base/vendor/slick/slick.min',
    'priceOptions',
    'Magento_Ui/js/modal/modal'
], function ($, _, discount) {
    'use strict';

    var RULE_TYPE_ONE = '1',
        RULE_TYPE_ALL = '0';

    $.widget('mage.ampromoPopup', {
        options: {
            slickSettings: {},
            sourceUrl: '',
            uenc: '',
            commonQty: 0,
            products: {},
            promoSku: {},
            formUrl: '',
            selectionMethod: 0,
            giftsCounter: 0,
            autoOpenPopup: 0,
            sliderItemsCount: 3,
            sliderWidthItem: 280,
            reloading: false,
            loading: true,
            delay: 1000
        },

        isSliderInitialized: false,
        isMultipleMethod: 1,
        isEnableGiftsCounter: 1,
        isOpen: false,
        initPopup: false,
        openPopupAfterLoadTotals: false,

        /**
         * @private
         * @returns {void}
         */
        _create: function () {
            var products = $.extend({}, this.options.products);

            this.autoOpen = this.options.autoOpenPopup || window.location.hash === '#choose-gift';
            this.delay = this.options.delay;
            this.cancelLoading = true;
            this.options.promoSku = Object.prototype.hasOwnProperty.call(products, 'triggered_products') ?
                products['promo_sku'] : this.options.promoSku;
            this.options.products = Object.prototype.hasOwnProperty.call(products, 'triggered_products') ?
                products['triggered_products'] : null;
        },

        /**
         * @private
         * @returns {void}
         */
        _init: function () {
            this._initElements();
            this._initHandles();
            this._loadItems();
        },

        /**
         * @private
         * @returns {void}
         */
        _initElements: function () {
            this.message = $('[data-ampopup-js="message"]');
            this.container = $('[data-ampromo-js="popup-container"]', this.element);
            this.titlePopup = $('[data-ampromo-js="popup-title"]', this.element);

            /**
             * @todo Change selector in CS ex. [data-ampromo-js]
             */
            this.showPopup = $('[data-role="ampromo-popup-show"]');
        },

        /**
         * @private
         * @returns {void}
         */
        _initHandles: function () {
            var self = this;

            self.element.on('mousedown', function (event) {
                var target = $(event.target);

                if (target.data('role') === 'ampromo-overlay') {
                    event.stopPropagation();
                    self.hide();
                }
            });
            self.showPopup.on('click', $.proxy(self.show, self));
            $(document).on('click', '[data-role="ampromo-popup-hide"]', $.proxy(self.hide, self));
            $(document).on('reloadPrice', function (item) {
                var products = $.extend({}, self.options.products);

                self.options.promoSku = Object.prototype.hasOwnProperty.call(products, 'triggered_products') ?
                    products['promo_sku'] : self.options.promoSku;
                discount.update(self.options.promoSku, item);
            });
        },

        /**
         * @returns {void}
         */
        show: function () {
            if (!this.isSliderInitialized) {
                this._initContent();
            }

            if (this.options.selectionMethod === this.isMultipleMethod) {
                this.checkAddButton();
            }

            if (!this.isOpen) {
                this.element.addClass('-show');
                this.isOpen = true;
            }
        },

        /**
         * @returns {void}
         */
        checkAddButton: function () {
            var self = this,
                stateCheckbox = false,
                stateInputs = true;

            this.productSelects.each(function () {
                if ($(this).children().prop('checked')) {
                    stateCheckbox = true;
                }
            });

            $.each(this.element.find('[data-am-js=ampromo-qty-input]'), function () {
                if ($(this).val() < 0 && !$(this).prop('disabled')) {
                    stateInputs = false;

                    return false;
                }

                return true;
            });

            if (stateCheckbox && stateInputs) {
                self.addToCartDisableOrEnable(false);
            } else {
                self.addToCartDisableOrEnable(true);
            }
        },

        /**
         * @param {Boolean} state
         * @returns {void}
         */
        addToCartDisableOrEnable: function (state) {
            this.element.find('[data-role=ampromo-item-buttons] button').attr('disabled', state);
        },

        /**
         * @returns {void}
         */
        hide: function () {
            this.isOpen = false;
            this.isClose = true;
            this.element.removeClass('-show');
        },

        /**
         *
         * @returns {void}
         */
        _loadItems: function () {
            var onSuccess = this._success.bind(this),
                config = {
                    url: this.options.sourceUrl,
                    method: 'GET',
                    data: {
                        uenc: this.options.uenc
                    },
                    success: onSuccess
                };

            $.ajax(config);
        },

        /**
         * @param {JSON} response
         * @param {String} response.popup
         * @param {Object} response.products
         * @returns {void}
         * @private
         */
        _success: function (response) {
            var isReload = !_.isEmpty(this.response) &&
                this.response.popup === response.popup &&
                this.response.products['common_qty'] === response.products['common_qty'];

            if (isReload) {
                this._initLoading();

                return;
            }

            this.response = $.extend({}, response);
            this.isSliderInitialized = false;
            this.container.html(response.popup);
            this.options.products = response.products;
            this.itemsCount = this.container.children().length;
            this.hasContent = !!this.itemsCount;

            if (this.hasContent) {
                this._initContent();
                this.container.trigger('contentUpdated');
                this._calcWidthTitle();
            }

            this._toggleMessage();

            if (this.response.autoOpenPopup) {
                this.autoOpen = true;
                this.isClose = false;
            }

            if (this.autoOpen) {
                if (!this.initPopup &&
                    this.hasContent &&
                    !this.isClose
                ) {
                    this.show();
                    this.initPopup = true;
                }

                this._trigger('init.ampopup');
            }
        },

        /**
         * @returns {void}
         */
        _initContent: function () {
            var products = $.extend({}, this.options.products);

            this.options.commonQty = 'common_qty' in products ?
                products['common_qty'] : this.options.commonQty;
            this.options.promoSku = Object.prototype.hasOwnProperty.call(products, 'triggered_products') ?
                products['promo_sku'] : this.options.promoSku;
            this.options.products = Object.prototype.hasOwnProperty.call(products, 'triggered_products') ?
                products['triggered_products'] : products;
            this._initElementsContent();
            this._initLoading();
            this._initOptions();
            this._initSlider();

            $('.ampromo-items-form').mage('validation');
        },

        /**
         * @private
         * @returns {void}
         */
        _initLoading: function () {
            var loadingElem,
                self = this,
                onRemoveLoading = self._removeLoading.bind(self);

            if (!self.hasContent) {
                return;
            }

            if (!self.options.loading) {
                self._removeLoading();

                return;
            }

            loadingElem = $('<div>', {
                class: 'ampromo-loading -show'
            });
            self.container.append(loadingElem);
            self.productList.addClass('-loading');
            self.loader = _.debounce(function () {
                if (self.cancelLoading) {
                    onRemoveLoading();
                }
            }, self.delay);
            self.loader();
        },

        /**
         * @private
         * @returns {void}
         */
        _removeLoading: function () {
            this.productList.removeClass('-loading');
            $('.ampromo-loading').removeClass('-show');
        },

        /**
         * @private
         * @returns {void}
         */
        _initElementsContent: function () {
            this.gallery = this.element.find('[data-role="ampromo-gallery"]');
            this.productList = this.element.find('[data-ampromo-js="popup-products"]');
            this.productSelects = this.element.find('[data-role="ampromo-product-select"]');
            this.productInputsQty = this.element.find('[data-am-js=ampromo-qty-input]');
            this.promoItems = this.element.find('[data-role=ampromo-item]');
        },

        /**
         * @returns {void}
         */
        _initOptions: function () {
            if (this.options.selectionMethod === this.isMultipleMethod) {
                this.initMultipleProductAdd();
            } else {
                this.initOneByOneProductAdd();
            }

            if (this.options.giftsCounter === this.isEnableGiftsCounter) {
                this.initProductQtyState();
                this.addCounterToPopup();

                if (this.options.selectionMethod === this.isMultipleMethod) {
                    this.addToCartDisableOrEnable(true);
                }
            }
        },

        /**
         * @returns {void}
         */
        initMultipleProductAdd: function () {
            var self = this,
                addButton = this.element.find('[data-am-js="ampromo-add-button"]'),
                onInitHandlersInputQty = self._initHandlersInputQty.bind(self),
                onInitHandlersProducts = self._initHandlersProducts.bind(self);

            this.productSelects.each(function () {
                var selectElem = $(this);

                selectElem.children().off('click').on('click', self._choiceProduct);
            });
            this.productInputsQty.each(onInitHandlersInputQty);
            this.promoItems.each(onInitHandlersProducts);
            addButton.off('click').on('click', function () {
                self.sendForm();
            });

            self.checkAddButton();
        },

        /**
         * @private
         * @returns {void}
         */
        _choiceProduct: function () {
            var checkbox = $(this),
                value = !checkbox.prop('checked');

            checkbox.prop('checked', value);
        },

        /**
         * @param {Number} index
         * @param {Element} element
         * @private
         * @returns {void}
         */
        _initHandlersInputQty: function (index, element) {
            var self = this,
                qtyInput = $(element),
                onDragEvent = this._isDragEventInit.bind(this);

            qtyInput.keyup(function () {
                self.checkAddButton();
                $.validator.validateSingleElement(this);
            });

            qtyInput.mouseenter(function () {
                if (onDragEvent()) {
                    self.gallery.slick('slickSetOption', 'draggable', false, false);
                }
            });

            qtyInput.mouseleave(function () {
                if (onDragEvent()) {
                    self.gallery.slick('slickSetOption', 'draggable', true, true);
                }
            });
        },

        /**
         * @private
         * @returns {Boolean}
         */
        _isDragEventInit: function () {
            var slickSettingsCurrent = this.gallery[0].slick.options,
                itemsCount = this.gallery.data('count'),
                slidesToShowCurrent = slickSettingsCurrent.slidesToShow;

            return itemsCount > slidesToShowCurrent;
        },

        /**
         * @param {Number} index
         * @param {Element} element
         * @private
         * @returns {void}
         */
        _initHandlersProducts: function (index, element) {
            var promoItems = $(element),
                self = this;

            promoItems
                .off('mousedown')
                .on('mousedown', function () {
                    self.slickStartTransform = $('.slick-track').css('transform');
                })
                .off('mouseup')
                .on('mouseup', {
                    'context': self
                }, self._onClickProduct);
        },

        /**
         * @param {Object} event
         * @private
         * @returns {void}
         */
        _onClickProduct: function (event) {
            var self = event.data.context,
                promoItem = $(this),
                excludedTags = ['INPUT', 'SELECT', 'LABEL', 'TEXTAREA'],
                currentAllowedQty = self.options.commonQty,
                isCheckbox,
                isInputQty,
                isAllowedTags,
                checkbox,
                qtyInput;

            self.slickEndTransform = $('.slick-track').css('transform');

            if (self.slickStartTransform !== self.slickEndTransform) {
                return;
            }

            checkbox = promoItem.find('[data-role=ampromo-product-select] input');
            qtyInput = promoItem.find('[data-am-js=ampromo-qty-input]');
            isCheckbox = event.target === checkbox[0];
            isInputQty = event.target === qtyInput[0] && qtyInput.prop('disabled');
            isAllowedTags = excludedTags.indexOf(event.target.tagName) === -1 &&
                excludedTags.indexOf(event.target.parentElement.tagName) === -1;

            if (self.options.giftsCounter !== self.isEnableGiftsCounter) {
                currentAllowedQty = currentAllowedQty - self.getSumQtys();
            }

            if (isCheckbox || isInputQty || isAllowedTags) {
                if (currentAllowedQty > 0 || promoItem.hasClass('-selected')) {
                    promoItem.toggleClass('-selected');
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    self.checkboxState(checkbox);
                    self.checkAddButton();
                }
            }
        },

        /**
         * @returns {void}
         */
        sendForm: function () {
            var formData = this._prepareFormData(),

                /**
                 * @returns {void}
                 */
                onSuccess = function () {
                    window.location.reload();
                };

            this.addToCartDisableOrEnable(true);
            $.ajax({
                type: 'POST',
                url: this.options.formUrl,
                data: {
                    uenc: this.options.uenc,
                    data: formData
                },
                success: onSuccess
            });
        },


        _prepareFormData: function () {
            var formData = [],
                re = /\[(.*?)\]/,
                form = this.element.find('[data-ampromo-js="form-item"]');

            form.each(function (index, element) {
                var $element = $(element),
                    propertyTemp = {},
                    tmpBundleOpt = {},
                    tmpBundleQtyOpt = {},
                    tmpBundleMultiSelect = [];


                if (!$element.find('input[type="checkbox"]').prop('checked')) {
                    return true;
                }

                formData[index] = $element.serializeArray().reduce(function (obj, item) {
                    var key,
                        keyName,
                        selectKey,
                        tmpBundleCheckbox = {},
                        links = [];

                    if (item.name.indexOf('super_attribute') >= 0 || item.name.indexOf('options') >= 0) {
                        key = item.name.match(re)[1];
                        keyName = item.name.indexOf('super_attribute') >= 0 ? 'super_attribute' : 'options';

                        propertyTemp[key] = item.value;
                        obj[keyName] = propertyTemp;
                    } else if (item.name.indexOf('bundle_option_qty') >= 0) {
                        key = item.name.match(re)[1];
                        keyName = 'bundle_option_qty';
                        tmpBundleQtyOpt[key] = item.value;
                        obj[keyName] = tmpBundleQtyOpt;
                    } else if (item.name.indexOf('bundle_option') >= 0) {
                        key = item.name.match(re)[1];
                        keyName = 'bundle_option';
                        if (/\[]$/.test(item.name)) {
                            if (tmpBundleMultiSelect[key] === undefined) {
                                tmpBundleMultiSelect[key] = [];
                            }

                            tmpBundleMultiSelect[key].push(item.value);
                            tmpBundleOpt[key] = tmpBundleMultiSelect[key];
                        } else if (/\[(.*?)\]\[(.*?)\]$/.test(item.name)) {
                            selectKey = item.name.match(/\]\[(.*?)\]/)[1];
                            tmpBundleCheckbox[selectKey] = item.value;
                            if (tmpBundleOpt[key] === undefined) {
                                tmpBundleOpt[key] = {};
                            } else if (tmpBundleOpt[key][selectKey] === undefined) {
                                tmpBundleOpt[key][selectKey] = {};
                            }

                            tmpBundleOpt[key][selectKey] = item.value;
                        } else {
                            tmpBundleOpt[key] = item.value;
                        }

                        obj[keyName] = tmpBundleOpt;
                    } else if (item.name.indexOf('links[]') >= 0) {
                        links.push(item.value);
                        obj.links = links;
                    } else {
                        obj[item.name] = item.value;
                    }

                    return obj;
                }, {});

                formData[index]['rule_id'] = $element.find('.ampromo-qty').attr('data-rule');

                return true;
            });

            return formData;
        },

        /**
         * @returns {void}
         */
        initOneByOneProductAdd: function () {
            var onInitHandlersOneProductAdd = this.initHandlersOneProductAdd.bind(this);

            this.promoItems.each(onInitHandlersOneProductAdd);
        },

        /**
         * @param {Number} index
         * @param {Element} element
         * @private
         * @returns {void}
         */
        initHandlersOneProductAdd: function (index, element) {
            var promoItem = $(element);

            if (promoItem.find('.ampromo-options .fieldset .field').length !== 0) {
                promoItem.find('.tocart').off('click').on('click', function () {
                    $('.ampromo-item.-selected').removeClass('-selected');
                    promoItem.addClass('-selected');
                });
            }
        },

        /**
         * @returns {void}
         */
        initProductQtyState: function () {
            var self = this;

            this._setQtys();
            this.productInputsQty.each(function (index, element) {
                $(element).off('keyup').on('keyup', function () {
                    var qtyInput = $(this);

                    self._changeQty(qtyInput);
                    self.checkAddButton();
                    $.validator.validateSingleElement(this);
                });
            });
        },

        /**
         * @private
         * @returns {void}
         */
        _setQtys: function () {
            this._updateCommonQty();
            this._updateProductLeftQty();
        },

        /**
         * @private
         * @returns {void}
         */
        _updateCommonQty: function () {
            this.element.find('[data-role=ampromo-popup-common-qty]').html(this.options.commonQty);
        },

        /**
         * @private
         * @returns {void}
         */
        _updateProductLeftQty: function () {
            var self = this;

            $.each(this.options.products, function (ruleId, rulesData) {
                var id = ruleId,
                    ruleType = rulesData['rule_type'];

                $.each(rulesData.sku, function (key, value) {
                    var productDomBySku = self.getProductDomBySku(key),
                        qtyInput,
                        qtyInt = +value.qty;

                    if (productDomBySku) {
                        productDomBySku.find('[data-ampromo-js="qty-left-text"]').html(qtyInt);
                        qtyInput = productDomBySku.find('[data-am-js="ampromo-qty-input"]');

                        if (qtyInput.length) {
                            qtyInput.attr('data-rule', id);
                            qtyInput.attr('data-rule-type', ruleType);
                        }
                    }
                });
            });
        },

        /**
         * @param {String} sku
         * @returns {jQuery}
         */
        getProductDomBySku: function (sku) {
            return this.getProductDom('data-product-sku', sku);
        },

        /**
         * @param {String} attribute
         * @param {String} value
         * @returns {jQuery|HTMLElement}
         */
        getProductDom: function (attribute, value) {
            var result = false;

            this.promoItems.each(function () {
                var promoItem = $(this),
                    attrValue = promoItem.attr(attribute);

                if (value === attrValue) {
                    result = promoItem;
                }
            });

            return result;
        },

        /**
         *
         * @param {jQuery} elem
         * @returns {void}
         */
        _changeQty: function (elem) {
            var value = elem.val(),
                newQty = value === '' ? 0 : parseInt(value, 10),
                productSku = this.getProductSku(elem),
                ruleId = this.getRuleId(elem),
                ruleType = this.getRuleType(elem);

            this.updateValues(newQty, productSku, ruleId, ruleType, elem);
            this._setQtys();
        },

        /**
         * @param {Number} newQty
         * @param {String} productSku
         * @param {String} ruleId
         * @param {String} ruleType
         * @param {jQuery} elem
         * @returns {void}
         */
        updateValues: function (newQty, productSku, ruleId, ruleType, elem) {
            var self = this,
                newValue = 0,
                qty = newQty,
                countOfThisFreeItem = 0,
                countOfRulesFreeItem = 0,
                sumQtyByRuleId,
                itemRuleType;

            if (!this.isValidNumber(qty)) {
                return;
            }

            $.each(this.options.products, function (itemRuleId, ruleData) {
                $.each(ruleData.sku, function (skuId, skuProps) {
                    sumQtyByRuleId = self.getSumQtysByRuleId()[itemRuleId];
                    countOfThisFreeItem = +self.options.promoSku[productSku].qty;

                    if (itemRuleId !== ruleId) {
                        return;
                    }

                    if (ruleType === RULE_TYPE_ONE) {
                        if (sumQtyByRuleId > countOfThisFreeItem) {
                            qty -= sumQtyByRuleId - countOfThisFreeItem;
                            elem.val(qty);
                        }

                        if (qty < countOfThisFreeItem - (sumQtyByRuleId - qty)) {
                            newValue = countOfThisFreeItem - sumQtyByRuleId;
                        } else {
                            newValue = 0;
                            qty = countOfThisFreeItem;
                        }

                        self.setProductQty(ruleId, skuId, qty, skuProps, newValue);
                    }

                    if (ruleType === RULE_TYPE_ALL && productSku === skuId) {
                        if (qty > countOfThisFreeItem) {
                            qty = countOfThisFreeItem;
                            elem.val(qty);
                        }

                        if (qty === 0) {
                            newValue = countOfThisFreeItem;
                        } else if (qty <= countOfThisFreeItem) {
                            newValue = countOfThisFreeItem - qty;
                        } else {
                            newValue = 0;
                            qty = countOfThisFreeItem;
                        }

                        self.setProductQty(ruleId, skuId, qty, skuProps, newValue);
                    }
                });
            });

            $.each(this.options.products, function (itemRuleId, ruleData) {
                var allSkuForRule = Object.keys(ruleData.sku),
                    firstSku = allSkuForRule[0];

                itemRuleType = self.options.products[itemRuleId]['rule_type'];

                switch (itemRuleType) {
                    case '1':
                        countOfRulesFreeItem += ruleData.sku[firstSku]['initial_value'];
                        break;

                    case '0':
                        $.each(self.options.products[itemRuleId].sku, function (itemSku, skuProps) {
                            countOfRulesFreeItem += skuProps['initial_value'];
                        });
                        break;

                    default:
                        break;
                }
            });

            if (self.getSumQtys() < countOfRulesFreeItem) {
                this.options.commonQty = countOfRulesFreeItem - self.getSumQtys();
            } else {
                this.options.commonQty = 0;
            }
        },

        /**
         * @returns {Object}
         */
        getSumQtysByRuleId: function () {
            var sumQtysByRuleId = {};

            $.each($('[data-am-js=ampromo-qty-input]'), function () {
                var itemRuleId = $(this).attr('data-rule'),
                    value = $(this).val(),
                    qty = value === '' ? 0 : parseInt(value, 10);

                if (qty >= 0) {
                    if (sumQtysByRuleId[itemRuleId]) {
                        sumQtysByRuleId[itemRuleId] += qty;
                    } else {
                        sumQtysByRuleId[itemRuleId] = qty;
                    }
                }
            });

            return sumQtysByRuleId;
        },

        /**
         * @returns {Number}
         */
        getSumQtys: function () {
            var sumQtys = 0;

            this.element.find('[data-am-js=ampromo-qty-input]').each(function () {
                var value = this.value,
                    qty = value === '' ? 0 : parseInt(value, 10);

                if (qty >= 0) {
                    sumQtys += qty;
                }
            });

            return sumQtys;
        },

        /**
         * @returns {void}
         */
        addCounterToPopup: function () {
            var self = this;

            $.each(this.options.products, function (ruleId, ruleData) {
                $.each(ruleData.sku, function (skuId, itemData) {
                    self.options.products[ruleId].sku[skuId]['old_value'] = itemData.qty;
                    self.options.products[ruleId].sku[skuId]['initial_value'] = itemData.qty;
                });
            });
        },

        /**
         * @private
         * @returns {void}
         */
        _initSlider: function () {
            if (this.gallery.hasClass('slick-initialized')) {
                this.gallery.slick('unslick');
            }

            this.gallery.slick(this.options.slickSettings);
            this.isSliderInitialized = true;
        },

        /**
         * @returns {void}
         */
        _calcWidthTitle: function () {
            var itemsCountReal = +this.gallery.data('count'),
                titleWidth = itemsCountReal >= this.options.sliderItemsCount ?
                    this.options.sliderItemsCount * this.options.sliderWidthItem :
                    itemsCountReal * this.options.sliderWidthItem;

            this.titlePopup.css('max-width', titleWidth + 'px');
        },

        /**
         * @returns {void}
         */
        _toggleMessage: function () {
            var isToggle = this._isToggleMessage();

            this.message.toggle(isToggle);
        },

        /**
         * @returns {Boolean}
         */
        _isToggleMessage: function () {
            return this.itemsCount > 0;
        },

        /**
         * @param {String} ruleId
         * @param {String} skuId
         * @param {Number} newQty
         * @param {Object} skuProps
         * @param {Number} newValue
         * @returns {void}
         */
        setProductQty: function (ruleId, skuId, newQty, skuProps, newValue) {
            this.options.products[ruleId].sku[skuId].qty =
                newQty === skuProps['old_value'] || this.isValidNumber(newValue) || newValue === 0 ?
                    newValue :
                    skuProps['old_value'];
        },

        /**
         * @param {Number} value
         * @returns {Boolean}
         */
        isValidNumber: function (value) {
            var isValid = $.isNumeric(value);

            if (value < 0 || !isValid) {
                this.addToCartDisableOrEnable(true);

                return false;
            }

            return true;
        },

        /**
         * @param {jQuery} elem
         * @returns {mage.ampromoPopup}
         */
        checkboxState: function (elem) {
            var product = this.getProductDomByElem(elem),
                selectInput = product
                    .find('[data-am-js="ampromo-qty-input"]'),
                isChecked = $(elem).attr('checked'),
                value = isChecked ? 1 : 0;

            selectInput.val(value);
            selectInput.keyup().prop('disabled', !isChecked);

            return this;
        },

        /**
         * @param {jQuery} elem
         * @returns {String}
         */
        getProductSku: function (elem) {
            return this.getProductDomByElem(elem).attr('data-product-sku');
        },

        /**
         * @param {jQuery} elem
         * @returns {String}
         */
        getRuleId: function (elem) {
            return this.getProductDomByElem(elem).find('.ampromo-qty').attr('data-rule');
        },

        /**
         * @param {jQuery} elem
         * @returns {String}
         */
        getRuleType: function (elem) {
            return this.getProductDomByElem(elem).find('.ampromo-qty').attr('data-rule-type');
        },

        /**
         * @param {jQuery} elem
         * @returns {jQuery}
         */
        getProductDomByElem: function (elem) {
            return elem.parents('[data-role=ampromo-item]');
        },

        /**
         * @returns {void}
         */
        reload: function () {
            this._loadItems();
            this.initPopup = false;
            this.options.loading = false;
            this.cancelLoading = false;
        }
    });

    return $.mage.ampromoPopup;
});
