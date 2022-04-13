import $ from "jquery"

interface Props {
    query: KnockoutObservable<string>
    visible: KnockoutObservable<boolean>
}

export class SearchBarView {
    props: Props

    constructor(props: Props) {
        this.props = props

        props.visible.subscribe(visible => {
            visible && setTimeout(() => $("[type=search]")[0].focus(), 10)
        })

        $(document).on("keyup", e => {
            if (e.key === "Escape") {
                if (props.query()) {
                    props.query("")
                }
            }
        })
    }
}
