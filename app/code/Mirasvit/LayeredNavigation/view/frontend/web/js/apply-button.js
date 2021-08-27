define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/action/apply-filter-instant-mode',
    'Mirasvit_LayeredNavigation/js/config',
    'Mirasvit_LayeredNavigation/js/cache'
], function ($, applyFilter, config, cache) {
    'use strict';

    return {
        selector:       '[data-element="mst-nav__applyButton"]',
        countSelector:  '[data-count]',
        buttonSelector: '[data-apply]',

        label1Selector: '[data-label-1]',
        labelNSelector: '[data-label-n]',

        $el: function () {
            return $(this.selector);
        },

        $count: function () {
            return $(this.countSelector, this.$el());
        },

        $button: function () {
            return $(this.buttonSelector, this.$el());
        },

        clear: function () {
            this.$count().html('');
            this.$button().attr('data-apply', '');
        },

        show: function () {
            this.$el().show();
        },

        hide: function () {
            this.$el().hide();
        },

        showLoader: function () {
            this.$el().addClass('_loading');
        },

        hideLoader: function () {
            this.$el().removeClass('_loading');
        },

        move: function ($initiator) {
            let x = $initiator.offset().left + $initiator.width();

            let baseElement = this.getBaseElement($initiator);
            let y = baseElement.offset().top - this.$el().height() / 2 + baseElement.height() / 2;

            this.$el().css("left", x)
                .css("top", y);
        },

        getBaseElement: function ($initiator) {
            let filterName = $initiator.closest('[data-mst-nav-filter]').data('mst-nav-filter');
            if (filterName === 'color') {
                return $initiator.children().first();
            }
            if (filterName === 'size') {
                return $initiator.children().first();
            }
            if (filterName === 'price') {
                return $('[data-element="slider"]', $initiator).first();
            }

            return $initiator
        },

        update: function (result) {
            let productsHtml = result['products'];
            let applyFilterUrl = result['url'];
            let productsCount = result['products_count'];

            this.$button().attr('data-apply', applyFilterUrl);
            this.$count().html(productsCount);
            this.toggleLabel(productsCount);

            if (productsHtml.length > 0) {//todo what for?
                $(config.getAjaxProductListWrapperId()).replaceWith(result['products']);
                $(config.getAjaxProductListWrapperId()).trigger('contentUpdated');
            }

            this.$button().on('click', function (e) {
                e.stopImmediatePropagation();

                const url = this.$button().attr('data-apply');
                applyFilter(url);
            }.bind(this))
        },

        load: function (url, force) {
            this.clear();
            let cacheKey = 'applyingMode:' + url;
            let cachedData = cache.getData(cacheKey);
            if (cachedData) {
                this.update(cachedData);
            } else {
                this._request(url, force);
            }
        },

        _request: function (url, force) {
            this.showLoader();

            let data = {isAjax: true}
            if (force) {
                data.mstNavForceMode = 'by_button_click';
            }

            $.ajax({
                url:      url,
                data:     data,
                cache:    true,
                method:   'GET',
                success:  function (response) {
                    let result = $.parseJSON(response);
                    let cacheKey = 'applyingMode:' + url;
                    cache.setData(cacheKey, result);

                    this.update(result);
                }.bind(this),
                error:    function () {
                    window.location = url;
                }.bind(this),
                complete: function () {
                    this.hideLoader();
                }.bind(this)
            });
        },

        toggleLabel: function (number) {
            if (number === 1) {
                $(this.labelNSelector, this.$el()).hide();
                $(this.label1Selector, this.$el()).show();
            } else {
                $(this.label1Selector, this.$el()).hide();
                $(this.labelNSelector, this.$el()).show();
            }
        }
    }
});
