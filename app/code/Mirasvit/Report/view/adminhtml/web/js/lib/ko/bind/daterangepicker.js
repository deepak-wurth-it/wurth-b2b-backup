define([
    'ko',
    'underscore',
    'jquery',
    'Mirasvit_Report/js/lib/moment.min',
    'Mirasvit_Report/js/lib/daterangepicker/daterange',
    'Mirasvit_Report/js/lib/daterangepicker/daterangepicker'
], function (ko, _, $, moment) {
    'use strict';

    ko.bindingHandlers.daterangepicker = {
        init: function (el, valueAccessor) {
            var config = valueAccessor(),
                observableFrom,
                observableTo,
                observableCompareFrom,
                observableCompareTo,
                observableComparisonEnabled,
                options = {};

            observableFrom = config.storageFrom;
            observableTo = config.storageTo;
            observableCompareFrom = config.storageCompareFrom;
            observableCompareTo = config.storageCompareTo;
            observableComparisonEnabled = config.storageComparisonEnabled;

            _.extend(options, config.options);

            _.extend(options, {
                mode:      'tworanges',
                starts:    1,
                calendars: 3,
                inline:    true,
                apply:     function (obj) {
                    observableFrom(moment(obj.dr1from).format('YYYY-MM-DD'));
                    observableTo(moment(obj.dr1to).format('YYYY-MM-DD'));

                    observableComparisonEnabled(obj.comparisonEnabled);
                    if (obj.comparisonEnabled) {
                        observableCompareFrom(moment(obj.dr2from).format('YYYY-MM-DD'));
                        observableCompareTo(moment(obj.dr2to).format('YYYY-MM-DD'));
                    } else {
                        observableCompareFrom(null);
                        observableCompareTo(null);
                    }
                }
            });

            $(el).DateRangesWidget(options);
        },

        /**
         * Reads target observable from valueAccessor and writes its' value to el.value
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         */
        update: function (el, valueAccessor) {
            var config = valueAccessor(),
                observable,
                value;

            observable = typeof config === 'object' ?
                config.storage :
                config;
        }
    }
})
;