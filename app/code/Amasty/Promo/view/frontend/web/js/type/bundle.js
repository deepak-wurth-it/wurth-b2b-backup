define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.ampromoBundle', {
        options: {
            optionSelector: '.bundle.option.change-container-classname',
            selectOptionSelector: 'bundle-option-select',
            defaultQtyAttr: 'default-qty'
        },

        _create: function () {
            this.bindOptionsChange();
        },

        /**
         * Bind change event for bundle options
         */
        bindOptionsChange: function () {
            $(this.options.optionSelector).on('change', this.changeQtyValue.bind(this));
        },

        /**
         * @param {Event} event
         * @return {void}
         */
        changeQtyValue: function (event) {
                var element = event.currentTarget,
                    optionId = $(element).data('option-id'),
                    qtyInput = $('.qty-option-' + optionId),
                    defaultQty;

                if (!qtyInput) {
                    return;
                }

                if ($(element).hasClass(this.options.selectOptionSelector)) {
                    defaultQty = $("option:selected", element).data(this.options.defaultQtyAttr);
                } else {
                    defaultQty = $(element).data(this.options.defaultQtyAttr);
                }

                if (defaultQty) {
                    qtyInput.val(defaultQty);
                }
        }
    });

    return $.mage.ampromoBundle;
});
