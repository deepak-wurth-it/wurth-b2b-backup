define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/ui-select'
], function (_, ko, UiSelect) {
    'use strict';
    
    return UiSelect.extend({
        defaults: {
            template: 'ui/grid/filters/elements/ui-select',

            exports: {
                values: '${ $.provider }:params.filters[${ $.column }]'
            },
            
            listens: {}
        },

        initObservable: function () {
            this._super();

            this.values = ko.observable();

            return this;
        },

        onUpdate: function() {
            this._super();

            this.values(this.value());
        }
    });
});
