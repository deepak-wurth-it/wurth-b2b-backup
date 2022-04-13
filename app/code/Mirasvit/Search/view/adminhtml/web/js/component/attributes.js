define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';

    var SearchableAttribute = function (attribute, weight) {
        function guid() {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
        }

        this.attribute = ko.observable(attribute);
        this.weight = ko.observable(weight);
        this.guid = guid();
        this.isSubscribed = false;

        this.subscribe = function (fnc) {
            if (this.isSubscribed) {
                return;
            }

            this.attribute.subscribe(fnc);
            this.weight.subscribe(fnc);
            this.isSubscribed = true;
        }.bind(this);
    };

    return Component.extend({
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
            var i;

            this._super();


            _.bindAll(this, 'handleAdd', 'handleDelete', 'synchronize');

            if (this.index()) {
                _.each(this.index().attributes, function (weight, attribute) {
                    this.weights.push(new SearchableAttribute(attribute, weight));
                }.bind(this));
            }

            for (i = 1; i <= 10; i++) {
                this.weightSource.push({
                    label: i,
                    value: i
                });
            }

            this.handleIndexChange();

            return this;
        },

        initObservable: function () {
            this._super();

            this.index = ko.observable();
            this.weights = ko.observableArray();
            this.attributes = ko.observableArray();

            this.weights.subscribe(function (items) {
                items.forEach(function (item) {
                    item.subscribe(this.synchronize);
                }.bind(this));

                this.synchronize();
            }.bind(this));

            return this;
        },

        handleAdd: function () {
            this.weights.push(new SearchableAttribute('', 1));
        },

        handleDelete: function ($data) {
            this.weights.remove($data);
        },

        handleIndexChange: function () {
            if (!this.index()) {
                return;
            }

            var attributes = this.instances[this.index()['identifier']];

            if (attributes) {
                this.attributes([]);

                ko.utils.objectForEach(attributes, function (value, label) {
                    this.attributes.push({
                        label: label,
                        value: value
                    });
                }.bind(this));
            }
        },

        synchronize: function () {
            var attributes = {};
            var index = this.index();

            _.each(this.weights(), function (item) {
                attributes[item.attribute()] = item.weight();
            });

            index.attributes = attributes;

            this.index(index);
        },

        attributesSelect: function ($data) {
            var config = {
                'Magento_Ui/js/core/app': {
                    'components': {}
                }
            };

            if (this.attributes().length < 100) {
                config['Magento_Ui/js/core/app']['components'][$data.guid] = {
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
                    options:       this.attributes(),
                    value:         $data.attribute,
                    ignoreTmpls:   {
                        value: false
                    }
                };
            } else {
                config['Magento_Ui/js/core/app']['components'][$data.guid] = {
                    component:     'Magento_Ui/js/form/element/select',
                    template:      'ui/form/field',
                    componentType: 'field',
                    formElement:   'select',
                    labelVisible:  false,
                    filterOptions: true,
                    showCheckbox:  false,
                    disableLabel:  true,
                    multiple:      false,
                    options:       this.attributes(),
                    value:         $data.attribute,
                    ignoreTmpls:   {
                        value: false
                    }
                };
            }

            return config;
        }
    });
});
