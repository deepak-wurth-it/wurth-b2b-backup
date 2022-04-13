define([
    'uiElement',
    'ko',
    'underscore',
    'mage/translate'
], function (Element, ko, _, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'report/settings/columns',
            searchColumn: '',
            columns: []
        },

        initObservable: function () {
            this.searchColumn = ko.observable('');

            return this._super();
        },

        resetSearch: function () {
            this.searchColumn('');
        },

        findColumns: function () {
            var columns = [];

            if (this.searchColumn()) {
                columns = _.filter(this.columns, function (column) {
                    if (column.label.toLowerCase().indexOf(this.searchColumn().toLowerCase()) !== -1) {
                        return column;
                    }
                }.bind(this));
            } else {
                columns = this.columns;
            }

            return columns;
        },

        toggle: function (col, e) {
            var status = e.currentTarget.querySelector('input[type="checkbox"]');
            if (!status.disabled) {
                status.checked = !status.checked;

                if (status.checked) {
                    e.currentTarget.classList.add('active');
                } else {
                    e.currentTarget.classList.remove('active');
                }
            }
        }
    });
});
