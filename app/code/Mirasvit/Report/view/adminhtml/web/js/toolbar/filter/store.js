define([
    'underscore',
    'ko',
    'uiElement'
], function (_, ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'report/toolbar/filter/store',

            exports: {
                storeIds: '${ $.provider }:params.filters[${ $.column }].value'
            },

            listens: {}
        },

        initialize: function () {
            this._super();

            _.bindAll(this, 'onChangeStore');

            return this;
        },

        initObservable: function () {
            this._super();

            this.storeIds = ko.observable();
            this.current = ko.observable(this.current);

            return this;
        },

        onChangeStore: function (store) {
            this.storeIds(store.storeIds.split(','));
            this.current(store.label);
        }
    });
});
