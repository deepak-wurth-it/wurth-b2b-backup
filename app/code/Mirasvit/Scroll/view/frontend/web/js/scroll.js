define([
    'jquery',
    './loader'
], function ($, loader) {
    "use strict";

    $.widget('mst.ajaxScroll', {
        nextBtn:       null,
        prevBtn:       null,
        isActive:      false,
        excludeHeight: null,
        dataPageAttr:  'scroll-page',

        modes: {
            infinite: '_initInfiniteMode',
            button:   '_initButtonMode'
        },

        options: {
            mode:                       'button',
            moreBtnClass:               'mst-scroll__button', // "load more" buttons class
            postCatalogHeightSelectors: [
                '.main .products ~ .block-static-block',
                '.page-footer',
                '.page-bottom'
            ],
            // elements that should be hidden
            hide:                       [
                '.pages',
                '.toolbar-amount'
            ],
            // initial info
            factor:                     0.9, // factor for loading next page when scrolling down in infinite mode
            pageParam:                  'p',
            pageNum:                    1,
            initPageNum:                1,
            prevPageNum:                null,
            nextPageNum:                null,
            lastPageNum:                null,
            loadPrevText:               'Load Previous Page',
            loadNextText:               'Load More'
        },

        _create: function () {
            // scroll is active only when catalog has more than 1 page
            this.isActive = this.options.nextPageNum || this.options.prevPageNum || false;

            // set initial page number to product list
            this.element.data(this.dataPageAttr, this.options.pageNum);

            // hide default DOM elements such as pagination
            this._hideElements();

            // init scroll widget in chosen mode
            this[this.modes[this.options.mode]]();

            this._bind();
        },

        _destroy: function () {
            this.isActive = false;
            this.options = {};
        },

        /**
         * Bind scroll event and load products when window is scrolled down.
         */
        _initInfiniteMode: function () {
            var onPause = false;

            $(window).scroll(function () {
                var scrollTop = $(window).scrollTop();

                if (scrollTop >= this._calculateHeightDiff() && !onPause && this.options.nextPageNum) {
                    onPause = true; // suspend ajax scrolling

                    loader.show(this._getProductListSelector() + ':last');

                    this._request(window.location.href, {p: this.options.nextPageNum})
                        .done(loader.hide.bind(loader))
                        .done(this._updateCatalog.bind(this))
                        .done(function (response) { // update next page num
                            if (response.config) {
                                this.options.nextPageNum = response.config.nextPageNum;
                                onPause = false; // resume ajax scrolling
                            }
                        }.bind(this));
                }
            }.bind(this));

            // init button for previous page
            if (this.options.prevPageNum) {
                this.prevBtn = this._createButton(this.options.loadPrevText, this.options.prevPageNum, 'insertBefore');
            }
        },

        /**
         * Calculate difference between the whole document height and its visible part + height of excluded blocks.
         *
         * @return {Number}
         */
        _calculateHeightDiff: function () {
            var diff = $(document).height() - $(window).height();

            diff -= this._getExcludeHeight();
            diff = this.options.factor * diff;

            return diff;
        },

        /**
         * Initialize widget in button mode.
         */
        _initButtonMode: function () {
            this._initButtons();
        },

        /**
         * Create buttons.
         */
        _initButtons: function () {
            if (this.options.nextPageNum) {
                this.nextBtn = this._createButton(this.options.loadNextText, this.options.nextPageNum, 'insertAfter');
            }

            if (this.options.prevPageNum) {
                this.prevBtn = this._createButton(this.options.loadPrevText, this.options.prevPageNum, 'insertBefore');
            }
        },

        /**
         * Create html button and attach it to widget's element.
         *
         * @param {String} label
         * @param {Number} pageNum - number of page used for button
         * @param {String} method - method used to insert the button over widget's element
         *
         * @return {jQuery}
         */
        _createButton: function (label, pageNum, method) {
            return $('<button class="action primary"></button>')
                .text(label)
                .data('page', pageNum)
                .addClass(this.options.moreBtnClass)
                [method](this.element);
        },

        /**
         * Hide DOM elements listed in this.options.hide array.
         */
        _hideElements: function () {
            this.options.hide.map(function (selector) {
                // hide only if "load" buttons exist
                if (this.isActive) {
                    // mark all hidden elements with our identification
                    $(selector + ':visible').data('scroll', 'hidden').hide();
                } else {
                    // show only elements hidden by us
                    $(selector).find('[data-scroll="hidden"]').show();
                }
            }.bind(this));
        },

        _bind: function () {
            var self = this;

            // observe "load more" buttons clicks
            $('.' + this.options.moreBtnClass).on('click', function () {
                var page      = $(this).data('page'),
                    targetBtn = page > self.options.initPageNum ? self.nextBtn : self.prevBtn;

                targetBtn.addClass('_loading');

                self._request(window.location.href, {p: page})
                    .done(function () {
                        targetBtn.removeClass('_loading');
                    })
                    .done(self._updatePaging.bind(self))
                    .done(self._updateCatalog.bind(self));
            });

            // update URL when scrolling through pages
            $(window).scroll(function () {
                if (this.isActive) {
                    this._updateHistory({config: {pageNum: this._determineCurrentPage()}});
                }
            }.bind(this));
        },

        /**
         * Determine current page by scrollTop position.
         *
         * @return {Number} - current page number
         */
        _determineCurrentPage: function () {
            var page        = null,
                self        = this,
                biggestPart = 0;

            $(this._getProductListSelector()).each(function () {
                var $list       = $(this),
                    $window     = $(window),
                    visiblePart = 0; // visible part of a product list block in window

                if ($list.offset().top - $window.scrollTop() < 0) { // list block is above window
                    visiblePart = $list.offset().top + $list.height() - $window.scrollTop();
                } else {
                    visiblePart = $window.height() - ($list.offset().top - $window.scrollTop());
                }

                if (visiblePart < 0) {
                    return; // skip current product list and continue loop
                }

                // if whole product list completely fit on the window
                if (visiblePart >= $list.height()
                    // or the product list takes up most of the window size
                    || $list.height() > $window.height() && $window.height() / visiblePart < 2
                ) {
                    page = $list.data(self.dataPageAttr) || 1;
                    return false; // we found page, stop looping
                }

                // otherwise use the page that takes up the most part of a space in the window
                if (visiblePart > biggestPart) {
                    biggestPart = visiblePart;
                    page = $list.data(self.dataPageAttr) || 1;
                }
            });

            return page;
        },

        /**
         * Update catalog products.
         *
         * @param {Object} response
         */
        _updateCatalog: function (response) {
            var selector      = this._getProductListSelector(),
                productList   = null,
                targetWrapper = null,
                doc = document.documentElement,
                top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);

            if (response.products && response.config) {
                // wrap product list into div to be able to retrieve needed selector
                productList = $('<div/>').html(response.products).find(selector);

                var $wrapper = $('<div/>').html(response.products);
                $wrapper.find(selector).remove();
                //left js outside productList (required for init the ajax cart)
                var scripts = $wrapper.find('script');

                productList.data(this.dataPageAttr, response.config.pageNum);
                // insert products after last of first list accordingly
                if (response.config.pageNum > this.options.initPageNum) {
                    targetWrapper = $(selector).last();
                    productList.insertAfter(targetWrapper);
                    window.scroll(0, top);
                } else {
                    targetWrapper = $(selector).first();
                    productList.insertBefore(targetWrapper);
                }

                scripts.insertAfter(targetWrapper);
                window.scroll(0, top);

                // trigger 3rd party events
                targetWrapper.trigger('contentUpdated');
                setTimeout(function () {
                    // execute after swatches are loaded
                    $(document).trigger('amscroll_refresh');
                }, 500);
                if ($.fn.lazyload) {
                    // lazyload images for new content (Smartwave_Porto theme)
                    $('.porto-lazyload').lazyload({
                        effect: 'fadeIn'
                    });
                }

                if ($('.lazyload').length) {
                    $("img.lazyload").unveil(0,function(){$(this).load(function(){this.classList.remove("lazyload");});});
                }

                $(document).trigger('contentUpdated');
            }
        },

        /**
         * @return {String}
         */
        _getProductListSelector: function () {
            return '.' + this.element.attr('class').split(' ').filter(Boolean).join('.');
        },

        /**
         * Update paging buttons.
         *
         * @param {Object} response
         */
        _updatePaging: function (response) {
            // hide next/prev buttons
            if (response.config) {
                // if next page was loaded - change next page number, otherwise change prev page number
                if (response.config.pageNum > this.options.initPageNum) {
                    this.nextBtn.data('page', response.config.nextPageNum);
                } else {
                    this.prevBtn.data('page', response.config.prevPageNum);
                }

                // hide next/prev page buttons if first or last pages loaded
                if (response.config.pageNum === 1) {
                    this.prevBtn.hide();
                } else if (this.prevBtn) {
                    this.prevBtn.show();
                }

                if (response.config.pageNum === response.config.lastPageNum) {
                    this.nextBtn.hide();
                } else if (this.nextBtn) {
                    this.nextBtn.show();
                }
            }
        },

        /**
         * Update page number param in URL.
         *
         * @param {Object} response
         */
        _updateHistory: function (response) {
            var url            = null,
                currentPageNum = null;

            if (response.config) {
                url = this._getUrl(response.config.pageNum);
                currentPageNum = this._getUrl().searchParams.get(this.options.pageParam);

                // ignore page #1
                if (response.config.pageNum === 1 && currentPageNum === null) {
                    return;
                }

                if (parseInt(currentPageNum) !== parseInt(response.config.pageNum)) {
                    history.replaceState({}, document.title, url.href);
                }
            }
        },

        /**
         * Send XHR.
         *
         * @param {String} url
         * @param {Object} data
         *
         * @return {Object}
         */
        _request: function (url, data) {
            data.is_scroll = 1;

            return $.ajax({
                url:   url,
                data:  data,
                cache: true
                //showLoader: true
            });
        },

        _getExcludeHeight: function () {
            var height = 0;

            if (this.excludeHeight === null) {
                if (!this.options.postCatalogHeightSelectors) {
                    this.options.postCatalogHeightSelectors = [
                        '.main .products ~ .block-static-block',
                        '.page-footer',
                        '.page-bottom'
                    ];
                }

                this.options.postCatalogHeightSelectors.map(function (selector) {
                    var block = $(selector);

                    if (block.length) {
                        height += block.first().height();
                    }
                });

                this.excludeHeight = height;
            }

            return this.excludeHeight;
        },

        /**
         * Get the URL for fetching additional products.
         *
         * @param {Number|Null} pageNum
         *
         * @return {URL}
         */
        _getUrl: function (pageNum) {
            var url = new URL(window.location);

            if (pageNum) {
                if (parseInt(pageNum) === 1) {
                    url.searchParams.delete(this.options.pageParam);
                } else {
                    url.searchParams.set(this.options.pageParam, pageNum);
                }
            }

            return url;
        }
    });

    return $.mst.ajaxScroll;
});
