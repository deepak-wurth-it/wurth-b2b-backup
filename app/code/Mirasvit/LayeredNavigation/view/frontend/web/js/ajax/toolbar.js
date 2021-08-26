define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/helper/url',
    'Mirasvit_LayeredNavigation/js/action/apply-filter',
    'Magento_Catalog/js/product/list/toolbar'
], function ($, config, url, applyFilter) {
    'use strict';

    /**
     * We rewrite this widget (requirejs-config.js) to enable AJAX for toolbar functionality.
     */
    $.widget('mst.productListToolbarForm', $.mage.productListToolbarForm, {

        /**
         * @param {String} paramName
         * @param {*} paramValue
         * @param {*} defaultValue
         */
        changeUrl: function (paramName, paramValue, defaultValue) {
            return config.isAjax()
                ? this._changeAjaxUrl(paramName, paramValue, defaultValue)
                : this._changeStdUrl(paramName, paramValue, defaultValue);

        },

        /**
         * Change URL when AJAX enabled.
         *
         * @param {String} paramName
         * @param {*} paramValue
         * @param {*} defaultValue
         */
        _changeAjaxUrl: function (paramName, paramValue, defaultValue) {
            var link;

            // ignore duplicate requests
            if (this._isToolbarLock()) {
                return false;
            }

            window.mNavigationConfigData.cleanUrl = this._getToolbarCleanUrl(
                paramName,
                paramValue,
                defaultValue,
                this.options
            );

            //seo filter
            link = url.getLink(window.mNavigationConfigData.cleanUrl);

            applyFilter.apply(link);
        },

        /**
         * Change URL without AJAX.
         *
         * @param {String} paramName
         * @param {*} paramValue
         * @param {*} defaultValue
         */
        _changeStdUrl: function (paramName, paramValue, defaultValue) {
            var decode    = window.decodeURIComponent,
                urlPaths  = this.options.url.split('?'),
                baseUrl   = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? url.decode(parameters[1])
                    : '';
            }

            paramData[paramName] = paramValue;

            if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                delete paramData[paramName];
            }

            paramData = $.param(paramData);

            //fix incorrect symbols in url
            paramData = paramData.replace(/%2C/g, ",");

            location.href = baseUrl + (paramData.length ? '?' + paramData : '');
        },

        _getToolbarCleanUrl: function (paramName, paramValue, defaultValue, options) {
            var decode    = window.decodeURIComponent,
                urlPaths  = options.url.split('?'),
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    //? decode(parameters[1].replace(/\+/g, '%20'))
                    ? parameters[1]
                    : '';
            }
            paramData[paramName] = paramValue;

            return url.prepareUrl(paramData, paramName, paramData[paramName] ? paramData[paramName] : '');
        },

        /**
         * Page contains 2 toolbars: before and after product listing, hence, multiple instances of this widget exists.
         * We use this hack in order to stop multiple requests from being called.
         *
         * @return {Boolean}
         */
        _isToolbarLock: function () {
            if (window.blockToolbar) {
                return true;
            }

            window.blockToolbar = true;
            setTimeout(function () {
                window.blockToolbar = false;
            }, 300);

            return false;
        }
    });

    return $.mst.productListToolbarForm;
});
