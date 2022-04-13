/*eslint-disable */
define(["underscore", "knockout"], function (_underscore, _knockout) {
  var ItemListView = function ItemListView(props) {
    "use strict";

    var _this = this;

    this.setItems = function (indexes, indexIdentifier) {
      var updated = false;

      _underscore.each(indexes, function (idx) {
        if (idx.identifier === indexIdentifier) {
          _this.items(idx.items);

          updated = true;
        }
      });

      !updated && _this.items([]);
    };

    this.props = props;
    this.items = _knockout.observableArray([]);
    this.setItems(props.result().indexes, props.activeIndex());
    props.result.subscribe(function (result) {
      return _this.setItems(result.indexes, props.activeIndex());
    });
    props.activeIndex.subscribe(function (index) {
      return _this.setItems(props.result().indexes, index);
    });
  };

  return {
    ItemListView: ItemListView
  };
});
//# sourceMappingURL=ItemListView.js.map