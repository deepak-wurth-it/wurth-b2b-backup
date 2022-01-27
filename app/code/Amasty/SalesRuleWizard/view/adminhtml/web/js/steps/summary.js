// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'mageUtils',
    'uiLayout',
    'Magento_Ui/js/form/client',
    'Magento_Ui/js/lib/spinner'
], function (Component, $, _, registry, utils, layout, Client, Spinner) {
    'use strict';

    return Component.extend({
        defaults: {
            notificationMessage: {
                text: null,
                error: null
            },
            stepInitialized: false,
            nextLabelText: 'Save the Rule',
            previewConfig: {
                name: 'summary_preview',
                component: 'Amasty_SalesRuleWizard/js/ui/preview',
                template: 'Amasty_SalesRuleWizard/preview',
                settings_data: 'amasty_promowizard_rule_settings.rule_settings_data_source:data',
                apply_data: 'amasty_promowizard_apply_settings.rule_settings_data_source:data'
            },
            previewObserver: null,
            clientConfig: {
                urls: {
                    save: '${ $.submit_url }',
                    beforeSave: '${ $.validate_url }'
                }
            },
            modules: {
                previewComponent: '${ $.previewConfig.name }'
            }
        },

        /**
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .initClient();

            this.previewComponent(function (component) {
                this.previewObserver(component);
            }.bind(this));

            return this;
        },

        /**
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(['previewObserver']);

            return this;
        },

        /**
         * Init Preview module
         *
         * @returns {Object} Chainable.
         */
        initModules: function () {
            this._super();
            layout([this.previewConfig]);

            return this;
        },

        /**
         * Initializes client component.
         */
        initClient: function () {
            this.client = new Client(this.clientConfig);

            return this;
        },

        render: function (wizard) {
            //on show
            this.wizard = wizard;

            var sourceDummy = {set: function (){}};

            this.elems.each(function (element) {
                element.source = sourceDummy;
            });

            if (wizard.data.scenario) {
                var ruleNameComponent = this.getChild('rule_name'),
                    scenariosData = wizard.getStep('0').scenarios();

                var currentScenarioData = _.find(scenariosData, function (scenarioData) {
                    return scenarioData.value == wizard.data.scenario;
                });

                ruleNameComponent.default = _.template(currentScenarioData.nameTemplate)({data: wizard.data});
                ruleNameComponent.restoreToDefault();
            }
        },
        force: function (wizard) {
            //save
            if (!wizard.data.additional) {
                wizard.data.additional = {};
            }
            var isValid = true;

            this.elems.each(function (element) {
                if (!element.validate().valid) {
                    isValid = false;
                }
                wizard.data.additional[element.index] = element.value();
            });
            if (!isValid) {
                throw new Error($.mage.__('Step is invalid.'));
            }

            wizard.data.additional.description = this.previewComponent().getTextContent();

            Spinner.show();

            this.client.save(wizard.data, {
                redirect: true,
                ajaxSave: false,
                ajaxSaveType: 'default'
            });
        },
        back: function (wizard) {
        }
    });
});
