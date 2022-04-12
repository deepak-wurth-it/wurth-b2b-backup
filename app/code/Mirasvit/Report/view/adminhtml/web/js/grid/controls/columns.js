// extend for pass visible columns to data provider
// extends default component for sort columns by name
define([
    'underscore',
    'Magento_Ui/js/grid/controls/columns'
], function (_, Columns) {
    'use strict';
    
    return Columns.extend({
        defaults: {
            template: 'report/grid/controls/columns',
            exports:  {
                columns: '${ $.provider }:params.columns'
            }
        },
        
        addColumns: function (columns) {
            this._super(columns);
            
            //this.elems(_.filter(this.elems(), {isFilterOnly: false}));
            
            return this;
        },
        
        tables: function () {
            var tables = {};
            _.each(this.elems(), function (elem) {
                var table = elem.table;
                
                if (tables[table] === undefined) {
                    tables[table] = {
                        label:   table,
                        columns: []
                    };
                }
                
                tables[table].columns.push(elem);
            }.bind(this));
            
            
            return _.values(tables);
        },
        
        isDisabled: function (column) {
            var disabled = this._super(column);
            
            return disabled
                || column.dataType === 'actions'
                || column.isDimension;
        },
        
        countVisible: function () {
            var columns = [];
            _.each(this.elems.filter('visible'), function (column) {
                columns.push(column.index);
            });
            
            if (this.get('columns') === undefined || columns.length > this.get('columns').length) {
                // set and reload
                this.set('columns', columns);
            }
            
            return this.elems.filter('visible').length;
        }
    });
});
