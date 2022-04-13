define([
    'Magento_Ui/js/grid/filters/filters',
    'uiLayout'
], function (Filters, layout) {
    'use strict';
    
    return Filters.extend({
        defaults: {
            templates: {
                filters: {
                    base:   {
                        parent:    '${ $.$data.filters.name }',
                        name:      '${ $.$data.column.index }',
                        provider:  '${ $.$data.filters.name }',
                        dataScope: '${ $.$data.column.index }',
                        label:     '${ $.$data.column.label }',
                        imports:   {
                            visible:      '${ $.$data.column.name }:visible',
                            isFilterOnly: '${ $.$data.column.name }:isFilterOnly'
                        }
                    },
                    select: {
                        component:     'Mirasvit_Report/js/grid/filters/multiselect',
                        template:      'ui/grid/filters/elements/ui-select',
                        options:       '${ JSON.stringify($.$data.column.options) }',
                        caption:       ' ',
                        filterOptions: true
                    }
                }
            }
        },
        
        isFilterVisible: function (filter) {
            return this._super(filter);// || filter.isFilterOnly;
        }
    });
});
