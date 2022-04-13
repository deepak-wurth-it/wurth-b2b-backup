define([
    'jquery',
    'underscore',
    'ko',
    'uiElement'
], function ($, _, ko, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'reports/toolbar/menu',
            collapsible: true,
            opened: false
        },

        initialize: function () {
            this._super();

            return this;
        }
    });
});
