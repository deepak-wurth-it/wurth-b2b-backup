define([
    'uiComponent',
    'jquery',
    'knockout',
    'underscore',
    'uiRegistry',
    'mage/translate'
], function (Component, $, ko, _, registry, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            buyX: '',
            productCondition: '',
            receive: '',
            applyType: '',
            applyTime: '',
            scenario: '',
            discountStep: 1,
            discountAmount: 0,
            isProductConditions: 0,
            productConditionsData: '',
            isSameProduct: 0,
            freeType: 0,
            freeGiftsArray: [],
            isCoupon: 0,
            couponCode: '',
            applyTimeData: '',
            applyTimeLimit: 1,
            isApplyConditions: 0,
            applyConditionsData: '',
            imports: {
                scenario: "promo-steps-wizard_step1:selectedScenario",
                discountStep: "${ $.settings_data }.discount_step",
                isProductConditions: "${ $.settings_data }.is_actions",
                productConditionsData: "${ $.settings_data }.rule.actions",
                discountAmount: "${ $.settings_data }.discount_amount",
                isSameProduct: "${ $.settings_data }.is_same_product",
                freeType: "${ $.settings_data }.extension_attributes.ampromo_rule.type",
                freeGiftsArray: "${ $.settings_data }.free_gifts.products",
                isCoupon: "${ $.apply_data }.is_coupon",
                couponCode: "${ $.apply_data }.coupon_code",
                applyTimeData: "${ $.apply_data }.apply_time",
                applyTimeLimit: "${ $.apply_data }.maximum_times",
                isApplyConditions: "${ $.apply_data }.is_conditions",
                applyConditionsData: "${ $.apply_data }.rule.conditions"
            }
        },
        conditionText: '',
        initObservable: function () {
            this._super().observe([
                'scenario',
                'discountStep',
                'isProductConditions',
                'productConditionsData',
                'discountAmount',
                'isSameProduct',
                'freeType',
                'freeGiftsArray',
                'isCoupon',
                'couponCode',
                'applyTimeData',
                'applyTimeLimit',
                'isApplyConditions',
                'applyConditionsData'
            ]);

            this.buyX = ko.computed(function () {
                var result = 'Should ',
                    amount = (this.discountStep() * 1);
                if (this.scenario() == 'spent_x_get_y') {
                    result += 'spent ' + amount + ' on';
                } else {
                    result += 'buy ' + amount;
                }

                if (this.isProductConditions() == 1) {
                    result += ' specified';
                } else {
                    result += ' any';
                }
                result += amount > 1 && this.scenario() != 'spent_x_get_y' ? ' products' : ' product';

                return result;
            }, this);

            this.productCondition = ko.computed(function () {
                if (this.isProductConditions() == 1 && this.productConditionsData()) {
                    return this.collectProductConditions();
                }

                return '';
            }, this);

            this.receive = ko.computed(function () {
                var amount = (this.discountAmount() * 1),
                    result = 'To get ' + amount;

                if (this.isSameProduct() == 1) {
                    result += ' of the same product';
                } else {
                    result += amount > 1 ? ' products ' : ' product ';

                    var names = [];
                    this.freeGiftsArray.each(function (product) {
                        names.push(product.name);
                    });

                    if (this.freeType() * 1) {
                        result += ' to choose from';
                    } else {
                        result += ' of each products';
                    }

                    result += ' "' + names.join('", "') + '"';
                }

                return result + ' for free';
            }, this);

            this.applyType = ko.computed(function () {
                var result = 'Your Rule will be triggered ';
                if (this.isCoupon() == 0) {
                    return result + 'automatically'
                }

                return result + 'by special coupon code "' + this.couponCode() + '"';
            }, this);

            this.applyTime = ko.computed(function () {
                switch (this.applyTimeData()) {
                    case 'first':
                        return 'only First Time';
                    case 'every':
                        return 'Every Time';
                    case 'limit':
                        return 'Every Time with limit "' + this.applyTimeLimit() + '"';
                }
            }, this);

            this.applyConditions = ko.computed(function () {
                if (this.isApplyConditions() == 1 && this.applyConditionsData()) {
                    return 'if the conditions are met: ' + this.collectApplyConditions();
                }

                return '';
            }, this);

            return this;
        },
        getTextContent: function () {
            var result = this.buyX();

            if (this.productCondition()) {
                result += "\n" + this.productCondition();
            }
            result += "\n" + this.receive() + "\n" + this.applyType() + ' ' + this.applyTime();
            if (this.applyConditions()) {
                result += "\n" + this.applyConditions();
            }

            return this._convertToText(result);
        },
        /**
         * @param {string} html
         * @returns {string}
         * @private
         */
        _convertToText: function (html) {
            var tmp = document.createElement('div');
            tmp.innerHTML = html;

            return tmp.textContent || tmp.innerText;
        },
        collectProductConditions: function () {
            this.conditionText = '<div class="condition">';
            var contents = $('#amasty_promowizard_rule_settingsrule_actions_fieldset_ > .rule-tree-wrapper').contents();
            this._ruleTreeWalker(contents, false);
            this.conditionText += '</div>';

            return this.conditionText;
        },
        collectApplyConditions: function () {
            this.conditionText = '<div class="condition">';
            var contents = $('#amasty_promowizard_apply_settingsrule_conditions_fieldset_ > .rule-tree-wrapper').contents();
            this._ruleTreeWalker(contents, false);
            this.conditionText += '</div>';

            return this.conditionText;
        },
        /**
         * @param {Array} contents
         * @param {Boolean} onlyText
         * @private
         */
        _ruleTreeWalker: function (contents, onlyText) {
            _.each(contents, function (elem) {
                if (elem.nodeType === 3) {
                    if (elem.data) {
                        this.conditionText += elem.data;
                    }
                } else if (onlyText) {
                    return; //continue
                } else if (elem.nodeName == 'SPAN') {
                    this._ruleTreeWalker($(elem).children('.label').contents(), true);
                } else if (elem.nodeName == 'UL') {
                    this.conditionText += "\n" + '<div class="condition">';
                    var children = $(elem).children('li'),
                        max = children.length - 2;

                    _.each(children, function (liElem, iteration) {
                        this._ruleTreeWalker($(liElem).contents(), false);
                        if (iteration < max) {
                            this.conditionText += "\n" + '<br/>';
                        }
                    }.bind(this));

                    this.conditionText += "\n" +'</div>';
                }
            }.bind(this));
        }
    });
});
