define([
    'jquery',
    'underscore',
    'Mirasvit_LayeredNavigation/js/lib/qs',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/apply-button'
], function ($, _, qs, config, applyButton) {
    "use strict";

    return function (url, $initiator, force) {
        if (config.isSeoFilterEnabled()) {
            url = config.getFriendlyClearUrl();
        }

        const actualParams = qs.parse(window.location.search.substr(1))
        const filtersParams = getFilters();

        const params = _.extend(actualParams, filtersParams, {mode: 'by_button_click'});

        url = url.split('?')[0];
        const query = qs.stringify(params);

        if (query) {
            url += "?" + query;
        }

        applyButton.move($initiator);
        applyButton.show();
        applyButton.load(url, force);
    };

    function getFilters() {
        let filters = {};

        _.each($('[data-mst-nav-filter]'), function (filter) {
            const $filter = $(filter);
            const filterName = $filter.attr('data-mst-nav-filter');

            let filterValues = [];

            switch (filterName) {
                case 'price': //@todo
                    let filterValue = getSliderPriceFilterValue($filter);
                    if (filterValue) {
                        filterValues.push(filterValue);
                        filters[filterName] = filterValues.join(',');
                    }
                    break;

                default:
                    _.each($('[data-element = filter]._checked', $filter), function (item) {
                        let $item = $(item);

                        let filterValue = $item.attr('data-value');
                        filterValues.push(filterValue);
                    }.bind(this));

                    if (filterValues.length > 0) {
                        filters[filterName] = filterValues.join(',');
                    }
            }
        }.bind(this));

        return filters;
    }

    function getSliderPriceFilterValue($el) {
        let priceWidget = $el.data("mst-navSliderRenderer");

        if (!priceWidget) {
            return false;
        }

        let minVal = parseFloat(priceWidget.options.min).toFixed(2);
        let maxVal = parseFloat(priceWidget.options.max).toFixed(2);
        let fromVal = parseFloat(priceWidget.from).toFixed(2);
        let toVal = parseFloat(priceWidget.to).toFixed(2);

        if (fromVal === minVal && toVal === maxVal) {
            return false;
        }

        return fromVal + "-" + toVal;
    }
});
