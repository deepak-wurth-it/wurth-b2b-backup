define([
    'underscore',
    'Magento_Ui/js/grid/listing'
], function (_, Listing) {
    'use strict';
    
    return Listing.extend({
        defaults: {
            template: 'report/grid/listing',
            imports:  {
                dynamicColumns:  '${ $.provider }:data.dynamicColumns',
                dimensionColumn: '${ $.provider }:data.dimensionColumn',
                columns:         '${ $.provider }:data.columns',
                totals:          '${ $.provider }:data.totals'
            },
            
            listens: {
                dynamicColumns:  'onChangeDynamicColumns',
                dimensionColumn: 'onChangeDimensionColumn'
            }
        },
        
        initObservable: function () {
            this._super()
                .track({
                    totals: []
                });
            
            return this;
        },
        
        updateVisible: function () {
            this.visibleColumns = [];
            
            _.each(this.elems.filter('visible'), function (elem) {
                if (!elem.isFilterOnly) {
                    this.visibleColumns.push(elem);
                }
            }.bind(this));
            
            return this;
        },
        
        onChangeDynamicColumns: function () {
            _.each(this.elems(), function (item) {
                if (this.dynamicColumns[item.index] !== undefined) {
                    item.visible = this.dynamicColumns[item.index].visible;
                    this.positions[item.index] = this.dynamicColumns[item.index].sort;
                } else {
                    // offset all other columns
                    this.positions[item.index] = 10;
                }
            }, this);
            
            this.applyPositions(this.positions);
        },
        
        onChangeDimensionColumn: function () {
            _.each(this.elems(), function (item) {
                if (this.dimensionColumn === 'mst_reports_postcode|state' && item.index === 'sales_order_address|country') {
                    item.visible = true;
                    this.positions[item.index] = 0;
                } else if (this.dimensionColumn === 'mst_reports_postcode|place' && item.index === 'sales_order_address|country') {
                    item.visible = true;
                    this.positions[item.index] = 3;
                } else if (this.dimensionColumn === 'mst_reports_postcode|place' && item.index === 'mst_reports_postcode|state') {
                    item.visible = true;
                    this.positions[item.index] = 2;
                } else if (this.dimensionColumn === 'mst_reports_postcode|postcode' && item.index === 'sales_order_address|country') {
                    item.visible = true;
                    this.positions[item.index] = 4;
                } else if (this.dimensionColumn === 'mst_reports_postcode|postcode' && item.index === 'mst_reports_postcode|state') {
                    item.visible = true;
                    this.positions[item.index] = 3;
                } else if (this.dimensionColumn === 'mst_reports_postcode|postcode' && item.index === 'mst_reports_postcode|place') {
                    item.visible = true;
                    this.positions[item.index] = 2;
                } else if (item.index === this.dimensionColumn) {
                    item.visible = true;
                    this.positions[item.index] = 0;
                } else {
                    if (item.isDimension === true) {
                        item.visible = false;
                    }
                    // offset all other columns
                    this.positions[item.index] = 10;
                }
            }, this);
            
            this.applyPositions(this.positions);
        },
        
        updatePositions: function () {
            var positions = {};
            
            this.elems.each(function (elem, index) {
                if (elem.isHidden) { // we do not want override user selection
                    elem.visible = false;
                }
                if (elem.index === 'actions') {
                    positions[elem.index] = 100000;
                } else {
                    positions[elem.index] = index;
                }
            });
            
            this.set('positions', positions);
            
            return this;
        }
    });
});
