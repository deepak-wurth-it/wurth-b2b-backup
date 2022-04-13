import { IndexResult, Result } from "../types"
import _ from "underscore"
import ko from "knockout"

interface Props {
    result: KnockoutObservable<Result>
    activeIndex: KnockoutObservable<string>
}

export class ItemListView {
    props: Props

    items: KnockoutObservableArray<object>

    constructor(props: Props) {
        this.props = props
        this.items = ko.observableArray([])

        this.setItems(props.result().indexes, props.activeIndex())

        props.result.subscribe(result => this.setItems(result.indexes, props.activeIndex()))
        props.activeIndex.subscribe(index => this.setItems(props.result().indexes, index))
    }

    setItems = (indexes: IndexResult[], indexIdentifier: string) => {
        let updated = false

        _.each(indexes, idx => {
            if (idx.identifier === indexIdentifier) {
                this.items(idx.items)
                updated = true
            }
        })

        !updated && this.items([])
    }
}
