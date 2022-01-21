/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/grid/columns/column',
    'underscore'
], function (Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_RequisitionList/grid/cells/text_multiline',
            lineClassPrefix: 'cell-label-line-',
            dataFields: []
        },

        /**
         * Get column record labels
         *
         * @param {Object} record
         * @returns {Array}
         */
        getLabels: function (record) {
            return _.chain(record)
                .pick(this.dataFields)
                .map(function (label, index) {
                    return {
                        label: label,
                        index: index
                    };
                })
                .value();
        },

        /**
         * Get line class
         *
         * @param {Number} index
         * @returns {String}
         */
        getLineClass: function (index) {
            return this.lineClassPrefix + index;
        }
    });
});
