define([
    "jquery",
    'mage/translate'
], function ($) {
    'use strict';

    //Work with checkbox
    $.widget('mst.mNavigationFilterLink', {
        _create: function () {
            this._bind();
        },

        _bind: function () {
            var el = this.element;
            if ($(el).parent('li').next('ol').length) {
                $(el).parent('li').prepend('<div class="arrowHolder"><span class="arrowDown"></span></div>');
                if ($(el).hasClass('filterable')) {
                    if (el.hasClass('expanded')) {
                        var html = '<a href="'+$(el).prop('href')+'" class="m-navigation-filter-item-ensure">'+$.mage.__('Dismiss Filter')+'</a>';
                    } else {
                        var html = '<a href="'+$(el).prop('href')+'" class="m-navigation-filter-item-ensure">'+$.mage.__('Filter')+'</a>';
                    }
                    $(el).parent('li').append(html);
                }
            }

            if (el.hasClass('expanded')) {
                $.each(el.parents('.item, .m-navigation-filter-item-nested'), function( index, value ) {
                    if (index > 0) {
                        if ($(value).prop('tagName') == 'OL') {
                            $(value).show();
                            $(value).prev('li').find('span').toggleClass('arrowDown');
                            $(value).prev('li').find('span').toggleClass('arrowLeft');
                        }
                    }
                });
                this.expandFilter(el);
            }

            if (this.options.isAjaxEnabled == 0) {
                this.element.on('click', function (e) {
                    if ((this.element.parent('li').next('ol').length && !this.element.hasClass('filterable')) ||
                        (this.element.parent('li').next('ol').length && this.element.hasClass('filterable'))) {

                        this.expandFilter(this.element);
                        return false;
                    }
                }.bind(this));
            }

            $(el).parent('li').find('.arrowHolder').on('click', function () {
                this.expandFilter(el);
                if (this.options.isAjaxEnabled == 1) {
                        if (this.options.isStylizedCheckbox == 0) {
                        window.mNavigationIsSimpleCheckboxChecked = undefined;
                    }
                } else {
                    var checkbox = el.find('input[type=checkbox]');

                    if (checkbox.prop('checked') && window.mNavigationFilterCheckboxApplied != true) {
                        checkbox.context.checked = false;
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    } else if (!checkbox.prop('checked') && window.mNavigationFilterCheckboxApplied != true) {
                        checkbox.context.checked = true;
                        checkbox.prop('checked', 'checked');
                    }
                }
            }.bind(this));
        },

        expandFilter: function(el) {
            if ($(el).parent('li').next('ol').length) {
                $(el).parent('li').find('span').toggleClass('arrowDown');
                $(el).parent('li').find('span').toggleClass('arrowLeft');
                $(el).parent('li').next('ol.m-navigation-filter-item-nested').toggle();

                if ($(el).hasClass('filterable')) {
                    $(el).next('.m-navigation-filter-item-ensure').toggle('slow');
                    $(el).next('.m-navigation-filter-item-ensure').toggleClass('ensure_show', 800);
                }
                return false;
            }
        }
    });

    return $.mst.mNavigationFilterLink;
});
