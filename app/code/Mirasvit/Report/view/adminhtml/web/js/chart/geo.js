define([
    './abstract',
    'jquery',
    'underscore'
], function (Abstract, $, _) {
    return Abstract.extend({
        isLoaded:  false,
        chartType: 'geo',
        
        map:     null,
        markers: [],
        
        ensureChart: function () {
            if (this.isLoaded) {
                this._ensureChart();
            } else {
                require([
                    '//www.gstatic.com/charts/loader.js?nomin',
                    '//maps.googleapis.com/maps/api/js?key=AIzaSyDOcoIgRmy7_yv_30OuqvZkulTwF2KJMiI&v=3.exp&signed_in=true&key=AIzaSyDOcoIgRmy7_yv_30OuqvZkulTwF2KJMiI'
                ], function () {
                    google.charts.load('current', {
                        'packages':   ['geochart'],
                        'mapsApiKey': 'AIzaSyBr3pLR_c6dttTc4X1zYhTdduVEiktpRHM'
                    });
                    google.charts.setOnLoadCallback(function () {
                        this.isLoaded = true;
                    }.bind(this));
                }.bind(this));
            }
        },
        
        _ensureChart: function () {
            if (!document.getElementById('map_div')) {
                return false;
            }
            
            $('#chart_canvas').remove();
            
            
            if (this.chartType === 'geo') {
                $('#map_div').hide();
                $('#geo_div').show();
            }
            
            if (this.chartType === 'map') {
                $('#geo_div').hide();
                $('#map_div').show();
            }
            
            if (!this.chart) {
                this.chart = new google.visualization.GeoChart(document.getElementById('geo_div'));
            }
            
            if (!this.map) {
                this.map = new google.maps.Map(document.getElementById('map_div'), {
                    mapTypeId: google.maps.MapTypeId.TERRAIN,
                    zoom:      3,
                    center:    new google.maps.LatLng(40, 0)
                });
            }
        },
        
        getChartConfig: function () {
            var dimension = this.params.dimension;
            if (!dimension) {
                return {};
            }
            
            var type = dimension.split('|')[1];
            
            if (type === 'country') {
                this.chartType = 'geo';
                return {}
            } else if (type === 'state') {
                this.chartType = 'geo';
                return {
                    region:                    this.mostPopularCountry(),
                    resolution:                'provinces',
                    enableRegionInteractivity: true,
                    displayMode:               'regions'
                };
            } else if (type === 'place') {
                this.chartType = 'geo';
                return {
                    region:                    this.mostPopularCountry(),
                    resolution:                'provinces',
                    enableRegionInteractivity: true,
                    displayMode:               'markers'
                };
            } else if (type === 'postcode') {
                this.chartType = 'map';
            }
            
            this.ensureChart();
            
            return {};
        },
        
        updateData: function () {
            if (this.chartType === 'geo' && this.chart) {
                var data = new google.visualization.DataTable();
                
                var rows = [];
                
                _.each(this.columns, function (column) {
                    if (column.isDimension) {
                        data.addColumn('string', column.label);
                    }
                    if (column.isVisible) {
                        data.addColumn('number', column.label);
                    }
                }, this);
                
                
                _.each(this.rows, function (row, i) {
                    var item = [];
                    var isValid = true;

                    _.each(this.columns, function (column) {
                        var value = row[column.index + '_orig'];

                        if ((column.isDimension || column.isVisible) && !value) {
                            isValid = false;
                        }

                        if (column.isDimension) {
                            item.push(value);
                        }
                        if (column.isVisible) {
                            value = parseFloat(parseFloat(value).toFixed(2));
                            item.push(value);
                        }
                    }.bind(this));

                    if (isValid) {
                        rows.push(item);
                    }
                }, this);
                
                data.addRows(rows);
                
                this.chart.draw(data, this.getChartConfig());
            }
            
            if (this.chartType === 'map' && this.map) {
                _.each(this.markers, function (marker) {
                    marker.setMap(null);
                });
                this.markers = [];
                
                var latLngList = [];
                _.each(this.rows, function (row) {
                    var pos = {
                        lat: parseFloat(row['mst_reports_postcode|lat_orig']),
                        lng: parseFloat(row['mst_reports_postcode|lng_orig'])
                    };
                    
                    if (pos.lat && pos.lng) {
                        latLngList.push(pos);
    
                        var marker = new google.maps.Marker({
                            position: pos,
                            map:      this.map,
                            hint:     '***'
                        });
                        this.markers.push(marker);
                    }
                }, this);
                this.getChartConfig();
                
                // center and zoom map based on markers
                var bounds = new google.maps.LatLngBounds();
                for (var i = 0, LtLgLen = latLngList.length; i < LtLgLen; i++) {
                    bounds.extend(latLngList[i]);
                }
                this.map.fitBounds(bounds);
            }
        },
        
        mostPopularCountry: function () {
            var countries = {};
            _.each(this.rows, function (row) {
                var country = _.find(row, function (value, column) {
                    if (column.split('|')[1] === 'country_orig') {
                        return value;
                    }
                });
                countries[country] = countries[country] ? countries[country] + 1 : 1;
            });
            
            return Object.keys(countries).reduce(function (a, b) {
                return countries[a] > countries[b] ? a : b
            });
        }
    });
});