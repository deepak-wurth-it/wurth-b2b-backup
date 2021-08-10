define(['jquery'], function($) {
    "use strict";

    return {
        /**
         * Decode URL encoded string.
         *
         * @param {string} value
         * @return {string}
         */
        decode: function(value) {
            return decodeURIComponent(value.replace(/\+/g, ' '));
        },

        /**
         * Build URL suitable for AJAX request.
         *
         * @param {Object} data
         * @param {string} param
         * @param {string|number} value
         * @param {boolean} isPaging
         * @return {string}
         */
        prepareUrl: function(data, param, value, isPaging) {
            var urlPaths  = window.mNavigationConfigData.cleanUrl.replace('&amp;', '&').split('?'),
                baseUrl   = urlPaths[0],
                replaced  = false,
                paramData = {},
                parameters, i;

            if (isPaging !== 'undefined') {
                var self = this;
                $.each(data, function(index, parameter) {
                if (typeof index == 'number') {
                    param = parameter.split("=");
                    paramData[param[0]] = self.decode(param[1]);
                } else {
                    paramData[index] = self.decode(parameter);
                }
                    replaced = true;
                });
            }

            if (!replaced) {
                $.each(data, function (key, val) {
                    if(key == param) {
                        paramData[key] = this.decode(val);
                    }
                }.bind(this));
                replaced = true;
            }

            if (!replaced && value != '') {
                paramData[param] = this.decode(value);
            }

            paramData = $.param(paramData);

            return baseUrl + (paramData.length ? '?' + paramData : '');
        },

        /**
         * Seo filter.
         *
         * @param {string} cleanUrl
         * @return {string}
         */
        getLink: function (cleanUrl) {
            var dataAfterQuestionMark, linkFirstPart, link;

            if (cleanUrl === undefined) {
                console.log('Something went wrong!');
                return window.location.href;
            }

            dataAfterQuestionMark = cleanUrl.split('?');
            linkFirstPart = window.location.href.split('?');

            if (linkFirstPart[0] !== undefined) {
                link = linkFirstPart[0];
            } else {
                link = window.location.href;
            }

            if (dataAfterQuestionMark[1] !== undefined) {
                link = link + '?' + dataAfterQuestionMark[1];
            }

            return link;
        }
    };
});
