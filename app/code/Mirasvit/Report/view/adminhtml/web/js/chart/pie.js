define([
    './abstract',
    'jquery',
    'underscore'
], function (Abstract, $, _) {
    return Abstract.extend({
        
        getChartConfig: function () {
            return {
                type:    'doughnut',
                options: {
                    title:               {
                        display: false
                    },
                    legend:              {
                        display: false
                    },
                    responsive:          true,
                    maintainAspectRatio: false
                }
            };
        },
        
        updateData: function () {
            if (!this.chart) {
                return;
            }
            
            var data = {
                labels:   this.getLabels(),
                datasets: this.getDataSets()
            };
            
            if (this.chart.data !== data) {
                this.chart.data = data;
                this.chart.update(0, true);
            }
        },
        
        
        getDataSets: function () {
            var sets = [];
            
            _.each(this.columns, function (column) {
                if (column.isInternal) {
                    return;
                }
                
                var set = {
                    label:           column.label,
                    stack:           column.index,
                    backgroundColor: [],
                    borderColor:     [],
                    borderWidth:     1,
                    data:            [],
                    hidden:          !column.isVisible
                };
                
                _.each(this.rows, function (row, i) {
                    var value = this.getCellValue(column, row);
                    set.data.push(value);
                    set.backgroundColor.push(this.getColor(i));
                }, this);
                
                sets.push(set);
            }, this);
            
            return sets;
        },
        
        getColor: function (idx) {
            var colors = [
                '#97CC64',
                '#FF5A3E',
                '#77B6E7',
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
            ];
            
            while (idx >= colors.length && colors.length > 0) {
                idx = idx - colors.length;
            }
            
            return colors[idx];
        }
    });
});