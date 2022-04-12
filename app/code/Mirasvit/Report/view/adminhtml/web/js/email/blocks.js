define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';
    
    var Block = function (data) {
        function guid() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            
            return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
        }
        
        this.data = data;
        
        this.identifier = ko.observable();
        this.timeRange = ko.observable();
        this.limit = ko.observable(data.limit);
        
        this.guid = guid();
        this.isSubscribed = false;
        
        this.subscribe = function (fnc) {
            if (this.isSubscribed) {
                return;
            }
            
            this.identifier.subscribe(fnc);
            this.timeRange.subscribe(fnc);
            this.limit.subscribe(fnc);
            this.isSubscribed = true;
        }.bind(this);
    };
    
    return Component.extend({
        defaults: {
            template: 'report/email/blocks',
            blocks:   [],
            ranges:   [],
            reports:  [],
            
            links: {
                email: '${ $.provider }:${ $.dataScope }'
            }
        },
        
        initialize: function () {
            var i;
            
            this._super();
            
            _.bindAll(this, 'handleAdd', 'handleDelete', 'synchronize');
            
            if (this.email()) {
                _.each(this.email().blocks, function (data) {
                    this.handleAdd(data);
                }.bind(this));
            }
            
            return this;
        },
        
        initObservable: function () {
            this._super();
            
            this.email = ko.observable();
            this.blocks = ko.observableArray();
            
            this.blocks.subscribe(function (items) {
                items.forEach(function (item) {
                    item.subscribe(this.synchronize);
                }.bind(this));
                
                this.synchronize();
            }.bind(this));
            
            return this;
        },
        
        handleAdd: function (data) {
            if (data === undefined) {
                data = {};
            }
            this.blocks.push(new Block(data));
        },
        
        handleDelete: function ($data) {
            this.blocks.remove($data);
        },
        
        
        synchronize: function () {
            var email = this.email();
            var blocks = [];
            
            _.each(this.blocks(), function (item) {
                blocks.push({
                    identifier: item.identifier(),
                    timeRange:  item.timeRange(),
                    limit:      item.limit()
                })
            });
            
            email.blocks = blocks;
            this.email(email);
        },
        
        reportSelect: function ($data) {
            var config = {
                'Magento_Ui/js/core/app': {
                    'components': {}
                }
            };
            
            config['Magento_Ui/js/core/app']['components'][$data.guid + 'report'] = {
                component:     'Magento_Ui/js/form/element/ui-select',
                template:      'ui/form/field',
                elementTmpl:   'ui/grid/filters/elements/ui-select',
                componentType: 'field',
                formElement:   'select',
                labelVisible:  false,
                filterOptions: true,
                showCheckbox:  false,
                disableLabel:  true,
                multiple:      false,
                options:       this.reports,
                value:         $data.identifier
            };
            
            setTimeout(function () {
                $data.identifier($data.data.identifier)
            }.bind(this), 1000);
            
            return config;
        },
        
        rangeSelect: function ($data) {
            var config = {
                'Magento_Ui/js/core/app': {
                    'components': {}
                }
            };
            
            config['Magento_Ui/js/core/app']['components'][$data.guid + 'range'] = {
                component:     'Magento_Ui/js/form/element/ui-select',
                template:      'ui/form/field',
                elementTmpl:   'ui/grid/filters/elements/ui-select',
                componentType: 'field',
                formElement:   'select',
                labelVisible:  false,
                filterOptions: true,
                showCheckbox:  false,
                disableLabel:  true,
                multiple:      false,
                options:       this.ranges,
                value:         $data.timeRange
            };
            
            setTimeout(function () {
                $data.timeRange($data.data.timeRange)
            }.bind(this), 1000);
            
            return config;
        }
    });
});
