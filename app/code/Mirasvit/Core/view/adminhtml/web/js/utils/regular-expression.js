define([
    'jquery',
    'Mirasvit_Core/js/lib/regex-colorizer'
], function ($) {
    'use strict';

    return function (el) {
        var $input = $(el);

        if ($input.attr('data-regexp')) {
            return;
        }
        $input.attr('data-regexp', true);
        $input.attr('autocomplete', 'off');

        var cls = 'regexp' + Math.floor((Math.random() * 1000000));

        var $container = $('<div />').addClass(cls).addClass('regex');

        $container.insertAfter($input);

        $input.on('change keyup focus blur', function () {
            render();
        });

        $input.on('change', function (e) {
            render();
        });

        RegexColorizer.addStyleSheet();
        render();

        function render() {
            $container.html($input.val());

            if (!$input.val()) {
                $container.hide();
                return;
            }

            RegexColorizer.colorizeAll(cls);

            if ($input.is(":focus")) {
                $container.show();
            } else {
                $container.hide();
            }

            $container.css('font-size', '12px')
                      .css('border-top', '0')
                      .css('border-radius', '0 0 1px 1px')
                      .css('padding', '5px')
                      .css('margin-top', '-1px');
            // .css('color', '#fff');

            if ($('.err', $container).length > 0) {
                $container.css('background', '#efa5a5');
                $input.css('border-color', '#e22626');
            } else {
                $container.css('background', '#b2deb2');
                $input.css('border-color', '#7db97d');
            }
        }
    };
});