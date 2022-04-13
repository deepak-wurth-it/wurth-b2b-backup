import { Config, Result } from "../types"
import _ from "underscore"
import ko from "knockout"
import $ from "jquery"
import { IndexListView } from "./IndexListView"
import { SearchBarView } from "./SearchBarView"
import { ItemListView } from "./ItemListView"
import { SidebarView } from "./SidebarView"

const EMPTY_RESULT = {
    indexes:    [],
    noResults:  false,
    query:      "",
    textAll:    "",
    textEmpty:  "",
    totalItems: 0,
    urlAll:     "",
}

export class PageView {
    visible: KnockoutObservable<boolean>
    loading: KnockoutObservable<boolean>
    sendRequest: () => void
    xhr: JQueryXHR | null
    config: Config
    result: KnockoutObservable<Result>
    time: KnockoutObservable<number>

    activeIndex: KnockoutObservable<string>
    searchQuery: KnockoutObservable<string>
    filterList: KnockoutObservable<Map<string, string>>

    searchBarView: SearchBarView
    indexListView: IndexListView
    itemListView: ItemListView
    sidebarView: SidebarView

    constructor(config: Config) {
        this.config = config
        this.visible = ko.observable(false)
        this.loading = ko.observable(false)
        this.searchQuery = ko.observable("")
        this.activeIndex = ko.observable("magento_catalog_product")
        this.filterList = ko.observable(new Map<string, string>())
        this.sendRequest = _.debounce(this.request, 10)
        this.xhr = null
        this.result = ko.observable(EMPTY_RESULT)
        this.time = ko.observable(0)

        this.searchBarView = new SearchBarView({
            query:   this.searchQuery,
            visible: this.visible,
        })

        this.indexListView = new IndexListView({
            result:      this.result,
            activeIndex: this.activeIndex,
        })

        this.itemListView = new ItemListView({
            result:      this.result,
            activeIndex: this.activeIndex,
        })

        this.sidebarView = new SidebarView({
            result:      this.result,
            activeIndex: this.activeIndex,
            filterList:  this.filterList,
        })

        this.visible.subscribe(visible => {
            $("html").toggleClass("mstInPage", visible)
        })

        $(document).on("keyup", e => {
            if (e.key === "Escape") {
                this.searchQuery() == "" && this.visible(false)
            }
        })

        this.searchQuery.subscribe(this.sendRequest)
        this.filterList.subscribe(this.sendRequest)
    }

    isEmpty = () => {
        return this.searchQuery() === "" || this.time() === 0
    }

    hide = () => {
        this.visible(false)
    }

    request = () => {
        this.xhr?.abort()

        if (this.searchQuery() === "") {
            this.result(EMPTY_RESULT)
            this.time(0)

            return
        }

        const ts = new Date().getTime()

        this.loading(true)

        const filters: any = {}
        this.filterList().forEach((value, key) => filters[key] = value)

        this.xhr = $.ajax({
            url:      this.config.url,
            dataType: "json",
            type:     "GET",
            data:     {
                q:        this.searchQuery(),
                store_id: this.config.storeId,
                cat:      false,
                limit:    30,
                page:     1,
                buckets:  [ "category_ids" ],
                filters:  filters,
            },
            success:  (data: Result) => {
                this.loading(false)
                this.result(data)
                this.time((new Date().getTime() - ts) / 1000)
            },
        })
    }
}
