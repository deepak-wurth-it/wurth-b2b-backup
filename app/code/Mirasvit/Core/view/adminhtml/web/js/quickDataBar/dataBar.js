define([
    'uiComponent',
    'ko',
    'underscore',
    'jquery'
], function (Component, ko, _, $) {
    'use strict';

    return Component.extend({
        defaults: {
            dateRangeList: [
                {
                    value: '0',
                    label: 'Today'
                },
                {
                    value: '7',
                    label: 'Last 7 days'
                },
                {
                    value: '30',
                    label: 'Last 30 days'
                },
                {
                    value: '365',
                    label: 'Last 365 days'
                }
            ],
            dateRange:     '30',
            isReady:       true
        },

        initObservable: function () {
            this._super();

            this.dateRange = ko.observable(this.dateRange);
            this.dataBlockList = ko.observableArray(this.dataBlockList);
            this.isReady = ko.observable(true);

            return this;
        }
    });
});
