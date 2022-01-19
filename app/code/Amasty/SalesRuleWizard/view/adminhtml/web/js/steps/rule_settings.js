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
            if (this.settingsFormFields().getChild('ampromorule_sku').visible() && form.source.get('data.free_gifts.products') && form.source.get('data.free_gifts.products').length == 0) {
                throw new Error($.mage.__('You should choose Promo products.'));
            }

            form.collectAdditionalData();
            wizard.data.rule_settings = form.source.get('data');

        },
        back: function (wizard) {
        }
    });
});
