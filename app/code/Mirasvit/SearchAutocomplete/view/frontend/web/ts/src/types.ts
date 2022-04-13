export interface Config {
    query: string
    priceFormat: string
    minSearchLength: number
    url: string
    storeId: number
    delay: number
    layout: string
    popularTitle: string
    popularSearches: string[]
    isTypeaheadEnabled: boolean
    typeaheadUrl: string
    minSuggestLength: number
}

export interface Result {
    indexes: IndexResult[]
    noResults: boolean
    query: string
    textAll: string
    textEmpty: string
    totalItems: number
    urlAll: string
}

export interface IndexResult {
    identifier: string
    isShowTotals: boolean
    items: object[]
    buckets: Bucket[]
    position: number
    title: string
    totalItems: number
}

export interface Bucket {
    code: string
    label: string
    buckets: BucketItem[]
}

export interface BucketItem {
    key: string
    label: string
    count: number
}
