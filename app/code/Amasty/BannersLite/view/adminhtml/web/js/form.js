define([
    'Magento_Ui/js/form/components/fieldset',
    'underscore',
    'uiRegistry'
], function (Component, _, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            rulesActions: [
                //SP Rules
                'thecheapest',
                'themostexpencive',
                'moneyamount',
                'eachn_perc',
                'eachn_fixdisc',
                'eachn_fixprice',
                'eachmaftn_perc',
                'eachmaftn_fixdisc',
                'eachmaftn_fixprice',
                'groupn',
                'groupn_disc',
                'buyxgety_perc',
                'buyxgety_fixprice',
                'buyxgety_fixdisc',
                'buyxgetn_perc',
                'buyxgetn_fixprice',
                'buyxgetn_fixdisc',
                'aftern_fixprice',
                'aftern_disc',
                'aftern_fixdisc',
                'setof_percent',
                'setof_fixed',
                'tiered_wholecheaper',
                'tiered_buyxgetcheapern',
                //Free Gift Rules
                'ampromo_product',
                'ampromo_items',
                'ampromo_cart',
                'ampromo_spent',
                'ampromo_eachn'
            ],
            listens: {
                '${ $.parentName }.actions.simple_action:value': 'onChange'
            }
        },

        initialize: function () {
            this._super();
            registry.get(this.parentName + '.actions.simple_action', function (component) {
                this.checkVisibility(component.value());
            }.bind(this));
        },

        onChange:function (value) {
            this.checkVisibility(value);
        },

        checkVisibility: function (value) {
            if (_.contains(this.rulesActions, value)) {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});
