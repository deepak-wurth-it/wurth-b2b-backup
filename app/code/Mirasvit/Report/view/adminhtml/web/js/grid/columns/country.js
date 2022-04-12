define([
    'Magento_Ui/js/grid/columns/select'
], function (Select) {
    'use strict';
    
    return Select.extend({
        defaults: {
            bodyTmpl: 'report/grid/cells/country'
        },
        
        initConfig: function () {
            this._super();
            return this;
        },
        
        getLabel: function (record) {
            var value = record[this.index + '_orig'];
            var text = record[this.index];
            if (value != null) {
                return '<img src="http://flagpedia.net/data/flags/w580/' + value.toLowerCase() + '.png" style="max-width: 2rem;">' + ' ' + text;
            } else {
                return text;
            }
        }
    });
});
