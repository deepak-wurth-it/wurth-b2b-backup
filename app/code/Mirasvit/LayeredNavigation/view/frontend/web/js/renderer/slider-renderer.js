define([
    'jquery',
    'Mirasvit_LayeredNavigation/js/action/apply-filter',
    'jquery-ui-modules/slider',
    'domReady!'
], function ($, applyFilter) {
    'use strict';

    $.widget('mst.navSliderRenderer', {
        options: {
            paramTemplate: '',
            urlTemplate:   '',
            min:           0,
            max:           0,
            from:          0,
            to:            0,
            valueTemplate: '',
            separator:     ':'
        },

        from: null,
        to:   null,

        $text:   null,
        $slider: null,
        $from:   null,
        $to:     null,
        $submit: null,

        _create: function () {
            this.$text = $('[data-element = text]', this.element);
            this.$slider = $('[data-element = slider]', this.element);
            this.$from = $('[data-element = from]', this.element);
            this.$to = $('[data-element = to]', this.element);
            this.$submit = $('[data-element = submit]', this.element);

            this.from = this.options.from || this.options.min;
            this.to = this.options.to || this.options.max;

            if (this.options.min !== this.options.max) {
                this.$slider.slider({
                    range:  true,
                    min:    this.options.min,
                    max:    this.options.max,
                    values: [this.from, this.to],
                    slide:  this.onSlide.bind(this),
                    change: this.onSliderChange.bind(this),
                    step:   1
                });
            } else {
                this.$slider.remove();
                this.$slider = null;
            }

            this.$from.on('change keyup', this.onFromToChange.bind(this));
            this.$to.on('change keyup', this.onFromToChange.bind(this));

            this.$submit.on('click', this.onSubmit.bind(this));

            if (this.from || this.to) {
                this.updateFromTo();
            }
        },

        onSlide: function (event, ui) {
            this.from = ui.values[0];
            this.to = ui.values[1];

            this.updateFromTo();
        },

        onSliderChange: function (event, ui) {
            this.from = ui.values[0];
            this.to = ui.values[1];

            if (event.eventPhase) { // it's user event
                this.applyFilter();
            }
        },

        onFromToChange: function () {
            this.from = parseFloat(this.$from.val());
            this.to = parseFloat(this.$to.val());

            this.updateFromTo();
        },

        onSubmit: function (e) {
            e.preventDefault();
            this.applyFilter();
        },

        applyFilter: function () {
            const value = this.toFixed(this.from, 2) + this.options.separator + this.toFixed(this.to, 2);

            let url = this.options.urlTemplate.replace(this.options.paramTemplate, value);

            applyFilter.apply(url, $(this.element));
        },

        updateFromTo: function () {
            this.$text.html(this.getTextValue(this.from) + ' - ' + this.getTextValue(this.to));

            this.$from.val(this.from);
            this.$to.val(this.to);

            if (this.$slider) {
                const to = this.to > this.options.max ? this.options.max : this.to;
                const from = this.from > to ? this.options.min : this.from;

                this.$slider.slider('values', [from, to]);
            }
        },

        getTextValue: function (value) {
            let tmpl = this.options.valueTemplate;

            tmpl = tmpl.replace('{value}', this.toFixed(value, 0));
            tmpl = tmpl.replace('{value.0}', this.toFixed(value, 0));
            tmpl = tmpl.replace('{value.1}', this.toFixed(value, 1));
            tmpl = tmpl.replace('{value.2}', this.toFixed(value, 2));

            return tmpl;
        },

        toFixed: function (value, precision) {
            //1.00 === 1
            if (parseFloat(parseFloat(value).toFixed(0)) === parseFloat(parseFloat(value).toFixed(precision))) {
                return parseFloat(value).toFixed(0);
            }
            return parseFloat(value).toFixed(precision);
        }
    });

    return $.mst.navSliderRenderer;
});
