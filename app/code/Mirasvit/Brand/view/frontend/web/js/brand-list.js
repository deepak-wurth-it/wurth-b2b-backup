define(['jquery'], function ($) {
    'use strict';

    $.widget('mst.brandList', {
        _create: function () {
            const ROW_SELECTOR = '.js-brand-row';
            const LETTER_SELECTOR = '.js-brand-letter';


            const el = this.element;

            el.on('click', function (e) {
                e.preventDefault();

                const $target = $(e.currentTarget);

                $(LETTER_SELECTOR).removeClass('_active');
                $target.addClass('_active');

                const letter = $(e.currentTarget).data('letter');
                const rows = $(ROW_SELECTOR);

                rows.each(function (_, item) {
                    const $item = $(item);
                    const rowLetter = $item.data('letter');

                    if (letter && rowLetter !== letter) {
                        $item.hide();
                    } else {
                        $item.show();
                    }
                });
            });
        }
    });

    return $.mst.brandList;
});
