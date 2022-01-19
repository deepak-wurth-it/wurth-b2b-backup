define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    var ampromoForm = {
        update: function (type) {
            var action = '',
                ruleId = registry.get('sales_rule_form.sales_rule_form.conditions.conditions_apply_to.html_content').source.data.rule_id ?? '',
                actionFieldSet = $('#' + type +'rule_actions_fieldset_'+ruleId).parent()

            this.resetFields(type);
            window.amPromoHide = 0;

            actionFieldSet.show();
            if (typeof window.amRulesHide !="undefined" && window.amRulesHide == 1) {
                actionFieldSet.hide();
            }

            var selector = $('[data-index="simple_action"] select');
            if (selector.length) {
                if (type !== 'sales_rule_form') {
                    action = selector[1] ? selector[1].value : selector[0].value ? selector[0].value : undefined;
                } else {
                    action = selector.val();
                }
            }

            if (!action) {
                action = 'by_percent';
            }

            if (action.match(/^ampromo/)) {
                this.hideFields(['simple_free_shipping', 'apply_to_shipping'], type);
            }

            this.renameRulesSetting(action);
            this.hideTabs();
            switch (action) {
                case 'ampromo_cart':
                    actionFieldSet.hide();
                    window.amPromoHide = 1;

                    this.hideFields(['discount_qty', 'discount_step'], type);
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
                case 'ampromo_items':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
                case 'ampromo_product':
                    this.showFields(['ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
                case 'ampromo_spent':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
                case 'ampromo_eachn':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
            }
        },
        showPromoItemPriceTab: function () {
            $('[data-index=ampromorule_items_price]').show();
        },

        hidePromoItemPriceTab: function () {
            $('[data-index=ampromorule_items_price]').hide();
        },

        resetFields: function (type) {
            this.showFields([
                'discount_qty', 'discount_step', 'apply_to_shipping', 'simple_free_shipping'
            ], type);
            this.hideFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
        },

        hideFields: function (names, type) {
            return this.toggleFields('hide', names, type);
        },

        showFields: function (names, type) {
            return this.toggleFields('show', names, type);
        },

        addPrefix: function (names, type) {
            for (var i = 0; i < names.length; i++) {
                names[i] = type + '.' + type + '.' + 'actions.' + names[i];
            }

            return names;
        },

        toggleFields: function (method, names, type) {
            registry.get(this.addPrefix(names, type), function () {
                for (var i = 0; i < arguments.length; i++) {
                    arguments[i][method]();
                }
            });
        },

        /**
         *
         * @param action
         */
        renameRulesSetting: function (action) {
            var discountStep = $('[data-index="discount_step"] label span'),
                discountAmount = $('[data-index="discount_amount"] label span');

            switch (action) {
                case 'ampromo_eachn':
                    discountStep.text($.mage.__("Each N-th"));
                    discountAmount.text($.mage.__("Number Of Gift Items"));
                    break;
                case 'ampromo_cart':
                case 'ampromo_items':
                case 'ampromo_product':
                case 'ampromo_spent':
                    discountAmount.text($.mage.__("Number Of Gift Items"));
                    break;
                default:
                    discountAmount.text($.mage.__("Discount Amount"));
                    discountStep.text($.mage.__("Discount Qty Step (Buy X)"));
                    break;
            }
        },
        
        hideTabs: function () {
            this.hidePromoItemPriceTab();
        }
    };

    return ampromoForm;
});
