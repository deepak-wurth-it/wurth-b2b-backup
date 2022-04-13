define([
    'underscore',
    'ko',
    'uiElement'
], function (_, ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'reports/toolbar/dimension',

            exports: {
                groupBy: '${ $.provider }:params.groupBy'
            },

            listens: {}
        },

        initialize: function () {
            this._super();

            this.observe('current');

            _.bindAll(this, 'onChange');

            return this;
        },

        onChange: function (item) {
            this.set('groupBy', item.value);
            this.set('current', item.label);
        }
    });
});
