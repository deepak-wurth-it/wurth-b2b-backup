define([
    'underscore',
    'jquery',
    'Magento_Ui/js/form/form'
], function (_, $, Form) {
    'use strict';

    /**
     * Collect form data.
     *
     * @param {Array} items
     * @returns {Object}
     */
    function collectData(items) {
        var result = {},
            name;

        items = Array.prototype.slice.call(items);

        items.forEach(function (item) {
            name = arrayNameToPath(item.name);
            switch (item.type) {
                case 'checkbox':
                    result[name] = +!!item.checked;
                    break;

                case 'radio':
                    if (item.checked) {
                        result[name] = item.value;
                    }
                    break;

                case 'select-multiple':
                    result[name] = _.pluck(item.selectedOptions, 'value');
                    break;

                default:
                    result[name] = item.value;
            }
        });

        return result;
    }

    /**
     *  parse "rule[conditions][1-1][value]
     *  to "rule.conditions.1-1.value"
     *
     * @param {String} arrayName
     * @return {String}
     */
    function arrayNameToPath(arrayName) {
        var path = arrayName.replace(new RegExp("\\[]|]", 'g'), '');
        return path.replace(new RegExp("\\[", 'g'), '.');
    }

    return Form.extend({
        collectAdditionalData: function () {
            var additional = collectData(this.additionalFields),
                source = this.source;

            _.each(additional, function (value, name) {
                source.set('data.' + name, value);
            });
        }
    });
});