define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/ui-select'
], function ($, _, UiSelect) {
    'use strict';

    return UiSelect.extend({
        defaults: {
            template:     'Mirasvit_Search/component/attributes',
            attributes:   [],
            weights:      [],
            weightSource: [],
            instances:    {},

            links:   {
                index: '${ $.provider }:${ $.dataScope }'
            },
            exports: {
                index: '${ $.provider }:${ $.dataScope }'
            },
            listens: {
                index: 'handleIndexChange'
            }
        },

        initialize: function () {
            this._super();
        },

        handleIndexChange: function () {
            if (_.isString(this.index) && this.index) {
                this.disabled(true);
            }
        }
    });
});


