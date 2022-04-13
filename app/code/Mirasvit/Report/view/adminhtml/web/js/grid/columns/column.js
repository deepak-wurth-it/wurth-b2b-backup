define([
    'Magento_Ui/js/grid/columns/column',
    'uiRegistry'
], function (Column) {
    'use strict';
    
    return Column.extend({
        defaults: {
            bodyTmpl: 'report/grid/cells/column'
        },
        
        getComparisonLabel: function (record) {
            if (record['c|' + this.index] !== undefined) {
                return record['c|' + this.index];
            }
            
            return null;
        },
        
        getDiffLabel: function (record) {
            if (record['c|' + this.index + '_orig'] !== undefined && record[this.index + '_orig'] !== undefined) {
                var a = Math.abs(record[this.index + '_orig']);
                var b = Math.abs(record['c|' + this.index + '_orig']);
                
                if (a === b) {
                    return null;
                }
                
                if (a === 0) {
                    return '∞';
                }
                
                return Math.round((a - b) / a * 100) + "%";
            }
            
            return null;
        },
        
        getDiffSign: function (record) {
            if (record['c|' + this.index + '_orig'] !== undefined && record[this.index + '_orig'] !== undefined) {
                var a = Math.abs(record[this.index + '_orig']);
                var b = Math.abs(record['c|' + this.index + '_orig']);
                
                if (a > b) {
                    return "positive";
                } else {
                    return "negative";
                }
            }
        }
    });
});
