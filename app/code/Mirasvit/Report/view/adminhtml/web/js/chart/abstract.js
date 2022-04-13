define([
    'uiClass',
    'jquery',
    'underscore'
], function (Class, $, _) {
    return Class.extend({
        wrapSelector: '.report__chart-wrap',
        
        chart: null,
        
        rows:    [],
        columns: [],
        
        scaleTypes: ['money', 'number', 'percent'],
        
        ensureChart: function () {
            if (!document.getElementById('chart_canvas')) {
                return false;
            }
            
            if (!this.getLabels().length) {
                $(this.wrapSelector).hide();
                return false;
            } else {
                $(this.wrapSelector).show();
            }
            
            if (this.chart) {
                return true;
            }
            
            var context = document.getElementById('chart_canvas').getContext('2d');
            
            this.chart = new Chart(context, this.getChartConfig());
        },
        
        getChartConfig: function () {
        
        },
        
        setRows: function (rows) {
            this.rows = rows;
            
            this.ensureChart();
            this.updateData();
        },
        
        setParams: function (params) {
            this.params = params;
        },
        
        setColumns: function (columns) {
            this.columns = columns;
            
            this.ensureChart();
            this.updateData();
        },
        
        getLabels: function () {
            var labels = [];
            
            _.each(this.rows, function (obj) {
                _.each(_.where(this.columns, {isDimension: true}), function (column) {
                    labels.push(this.getCellValue(column, obj) + "");
                }, this);
            }, this);
            
            return labels;
        },
        
        getCellValue: function (column, row, prefix) {
            var index = column.index;
            
            if (prefix !== undefined) {
                index = prefix + index;
            }
            
            var value = row[index];
            
            var type = column.type;
            
            if (_.indexOf(this.scaleTypes, type) !== -1) {
                value = row[index + '_orig'];
                value = parseFloat(parseFloat(value).toFixed(2));
            } else if (type === 'date') {
                value = new Date(Date.parse(value));
            } else if (type === 'country') {
                value = value + '';
            } else {
                value = column.model.getLabel(row);
            }
            
            return value;
        },
        
        destroy: function () {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    });
});