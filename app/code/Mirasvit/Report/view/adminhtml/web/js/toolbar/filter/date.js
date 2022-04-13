define([
    'underscore',
    'ko',
    'uiElement',
    'Mirasvit_Report/js/lib/ko/bind/daterangepicker'
], function (_, ko, Element) {
    'use strict';
    
    return Element.extend({
        defaults: {
            template: 'Mirasvit_Report/toolbar/filter/date',
            
            exports: {
                from:              '${ $.provider }:params.filters[${ $.column }].from',
                to:                '${ $.provider }:params.filters[${ $.column }].to',
                compareFrom:       '${ $.provider }:params.filters[${ $.column }].compareFrom',
                compareTo:         '${ $.provider }:params.filters[${ $.column }].compareTo',
                comparisonEnabled: '${ $.provider }:params.filters[${ $.column }].comparisonEnabled'
            },
            
            listens: {}
        },
        
        initialize: function () {
            this._super();
            
            return this;
        },
        
        initObservable: function () {
            this._super();
            
            this.from = ko.observable(this.value.from);
            this.to = ko.observable(this.value.to);
            this.compareFrom = ko.observable(this.value.compareFrom);
            this.compareTo = ko.observable(this.value.compareTo);
            this.comparisonEnabled = ko.observable(this.value.comparisonEnabled);
            
            return this;
        }
    });
});
