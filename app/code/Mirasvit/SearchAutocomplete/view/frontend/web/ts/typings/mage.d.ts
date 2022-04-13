interface JQueryStatic {
    mage: {
        quickSearch: JQueryUI.Widget
    };
}

interface Window {
    priceFormat: undefined | string
}

interface JQuery {
    catalogAddToCart: (params: object) => void
}

declare module "Mirasvit_Search/js/highlight" {
    export default function (element, query, css)
}
