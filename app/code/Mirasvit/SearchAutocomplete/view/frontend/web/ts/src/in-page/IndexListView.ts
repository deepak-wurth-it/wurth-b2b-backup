import { IndexResult, Result } from "../types"

interface Props {
    result: KnockoutObservable<Result>
    activeIndex: KnockoutObservable<string>
}

export class IndexListView {
    props: Props

    constructor(props: Props) {
        this.props = props
    }

    indexes = () => {
        return this.props.result().indexes
    }

    isActive = (index: IndexResult) => {
        return index.identifier == this.props.activeIndex()
    }

    selectIndex = (index: IndexResult) => {
        this.props.activeIndex(index.identifier)
    }
}
