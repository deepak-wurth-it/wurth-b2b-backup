define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/cache',
    'Mirasvit_LayeredNavigation/js/action/update-content',
    'Mirasvit_LayeredNavigation/js/action/apply-filter',
    'Mirasvit_LayeredNavigation/js/apply-button',
    'Mirasvit_LayeredNavigation/js/ajax/pagination',
    'Mirasvit_LayeredNavigation/js/helper/overlay'
], function ($, config, cache, updateContent, applyFilter, applyButton, initPaging, overlay) {

    /**
     * Widget responsible for initializing AJAX layered navigation, toolbar and paging.
     */
    $.widget('mst.layeredNavigation', {
        options: {
            cleanUrl:                   '',
            overlayUrl:                 '',
            isSeoFilterEnabled:         false,
            isFilterClearBlockInOneRow: false,
            isHorizontalByDefault:      false
        },

        _create: function () {
            window.mNavigationConfigData = this.options;

            this._bind();

            initPaging();
        },

        _bind: function () {
            $(document).on(config.getAjaxCallEvent(), function (event, url, force) {
                applyButton.hide();

                let cachedData = cache.getData(url);
                if (cachedData) {
                    this.updatePageInstantlyMode(url, cachedData);
                } else {
                    this.requestUpdate(url, force);
                }
                this.addBrowserHistory(url);
            }.bind(this));

            if (typeof window.history.replaceState === 'function') {
                /** Browser back button */
                window.onpopstate = function (e) {
                    if (e.state) {
                        window.location.href = e.state.url;
                    }
                }.bind(this);
            }
        },

        updatePageInstantlyMode: function (url, result) {
            updateContent.updateInstantlyMode(result, window.mNavigationConfigData.isHorizontalByDefault);

            initPaging();
        },

        addBrowserHistory: function (url) {
            url = this.deleteForceModeQueryParam(url);
            window.history.pushState({url: url}, '', url);

            return true;
        },

        deleteForceModeQueryParam: function (url) {
            url = url.replace("?mstNavForceMode=instantly", "");
            url = url.replace("?mstNavForceMode=instantly&", "?");
            url = url.replace("&mstNavForceMode=instantly&", "&");
            url = url.replace("&mstNavForceMode=instantly", "");

            url = url.replace("?mstNavForceMode=by_button_click", "");
            url = url.replace("?mstNavForceMode=by_button_click&", "?");
            url = url.replace("&mstNavForceMode=by_button_click&", "&");
            url = url.replace("&mstNavForceMode=by_button_click", "");

            return url;
        },

        requestUpdate: function (url, force) {
            overlay.show();

            let data = {isAjax: true}
            if (force) {
                data.mstNavForceMode = 'instantly';
            }
            $.ajax({
                url:      url,
                data:     data,
                cache:    true,
                method:   'GET',
                success:  function (result) {
                    try {
                        result = $.parseJSON(result);
                        cache.setData(url, result);
                        this.updatePageInstantlyMode(url, result);
                    } catch (e) {
                        if (window.mNavigationAjaxscrollCompatibility !== 'true') {
                            console.log(e);

                            window.location = url;
                        }
                    }
                }.bind(this),
                error:    function () {
                    window.location = url;
                }.bind(this),
                complete: function () {
                    overlay.hide();
                }.bind(this)
            });
        }

    });

    return $.mst.layeredNavigation;
});
