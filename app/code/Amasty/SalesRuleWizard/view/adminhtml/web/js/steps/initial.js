// jscs:disable jsDoc
define([
    'uiComponent',
    'underscore'
], function (Component, _) {
    'use strict';

    return Component.extend({
        stepInitialized: false,
        defaults: {
            scenariosMap: {},
            scenarios: {},
            selectedType: '',
            selectedScenario: '',
            notificationMessage: {
                text: null,
                error: null
            },
            listens: {
                selectedType: 'onScenarioChange',
                selectedScenario: 'onScenarioChange'
            }
        },
        initialize: function () {
            this._super();

            this.selectRuleType(this.selectedType());
        },
        initObservable: function () {
            this._super().observe(['scenarios', 'selectedScenario', 'selectedType']);

            return this;
        },
        onScenarioChange: function (scenario) {
        },
        selectRuleType: function (ruleType) {
            _.each(this.scenariosMap, function (type, typeCode) {
                if (typeCode == ruleType) {
                    this.scenarios(type.scenarios);
                }
            }.bind(this));
        },
        render: function (wizard) {
            this.wizard = wizard;
        },
        force: function (wizard) {
            wizard.data.scenario = this.selectedScenario();

            if (!wizard.data.scenario) {
                throw new Error($.mage.__('Please select scenario.'));
            }
        },
        back: function () {
        }
    });
});
