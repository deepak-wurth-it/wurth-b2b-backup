define([
    'underscore',
    'uiRegistry',
    'Magento_Catalog/js/components/new-category',
    'jquery'
], function (_, uiRegistry, Category, $) {
    'use strict';

    return Category.extend({
        toggleOptionSelected: function (data) {
            this._super(data);

            if (this.isSelected(data.value) && data.hasOwnProperty(this.separator)) {
                // Show and select all nested category
                this.openChildByData(data);
                var self = this;
                _.each(data[this.separator], function (child) {
                    self.selectChilds(child);
                });
            }

            return this;
        },

        selectChilds: function (data) {
            if (!this.isSelected(data.value)) {
                this.value.push(data.value);
            }
            if (data.hasOwnProperty(this.separator)) {
                this.openChildByData(data);
                var self = this;
                _.each(data[this.separator], function (child) {
                    self.selectChilds(child);
                });
            }
        },

        openChildByData: function (data) {
            var contextElement = $(this.cacheUiSelect).find('li')[this.getOptionIndex(data)];
            $(contextElement).children('ul').show();
        }
    });
});