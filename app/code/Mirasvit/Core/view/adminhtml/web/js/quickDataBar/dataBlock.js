define([
    'uiComponent',
    'ko',
    'underscore',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Mirasvit_Core/js/lib/chart'
], function (Component, ko, _, $, modal) {
    'use strict';

    return Component.extend({
        defaults: {
            imports: {
                dateRange: 'mstQuickDataBar:dateRange'
            },
            listens: {
                dateRange: 'updateData'
            },

            loading: false,

            updateURL: '',
            block:     '',

            data: {
                label:     '',
                value:     '',
                sparkline: []
            }
        },

        initObservable: function () {
            this._super();

            this.loading = ko.observable(this.loading)
            this.data = ko.observable(this.data)

            return this;
        },

        handleClick: function () {
            const options = {
                type:    'popup',
                title:   this.data().label,
                buttons: []
            };
            const $div = $('<div />')
            const $canvas = $('<canvas />');
            $div.html($canvas);
            var popup = modal(options, $div);
            $div.modal('openModal');

            const labels = _.map(this.data().sparkline, function (v, key) {
                return key
            });

            const values = _.map(this.data().sparkline, function (v) {
                return v
            });

            const config = {
                type:    'bar',
                data:    {
                    labels:   labels,
                    datasets: [{
                        label:           this.data().label,
                        backgroundColor: 'rgba(60, 98, 179, 0.9)',
                        data:            values
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    layout: {
                        padding: {
                            left:   0,
                            right:  0,
                            top:    30,
                            bottom: 30
                        }
                    }
                }
            };

            const myChart = new Chart(
                $canvas[0],
                config
            );
        },

        updateData: function () {
            const requestData = {
                block:     this.block,
                dateRange: this.dateRange
            };

            this.loading(true);

            $.ajax({
                type:     'GET',
                url:      this.updateURL,
                data:     requestData,
                dataType: 'json',

                success: function (response) {
                    this.data(response.data)

                    this.loading(false);
                }.bind(this)
            });
        },

        buildSparkline: function (sparklineValues) {
            if (!sparklineValues) return []

            const values = _.values(sparklineValues)

            const max = _.max(values)
            const len = values.length

            if (len === 0) return []

            return _.map(values, (v, idx) => {
                const x = idx * 7 + (224 - values.length * 7) + 'px';
                const y = max > 0 ? 48 - (v / max) * 48 + 'px' : '48px'

                return {
                    x: x,
                    y: y
                };
            });
        }
    });
});
