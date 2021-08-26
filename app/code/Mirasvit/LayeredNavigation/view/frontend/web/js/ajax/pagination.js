define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/action/apply-filter'
], function ($, applyFilter) {
    'use strict';

    /**
     * Init AJAX paging.
     */
    return function () {
        //change page number

        $(".toolbar .pages a").on('click', function (e) {
            var url = $(this).prop('href');

            applyFilter.applyForced(url);

            e.stopPropagation();
            e.preventDefault();

            setTimeout(function () {
                $(window).scrollTop($('.toolbar').position().top);
            }, 300);
        });
    };
});
