import $ from "jquery"
import { Config } from "./types"
import { PageView } from "./in-page/PageView"
import ko from "knockout"
import highlight from "Mirasvit_Search/js/highlight"

export default class Injection {
    $input: JQuery<HTMLInputElement>
    pageView: PageView

    constructor(input: HTMLInputElement, config: Config) {
        this.$input = $(input)
        this.pageView = new PageView(config)

        this.$input.on("click focus", this.onFocus)

        ko.bindingHandlers.highlight = {
            init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                highlight(element, bindingContext.$parents[2].result().query, "mstInPage__highlight")
            },
        }
    }

    initView = () => {
        const selector = "#mst-searchautocomplete-in-page"

        if ($(selector).length > 0) {
            return
        }

        const wrapper = $("#mstInPage__page").html()
        $("body").append(wrapper)

        const node = $(selector)[0]

        ko.applyBindings(this.pageView, node)
    }

    onFocus = () => {
        this.initView()

        this.pageView.visible(true)
        this.pageView.searchQuery(this.$input.val() + "")
    }
}
