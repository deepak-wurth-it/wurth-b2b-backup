define([
    'jquery',
    'mage/utils/wrapper',
    'uiRegistry'
], function ($, wrapper) {
    "use strict";

    return function (shippingRatesValidationRules) {
        shippingRatesValidationRules.getObservableFields = wrapper.wrap(shippingRatesValidationRules.getObservableFields,
            function (originalAction) {
                var fields = originalAction();
                fields.push('street');
                fields.push('city');
                fields.push('region_id');

                return fields;
            }
        );

        return shippingRatesValidationRules;
    };
});
