import { Bucket, BucketItem, IndexResult, Result } from "../types"
import _ from "underscore"
import ko from "knockout"

interface Props {
    result: KnockoutObservable<Result>
    activeIndex: KnockoutObservable<string>
    filterList: KnockoutObservable<Map<string, string>>
}

interface SelectableBucketItem extends BucketItem {
    select: () => void
    isActive: boolean
}

interface SelectableBucket {
    code: string
    label: string
    buckets: SelectableBucketItem[]
}

export class SidebarView {
    props: Props

    buckets: KnockoutObservableArray<SelectableBucket>

    constructor(props: Props) {
        this.props = props
        this.buckets = ko.observableArray([])

        this.setBuckets(props.result().indexes, props.activeIndex())

        props.result.subscribe(result => this.setBuckets(result.indexes, props.activeIndex()))
        props.activeIndex.subscribe(index => this.setBuckets(props.result().indexes, index))
        props.filterList.subscribe(() => this.setBuckets(props.result().indexes, props.activeIndex()))
    }

    setBuckets = (indexes: IndexResult[], indexIdentifier: string) => {
        let buckets: SelectableBucket[] = []

        _.each(indexes, idx => {
            if (idx.identifier != indexIdentifier) {
                return
            }

            _.each(idx.buckets, bucket => {
                const bucketItems = _.map(bucket.buckets, item => {
                    return {
                        ...item,
                        isActive: this.props.filterList().has(bucket.code) && this.props.filterList().get(bucket.code) === item.key,
                        select:   () => this.selectItem(bucket, item),
                    }
                })

                buckets.push({
                    ...bucket,
                    buckets: bucketItems,
                })
            })
        })

        this.buckets(buckets)
    }

    selectItem = (bucket: Bucket, item: BucketItem) => {
        const map = this.props.filterList()

        if (map.has(bucket.code) && map.get(bucket.code) === item.key) {
            map.delete(bucket.code)
        } else {
            map.set(bucket.code, item.key)
        }

        this.props.filterList(map)
    }
}
