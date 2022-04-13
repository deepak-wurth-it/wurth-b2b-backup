define([
    'underscore',
    'Magento_Ui/js/grid/provider'
], function (_, Provider) {
    'use strict';

    return Provider.extend({
        reload: function (options) {
            this.params.ts = Date.now();
            return this._super(options);
        }
    });
});
