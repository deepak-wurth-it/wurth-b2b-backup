define([
    'Magento_Ui/js/form/element/ui-select',
    'underscore'
], function (Multiselect, _) {
    'use strict';

    return Multiselect.extend({
        getPreview: function () {
            var values = this.value();

            var options = [];
            _.each(values, function(value) {
                var option = _.findWhere(this.options(), {value: value});
                options.push(option.label);
            }.bind(this));

            var preview = options.join(', ');

            this.preview(preview);
            return preview;
        }
    });
});
