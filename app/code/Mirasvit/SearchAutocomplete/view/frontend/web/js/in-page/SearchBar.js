/*eslint-disable */
define(["jquery"], function (_jquery) {
  var SearchBar = function SearchBar(props) {
    "use strict";

    this.props = props;
    props.visible.subscribe(function (visible) {
      visible && setTimeout(function () {
        return (0, _jquery)("[type=search]")[0].focus();
      }, 10);
    });
    (0, _jquery)(document).on("keyup", function (e) {
      if (e.key === "Escape") {
        if (props.query()) {
          props.query("");
        }
      }
    });
  };

  return {
    SearchBar: SearchBar
  };
});
//# sourceMappingURL=SearchBar.js.map