define([
    'jquery',
    'Mirasvit_Core/js/lib/prettycron',
    'Mirasvit_Core/js/lib/later'
], function ($, cron) {
    'use strict';

    return function (el) {
        var $input = $(el);

        if ($input.attr('data-cron')) {
            return;
        }
        $input.attr('data-cron', true);

        var $container = $('<div />');

        $container.insertAfter($input);

        $input.on('change keyup', function () {
            render();
        });

        $input.on('change', function () {
            render();
        });

        render();

        function render() {
            $container.html('');

            var val = $input.val();

            var readable = cron.toString(val);

            var $p = $('<p />')
            .css('font-size', '12px')
            .css('background', isValid($input) ? '#b2deb2' : '#efa5a5')
            .css('border-top', '0')
            .css('border-radius', '0 0 1px 1px')
            .css('padding', '5px')
            .css('margin-top', '-1px')
            .html(readable);
            $container.append($p);

            $input.css('border-color', isValid($input) ? '#7db97d' : '#e22626');

            later.schedule(later.parse.cron(val)).next(3).forEach(function (next) {
                var $p = $('<p />').css('font-size', '11px').html(next.toGMTString());
                $container.append($p);
            })
        }

        function isValid($input) {
            var val = $input.val();

            var parts = val.split(' ');

            parts = parts.filter(function (item) {
                if (item.trim()) {
                    return item;
                }
            });

            return parts.length == 5;
        }
    };
});