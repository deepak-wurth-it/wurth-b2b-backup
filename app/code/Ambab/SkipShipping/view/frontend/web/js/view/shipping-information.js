/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/sidebar'
], function ($, Component, quote, stepNavigator, sidebarModel) {
    'use strict';
    var displayShipping= window.checkoutConfig.ambabSkipShippingSettings.hideShippingCharge;
    var moduleEnabled= window.checkoutConfig.ambabSkipShippingSettings.isEnabled;
    return Component.extend({
        defaults: {
            displayShipping: displayShipping,
            moduleEnabled :moduleEnabled,
            template: 'Magento_Checkout/shipping-information'
        },

        /**
         * @return {Boolean}
         */
        ismoduleEnabled: function () {
            return this.moduleEnabled ; //check if SkipShipping module enabled
        },
        
        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
        },

        /**
         * @return {String}
         */
        getShippingMethodTitle: function () {
            var shippingMethod = quote.shippingMethod();

            return shippingMethod ? shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'] : '';
        },

        /**
         * Back step.
         */
        back: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping');
        },

        /**
         * Back to shipping method.
         */
        backToShippingMethod: function () {
            sidebarModel.hide();
            stepNavigator.navigateTo('shipping', 'opc-shipping_method');
        }
    });
});
