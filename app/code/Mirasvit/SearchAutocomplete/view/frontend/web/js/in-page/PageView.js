/*eslint-disable */
define(["underscore", "knockout", "jquery", "Mirasvit_SearchAutocomplete/js/in-page/IndexListView", "Mirasvit_SearchAutocomplete/js/in-page/SearchBarView", "Mirasvit_SearchAutocomplete/js/in-page/ItemListView", "Mirasvit_SearchAutocomplete/js/in-page/SidebarView"], function (_underscore, _knockout, _jquery, _IndexListView, _SearchBarView, _ItemListView, _SidebarView) {
  var EMPTY_RESULT = {
    indexes: [],
    noResults: false,
    query: "",
    textAll: "",
    textEmpty: "",
    totalItems: 0,
    urlAll: ""
  };

  var PageView = function PageView(config) {
    "use strict";

    var _this = this;

    this.isEmpty = function () {
      return _this.searchQuery() === "" || _this.time() === 0;
    };

    this.hide = function () {
      _this.visible(false);
    };

    this.request = function () {
      var _this$xhr;

      (_this$xhr = _this.xhr) == null ? void 0 : _this$xhr.abort();

      if (_this.searchQuery() === "") {
        _this.result(EMPTY_RESULT);

        _this.time(0);

        return;
      }

      var ts = new Date().getTime();

      _this.loading(true);

      var filters = {};

      _this.filterList().forEach(function (value, key) {
        return filters[key] = value;
      });

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
          filters: filters
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
    this.filterList = _knockout.observable(new Map());
    this.sendRequest = _underscore.debounce(this.request, 10);
    this.xhr = null;
    this.result = _knockout.observable(EMPTY_RESULT);
    this.time = _knockout.observable(0);
    this.searchBarView = new _SearchBarView.SearchBarView({
      query: this.searchQuery,
      visible: this.visible
    });
    this.indexListView = new _IndexListView.IndexListView({
      result: this.result,
      activeIndex: this.activeIndex
    });
    this.itemListView = new _ItemListView.ItemListView({
      result: this.result,
      activeIndex: this.activeIndex
    });
    this.sidebarView = new _SidebarView.SidebarView({
      result: this.result,
      activeIndex: this.activeIndex,
      filterList: this.filterList
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
    this.filterList.subscribe(this.sendRequest);
  };

  return {
    PageView: PageView
  };
});
//# sourceMappingURL=PageView.js.map