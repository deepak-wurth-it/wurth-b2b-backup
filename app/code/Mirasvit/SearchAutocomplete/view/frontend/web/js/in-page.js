/*eslint-disable */
define(["jquery", "Mirasvit_SearchAutocomplete/js/in-page/PageView", "knockout", "Mirasvit_Search/js/highlight"], function (_jquery, _PageView, _knockout, _highlight) {
  var Injection = function Injection(input, config) {
    "use strict";

    var _this = this;

    this.initView = function () {
      var selector = "#mst-searchautocomplete-in-page";

      if ((0, _jquery)(selector).length > 0) {
        return;
      }

      var wrapper = (0, _jquery)("#mstInPage__page").html();
      (0, _jquery)("body").append(wrapper);
      var node = (0, _jquery)(selector)[0];

      _knockout.applyBindings(_this.pageView, node);
    };

    this.onFocus = function () {
      _this.initView();

      _this.pageView.visible(true);

      _this.pageView.searchQuery(_this.$input.val() + "");
    };

    this.$input = (0, _jquery)(input);
    this.pageView = new _PageView.PageView(config);
    this.$input.on("click focus", this.onFocus);
    _knockout.bindingHandlers.highlight = {
      init: function init(element, valueAccessor, allBindings, viewModel, bindingContext) {
        (0, _highlight)(element, bindingContext.$parents[2].result().query, "mstInPage__highlight");
      }
    };
  };

  return Injection;
});
//# sourceMappingURL=in-page.js.map