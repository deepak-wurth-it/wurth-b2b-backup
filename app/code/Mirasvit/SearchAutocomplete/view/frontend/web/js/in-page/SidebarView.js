/*eslint-disable */

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

define(["underscore", "knockout"], function (_underscore, _knockout) {
  var SidebarView = function SidebarView(props) {
    "use strict";

    var _this = this;

    this.setBuckets = function (indexes, indexIdentifier) {
      var buckets = [];

      _underscore.each(indexes, function (idx) {
        if (idx.identifier != indexIdentifier) {
          return;
        }

        _underscore.each(idx.buckets, function (bucket) {
          var bucketItems = _underscore.map(bucket.buckets, function (item) {
            return _extends({}, item, {
              isActive: _this.props.filterList().has(bucket.code) && _this.props.filterList().get(bucket.code) === item.key,
              select: function select() {
                return _this.selectItem(bucket, item);
              }
            });
          });

          buckets.push(_extends({}, bucket, {
            buckets: bucketItems
          }));
        });
      });

      _this.buckets(buckets);
    };

    this.selectItem = function (bucket, item) {
      var map = _this.props.filterList();

      if (map.has(bucket.code) && map.get(bucket.code) === item.key) {
        map.delete(bucket.code);
      } else {
        map.set(bucket.code, item.key);
      }

      _this.props.filterList(map);
    };

    this.props = props;
    this.buckets = _knockout.observableArray([]);
    this.setBuckets(props.result().indexes, props.activeIndex());
    props.result.subscribe(function (result) {
      return _this.setBuckets(result.indexes, props.activeIndex());
    });
    props.activeIndex.subscribe(function (index) {
      return _this.setBuckets(props.result().indexes, index);
    });
    props.filterList.subscribe(function () {
      return _this.setBuckets(props.result().indexes, props.activeIndex());
    });
  };

  return {
    SidebarView: SidebarView
  };
});
//# sourceMappingURL=SidebarView.js.map