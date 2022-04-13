define([
    'underscore',
    'ko',
    'uiComponent',
    'uiLayout'
], function (_, ko, Component, Layout) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'report/toolbar',

            exports: {
                dimension: '${ $.provider }:params.dimension',
                rand:      '${ $.provider }:params.rand'
            },

            listens: {}
        },

        initialize: function () {
            this._super();

            _.bindAll(this, 'onGroupBy');

            _.each(this.fastFilters, function (filter) {
                filter.provider = this.provider;
                filter.parent = this.name;
                filter.name = filter.column;

                Layout([filter]);
            }.bind(this));

            _.each(this.groupBy, function (group) {
                group.provider = this.provider;
                group.parent = this.name;

                Layout([group]);
            }.bind(this));

            return this;
        },

        initObservable: function () {
            this._super();

            this.observe('dimension');
            this.observe('dimensionLabel');
            this.observe('rand');

            return this;
        },

        onGroupBy: function (group) {
            this.dimensionLabel(': ' + group.label);
            this.dimension(group.column);
            this.rand(new Date().toLocaleString());
        }
    });
});
