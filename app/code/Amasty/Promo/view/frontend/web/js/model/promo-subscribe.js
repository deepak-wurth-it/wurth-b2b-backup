define([
    'ko',
    'jquery',
    'uiComponent',
    'Amasty_Conditions/js/model/subscriber',
    'Amasty_Promo/js/popup'
], function (ko, $, Component, subscriber) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();

            subscriber.isLoading.subscribe(function (isLoading) {
                if (!isLoading) {
                    $('[data-role=ampromo-overlay]').ampromoPopup('reload');
                }
            });

            return this;
        }
    });
});
