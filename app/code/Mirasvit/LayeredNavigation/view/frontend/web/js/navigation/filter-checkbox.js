define(["jquery"], function ($) {
    'use strict';
    
    //Work with checkbox
    $.widget('mst.mNavigationFilterCheckbox', {
        _create: function () {
            this._bind();
        },

        _bind: function () {
            var el = this.element;

            if (this.options.isAjaxEnabled == 0) {
                el.on('click', function () {
                    var href = el.parents('a').prop('href');
                    var checkbox = el.find('input[type=checkbox]');

                    if (checkbox.prop('checked')) {
                        window.mNavigationFilterCheckboxApplied = true;
                        checkbox.prop('checked', !checkbox.prop('checked'));
                        checkbox.context.checked = false;
                    } else if (!checkbox.prop('checked')) {
                        window.mNavigationFilterCheckboxApplied = true;
                        checkbox.prop('checked', 'checked');
                        checkbox.context.checked = true;
                    }
                    window.location.href = href;
                });
            }

            if (this.options.isAjaxEnabled == 1) {
                el.on('click', function (e) {
                    e.preventDefault();
                    var checkbox;

                    window.mNavigationFilterCheckboxAjaxApplied = true;
                    if (this.options.isStylizedCheckbox == 0) {
                        checkbox = el.find('input[type=checkbox]');

                        if (checkbox.context.checked === false) {
                            window.mNavigationIsSimpleCheckboxChecked = true;
                        }
                        if (checkbox.context.checked === true) {
                            window.mNavigationIsSimpleCheckboxChecked = false;
                        }
                    }
                }.bind(this));
            }
        }
    });

    return $.mst.mNavigationFilterCheckbox;
});
