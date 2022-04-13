define([
    './column'
], function (Column) {
    return Column.extend({
        opacity:       .5,
        borderOpacity: 1,
        
        getChartConfig: function () {
            var config = this._super();
            config.type = 'line';
            
            return config;
        }
    });
});