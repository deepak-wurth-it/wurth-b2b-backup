define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    './chart/column',
    './chart/line',
    './chart/pie',
    './chart/geo'
], function ($, _, ko, Component, ColumnChart, LineChart, PieChart, GeoChart) {
    'use strict';
    
    return Component.extend({
        defaults: {
            template: 'report/chart',
            
            provider: '${ $.provider }:data',
            
            imports: {
                rows:            '${ $.provider }:data.items',
                columnsProvider: '${ $.columnsProvider }:elems',
                dimensionColumn: '${ $.provider }:data.dimensionColumn',
                params:          '${ $.provider }:params'
                //pdata:           '${ $.provider }:data'
            },
            listens: {
                rows:            'updateRows',
                columnsProvider: 'updateColumns',
                dimensionColumn: 'updateColumns'
            },
            tracks:  {
                chartType: true
            }
        },
        
        chartType:    'empty',
        typeInstance: null,
        
        primaryColors: [
            '#97CC64',
            '#FF5A3E',
            '#77B6E7'
        ],
        
        colors: [
            '#FFD963',
            '#A9B9B8',
            '#DC9D6B',
            '#8549ba',
            '#00a950',
            '#166a8f',
            '#acc236',
            '#537bc4',
            '#f53794',
            '#f67019',
            '#4dc9f6'
        ],
        
        initialize: function () {
            this._super();
            
            _.bindAll(this, 'setChartType');
            
            this.setChartType(this.chartType);
        },
        
        setChartType: function (type) {
            this.set('chartType', type);
            
            if (this.typeInstance) {
                this.typeInstance.destroy();
            }
            
            switch (type) {
                case 'column':
                    this.typeInstance = new ColumnChart();
                    break;
                case 'line':
                    this.typeInstance = new LineChart();
                    break;
                case 'pie':
                    this.typeInstance = new PieChart();
                    break;
                case 'geo':
                    this.typeInstance = new GeoChart();
                    break;
            }
            
            if (this.chartType === 'geo') {
                this.typeSwitcher = ['geo'];
            } else {
                this.typeSwitcher = ['column', 'line', 'pie'];
            }
            
            this.updateRows();
            this.updateColumns();
        },
        
        updateRows: function () {
            if (this.typeInstance) {
                this.typeInstance.setParams(this.params);
                this.typeInstance.setRows(this.rows);
            }
        },
        
        updateColumns: function () {
            var columns = [];
            _.each(this.columnsProvider, function (column, idx) {
                var isVisible = _.indexOf(this.defaultColumns, column.index) >= 0
                    && column.index !== this.dimensionColumn;
                
                var data = {
                    index:       column.index,
                    label:       column.label,
                    color:       this.getColor(idx, column),
                    type:        column.valueType,
                    isVisible:   isVisible,
                    isDimension: column.index === this.dimensionColumn,
                    isInternal:  column.index === this.dimensionColumn || column.isFilterOnly || !column.visible || column.index === 'actions',
                    model:       column
                };
                
                columns.push(data);
            }, this);
            
            if (this.typeInstance) {
                this.typeInstance.setColumns(columns);
            }
        },
        
        getColor: function (idx, column) {
            // set of default columns
            if (_.indexOf(this.defaultColumns, column.index) >= 0) {
                idx = _.indexOf(this.defaultColumns, column.index);
                
                return this.primaryColors[idx];
            } else {
                
                while (idx >= this.colors.length && this.colors.length > 0) {
                    idx = idx - this.colors.length;
                }
            }
            
            return this.colors[idx];
        }
    });
});