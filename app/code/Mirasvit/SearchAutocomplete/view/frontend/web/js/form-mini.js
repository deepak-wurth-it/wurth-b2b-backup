define(['jquery'], function ($) {
    'use strict';

    var formMixin = {
        _onPropertyChange: function () {
            return true;
        }
    };

    return function (targetWidget) {
        $.widget('mage.quickSearch', targetWidget, formMixin);
        return $.mage.quickSearch;
    };
});
