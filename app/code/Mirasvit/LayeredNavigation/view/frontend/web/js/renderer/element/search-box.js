define([
    "jquery",
    "domReady!"
], function ($) {
    'use strict';

    $.widget('mst.navSearchBox', {
        options: {},

        _create: function () {
            const $element = $(this.element);
            const $searchBox = $('[data-element = search]', this.element);

            $searchBox.on('change keyup', function () {
                const q = $searchBox.val().toLowerCase();

                const $items = $('[data-element = filter]', this.element);

                if (q === "") {
                    $element.trigger('search', false);

                    $items.css('display', '');
                } else {
                    $element.trigger('search', true); //communication with size-limiter

                    $items.each(function (i, item) {
                        const $item = $(item);
                        const $label = $('label', $item);
                        $label.find('*').remove(); //remove all html (count)

                        const text = $label.text().toLowerCase();

                        if (text.indexOf(q) !== -1) {
                            $item.show();
                        } else {
                            $item.hide();
                        }
                    })
                }
            }.bind(this))
        }
    });

    return $.mst.navSearchBox;
});
