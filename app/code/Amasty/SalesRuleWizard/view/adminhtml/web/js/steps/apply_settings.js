// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'underscore'
], function (Component, $, _) {
    'use strict';

    return Component.extend({
        defaults: {
            notificationMessage: {
                text: null,
                error: null
            },
            modules: {
                settingsForm: '${ $.formName }',
                settingsFormFields: '${ $.formName }.general'
            },
            stepInitialized: false
        },
        currentScenario: '',
        initialize: function () {
            this._super();
        },
        render: function (wizard) {
            //on show
            this.wizard = wizard;

            var selectedScenario = wizard.data.scenario;
            if (selectedScenario != this.currentScenario) {
                this.settingsForm(function (form) {
                    form.reset();
                });

                this.settingsFormFields(function (formComponent) {
                    formComponent.elems.each(function (element) {
                        if (element.scenario) {
                            var isVisible = false;
                            element.scenario.each(function (scenario) {
                                if (scenario == selectedScenario) {
                                    isVisible = true;
                                }
                            });
                            element.visible(isVisible);
                        }
                    });
                    this.currentScenario = selectedScenario;
                }.bind(this));
            }
        },
        force: function (wizard) {
            var form = this.settingsForm();
            if (!form || !form.source) {
                throw new Error($.mage.__('Form is undefined, please try again.'));
            }
            form.validate();

            //save
            if (form.source.get('params.invalid')) {
                throw new Error($.mage.__('Step is invalid.'));
            }
            form.collectAdditionalData();
            wizard.data.apply_settings = form.source.get('data');

        },
        back: function (wizard) {
        }
    });
});
