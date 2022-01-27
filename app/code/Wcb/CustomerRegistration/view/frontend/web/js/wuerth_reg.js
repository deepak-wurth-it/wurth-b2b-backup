define([
    "jquery",
    "jquery/ui",
    'mage/validation'
], function($) {
    "use strict";
    console.log('wuerth_reg.js is loaded!!');
        //creating jquery widget
        $.widget('wuerth_reg.js', {
            _create: function() {
                this._bind();
            },

            /**
             * Event binding, will monitor change, keyup and paste events.
             * @private
             */
            _bind: function () {
                this._on(this.element, {
                    'change': this.validateField,
                    'keyup': this.validateField,
                    'paste': this.validateField,
                    'click': this.validateField,
                    'focusout': this.validateField,
                    'focusin': this.validateField,
                });
            },

            validateField: function () {
                $.validator.validateSingleElement(this.element);
            },

        });

    return $.wuerth_reg.js;
});