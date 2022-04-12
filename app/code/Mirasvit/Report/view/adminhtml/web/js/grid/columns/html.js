define([
    'Magento_Ui/js/grid/columns/select'
], function (Select) {
    'use strict';
    
    return Select.extend({
        defaults: {
            bodyTmpl: 'report/grid/cells/html'
        },
        
        initConfig: function () {
            this._super();
            return this;
        },
        
        getLabel: function (record) {
            var value = record[this.index];
            
            if (value) {
                return value.replace(/\n/g, "<br />");
            }
        }
    });
});
