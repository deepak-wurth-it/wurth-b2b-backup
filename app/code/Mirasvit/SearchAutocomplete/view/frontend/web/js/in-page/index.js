/*eslint-disable */
define(["./IndexListView", "./PageView", "./SearchBarView"], function (_IndexListView, _PageView, _SearchBarView) {
  Object.keys(_IndexListView).forEach(function (key) {
    if (key === "default" || key === "__esModule") return;
    if (key in _exports && _exports[key] === _IndexListView[key]) return;
    Object.defineProperty(_exports, key, {
      enumerable: true,
      get: function get() {
        return _IndexListView[key];
      }
    });
  });
  Object.keys(_PageView).forEach(function (key) {
    if (key === "default" || key === "__esModule") return;
    if (key in _exports && _exports[key] === _PageView[key]) return;
    Object.defineProperty(_exports, key, {
      enumerable: true,
      get: function get() {
        return _PageView[key];
      }
    });
  });
  Object.keys(_SearchBarView).forEach(function (key) {
    if (key === "default" || key === "__esModule") return;
    if (key in _exports && _exports[key] === _SearchBarView[key]) return;
    Object.defineProperty(_exports, key, {
      enumerable: true,
      get: function get() {
        return _SearchBarView[key];
      }
    });
  });
  return {};
});
//# sourceMappingURL=index.js.map