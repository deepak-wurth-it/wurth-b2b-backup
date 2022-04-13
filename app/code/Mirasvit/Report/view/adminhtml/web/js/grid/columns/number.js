define([
    'Mirasvit_Report/js/grid/columns/column'
], function (Column) {
    'use strict';
    
    return Column.extend({
        defaults: {
            bodyTmpl: 'report/grid/cells/number',
            
            imports: {
                totals: '${ $.provider }:data.totals'
            }
        },
        
        getPercent: function (row) {
            var total = 0;
            if (this.totals.length) {
                total = this.totals[0][this.index + '_orig'];
            }

            if (total === 0 || total === undefined || total === '' || total === null) {
                return false;
            }

            var value  = row[this.index + '_orig'];
            var result = false;
            
            if (this.valueType === 'percent') {
                result = ((value / 100) * 100).toFixed(1);
            } else if (this.valueType === 'money' || this.valueType === 'number') {
                result = ((value / total) * 100).toFixed(1);
            }
            
            return result;
        }
    });
});
