/*eslint-disable */
define(["underscore", "knockout", "jquery", "Mirasvit_SearchAutocomplete/js/in-page/SearchBar"], function (_underscore, _knockout, _jquery, _SearchBar) {
  var Page = function Page(config) {
    "use strict";

    var _this = this;

    this.selectIndex = function (index) {
      _this.activeIndex(index.identifier);
    };

    this.request = function () {
      var _this$xhr;

      (_this$xhr = _this.xhr) == null ? void 0 : _this$xhr.abort();

      if (_this.searchQuery() === "") {
        _this.result(null);

        return;
      }

      var ts = new Date().getTime();

      _this.loading(true);

      _this.xhr = _jquery.ajax({
        url: _this.config.url,
        dataType: "json",
        type: "GET",
        data: {
          q: _this.searchQuery(),
          store_id: _this.config.storeId,
          cat: false,
          limit: 30,
          page: 1,
          buckets: ["category_ids"],
          filters: {
            "category_ids": 21
          }
        },
        success: function success(data) {
          _this.loading(false);

          _this.result(data);

          _this.time((new Date().getTime() - ts) / 1000);
        }
      });
    };

    this.config = config;
    this.visible = _knockout.observable(false);
    this.loading = _knockout.observable(false);
    this.searchQuery = _knockout.observable("");
    this.activeIndex = _knockout.observable("magento_catalog_product");
    this.sendRequest = _underscore.debounce(this.request, config.delay);
    this.xhr = null;
    this.result = _knockout.observable(null);
    this.time = _knockout.observable(null);
    this.searchBarView = new _SearchBar.SearchBar({
      query: this.searchQuery,
      visible: this.visible
    });
    this.visible.subscribe(function (visible) {
      (0, _jquery)("html").toggleClass("mstInPage", visible);
    });
    (0, _jquery)(document).on("keyup", function (e) {
      if (e.key === "Escape") {
        _this.searchQuery() == "" && _this.visible(false);
      }
    });
    this.searchQuery.subscribe(this.sendRequest);
  };

  return {
    Page: Page
  };
});
//# sourceMappingURL=Page.js.map