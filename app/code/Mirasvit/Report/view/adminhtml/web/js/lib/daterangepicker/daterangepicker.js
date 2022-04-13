define([
    'jquery',
    'Mirasvit_Report/js/lib/moment.min'
], function ($, moment) {

    var $currentTarget;
    var $dropdown;

    // form elements
    var $datepicker;

    var $daterangePreset;

    var $enableComparison;
    var $comparisonPreset;

    var defaultOptions = {
        values: {}
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $this = $(this);
                var data = $this.data('DateRangesWidget');
                $this.data('test', internal);

                if (!data) {
                    var effectiveOptions = $.extend({}, defaultOptions, options);
                    $this.data('DateRangesWidget', {
                        options: effectiveOptions
                    });
                }

                internal.createElements($this);
                internal.updateDateField($this);
            });
        }
    };

    var internal = {

        refreshForm: function () {
            var lastSel = $datepicker.DatePickerGetLastSel();

            if ($('.comparison-preset', $dropdown).val() != 'custom') {
                lastSel = lastSel % 2;
                $datepicker.DatePickerSetLastSel(lastSel);
            }

            $('.dr', $dropdown).removeClass('active');
            $('.dr[lastSel=' + lastSel + ']', $dropdown).addClass('active');

            var dates = $datepicker.DatePickerGetDate()[0];

            var newFrom = moment(dates[0]).format('ll');
            var newTo = moment(dates[1]).format('ll');

            var oldFrom = $('.dr1.from', $dropdown).val();
            var oldTo = $('.dr1.to', $dropdown).val();

            if (newFrom != oldFrom || newTo != oldTo) {
                $('.dr1.from', $dropdown).val(newFrom);
                $('.dr1.to', $dropdown).val(newTo);
            }

            if (dates[2]) {
                $('.dr2.from', $dropdown).val(moment(dates[2]).format('ll'));
            }

            if (dates[3]) {
                $('.dr2.to', $dropdown).val(moment(dates[3]).format('ll'));
            }
        },

        createElements: function ($target) {
            // modify div to act like a dropdown
            $target.html(
                '<div class="date-range-field">' +
                '<span class="main"></span>' +
                //'<span class="comparison-divider"> Cmp to: </span>'+
                '<span class="comparison"></span>' +
                '<a href="#" class="arrow"></a>' +
                '</div>'
            );

            // only one dropdown exists even though multiple widgets may be on the page
            if (!$dropdown) {
                $dropdown = $(
                    '<div id="datepicker-dropdown">' +
                    '<div class="date-ranges-picker"></div>' +
                    '<div class="date-ranges-form">' +
                    '<div class="main-daterange">' +
                    '<div class="ranges">' +
                    '<select class="daterange-preset admin__control-select">' +
                    '</select>' +
                    '</div>' +
                    '<input type="text" class="dr dr1 from admin__control-text" lastSel="0" /> - <input type="text" class="dr dr1 to admin__control-text" lastSel="1" />' +
                    '<input type="hidden" class="dr dr1 from_millis" lastSel="2" /><input type="hidden" class="dr dr1 to_millis" lastSel="3" />' +
                    '</div>' +
                    '<div class="compare-daterange">' +
                    '<div class="admin__field admin__field-option">' +
                    '<input type="checkbox" checked="checked" id="compare" class="enable-comparison admin__control-checkbox" />' +
                    '<label class="admin__field-label" for="compare">Compare to:</label>' +
                    '</div>' +
                    '<select class="comparison-preset admin__control-select">' +
                    '<option value="custom">Custom</option>' +
                    '<option value="previousperiod" selected="selected">Previous period</option>' +
                    '<option value="previousyear">Previous year</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="comparison-daterange" style="display: none">' +
                    '<input type="text" class="dr dr2 from admin__control-text" lastSel="2" /> - <input type="text" class="dr dr2 to admin__control-text" lastSel="3" />' +
                    '<input type="hidden" class="dr dr2 from_millis" lastSel="2" /><input type="hidden" class="dr dr2 to_millis" lastSel="3" />' +
                    '</div>' +
                    '<div class="btn-group">' +
                    '<button class="btn primary" id="button-ok">Apply</button>' +
                    '<button class="btn secondary" id="button-cancel">Cancel</button>' +
                    '</div>' +
                    '</div>' +
                    '</div>');
                //
                // <div class="admin__field admin__field-option">
                //         <input name="comment[is_customer_notified]" type="checkbox" class="admin__control-checkbox" id="history_notify" value="1">
                //         <label class="admin__field-label" for="history_notify">Notify Customer by Email</label>
                //     </div>
                $dropdown.appendTo($('body'));

                $datepicker = $('.date-ranges-picker', $dropdown);

                $daterangePreset = $('.daterange-preset', $dropdown);

                $enableComparison = $('.enable-comparison', $dropdown);
                $comparisonPreset = $('.comparison-preset', $dropdown);

                var options = $target.data('DateRangesWidget').options;

                options = _.extend(options, {
                    onChange: function (dates, el, options) {
                        internal.setDaterangePreset('custom');
                    }
                });
                $datepicker.DatePicker(options);

                internal.options = options;

                /**
                 * Handle change of datePreset
                 */
                $daterangePreset.change(function () {
                    var datePreset = internal.getDaterangePreset();
                    $('.dr1', $dropdown).prop('disabled', ($daterangePreset.val() == 'custom' ? false : true));

                    internal.recalculateDaterange();
                });

                /**
                 * Handle enable/disable comparison.
                 */
                $enableComparison.change(function () {
                    internal.setComparisonEnabled($(this).is(':checked'));
                });

                /**
                 * Handle change of comparison preset.
                 */
                $comparisonPreset.change(function () {
                    internal.recalculateComparison();
                });

                /**
                 * Handle clicking on date field.
                 */
                $('.dr', $dropdown).click(function () {
                    $datepicker.DatePickerSetLastSel($(this).attr('lastSel'));
                });

                /**
                 * Handle clicking on OK button.
                 */
                $('#button-ok', $dropdown).click(function () {
                    internal.retractDropdown($currentTarget);
                    internal.saveValues($currentTarget);
                    internal.updateDateField($currentTarget);
                    return false;
                });

                /**
                 * Handle clicking on OK button.
                 */
                $('#button-cancel', $dropdown).click(function () {
                    var $this = $(this);
                    internal.retractDropdown($currentTarget);
                    return false;
                });
            }

            /**
             * Handle expand/retract of dropdown.
             */
            $target.bind('click', function () {
                var $this = $(this);
                if ($this.hasClass('DRWClosed')) {
                    internal.expandDropdown($this);
                } else {
                    internal.retractDropdown($this);
                }
                return false;
            });

            $target.addClass('DRWInitialized');
            $target.addClass('DRWClosed');
        },

        recalculateDaterange: function () {
            var datePreset = internal.getDaterangePreset();

            var dates = $datepicker.DatePickerGetDate()[0];

            var d = datePreset;

            if (d != null) {
                dates[0] = d[0];
                dates[1] = d[1];
            }

            $.each(internal.options.ranges, function (key, value) {
                if (moment(value[0]).format('ll') == moment(dates[0]).format('ll')
                    && moment(value[1]).format('ll') == moment(dates[1]).format('ll')) {
                    $daterangePreset.val(key);
                }
            });

            if (!$daterangePreset.val()) {
                $daterangePreset.val('custom');
            }

            $datepicker.DatePickerSetDate(dates);

            internal.recalculateComparison();
        },

        recalculateComparison: function () {
            var dates = $datepicker.DatePickerGetDate()[0];
            if (dates.length >= 2) {
                var comparisonPreset = internal.getComparisonPreset();
                //console.log(comparisonPreset);
                switch (comparisonPreset) {
                case 'previousperiod':
                    var days = parseInt((dates[1] - dates[0]) / (24 * 3600 * 1000));
                    dates[2] = new Date(dates[0]).setDate(dates[0].getDate() - (days + 1));
                    dates[3] = new Date(dates[1]).setDate(dates[1].getDate() - (days + 1));
                    break;
                case 'previousyear':
                    dates[2] = new Date(dates[0]).setFullYear(dates[0].getFullYear(dates[0]) - 1);
                    dates[3] = new Date(dates[1]).setFullYear(dates[1].getFullYear(dates[1]) - 1);
                    break;
                }
                $datepicker.DatePickerSetDate(dates);
                //console.log('comp', $this.val());
                $('.comparison-daterange input.dr', $dropdown).prop('disabled', (comparisonPreset == 'custom' ? false : true));
                internal.refreshForm();
            }
        },

        /**
         * Loads values from target element's data to controls.
         */
        loadValues: function ($target) {
            var values = $target.data('DateRangesWidget').options.values;

            $('.dr1.from', $dropdown).val(values.dr1from);
            $('.dr1.from', $dropdown).change();

            $('.dr1.to', $dropdown).val(values.dr1to);
            $('.dr1.to', $dropdown).change();

            $('.dr2.from', $dropdown).val(values.dr2from);
            $('.dr2.from', $dropdown).change();

            $('.dr2.to', $dropdown).val(values.dr2to);
            $('.dr2.to', $dropdown).change();

            $daterangePreset.val(values.daterangePreset);
            $daterangePreset.change();

            if (values.comparisonEnabled === true || values.comparisonEnabled === 'true') {
                $enableComparison.prop('checked', true);
                $enableComparison.change();
            } else {
                $enableComparison.removeProp('checked');
                $enableComparison.change();
            }

            if (values.comparisonPreset) {
                $comparisonPreset.val(values.comparisonPreset);
                $comparisonPreset.change();
            }
        },

        /**
         * Stores values from controls to target element's data.
         */
        saveValues: function ($target) {
            var data = $target.data('DateRangesWidget');
            var values = data.options.values;

            values.daterangePreset = internal.getDaterangePresetVal();
            values.dr1from = $('.dr1.from', $dropdown).val();
            values.dr1to = $('.dr1.to', $dropdown).val();
            values.dr1from_millis = $('.dr1.from_millis', $dropdown).val();
            values.dr1to_millis = $('.dr1.to_millis', $dropdown).val();

            values.comparisonEnabled = internal.getComparisonEnabled();
            values.comparisonPreset = internal.getComparisonPreset();
            values.dr2from = $('.dr2.from', $dropdown).val();
            values.dr2to = $('.dr2.to', $dropdown).val();

            values.dr2from_millis = $('.dr2.from_millis', $dropdown).val();
            values.dr2to_millis = $('.dr2.to_millis', $dropdown).val();
            $target.data('DateRangesWidget', data);

            if ($target.data().DateRangesWidget.options.apply)
                $target.data().DateRangesWidget.options.apply(values);

        },

        /**
         * Updates target div with data from target element's data
         */
        updateDateField: function ($target) {
            var values = $target.data("DateRangesWidget").options.values;
            if (values.dr1from && values.dr1to) {
                $('span.main', $target).text(moment(values.dr1from).format('ll')
                    + ' - '
                    + moment(values.dr1to).format('ll'));
            } else if (values.daterangePreset) {
                var dates = db.datePresets[values.daterangePreset].dates();
                $('span.main', $target).text(dates[0] + ' - ' + dates[1]);
            } else {
                $('span.main', $target).text('N/A');
            }

            if (values.comparisonEnabled && values.dr2from && values.dr2to) {
                $('.date-range-field').addClass('comparison-enabled');
                $('span.comparison', $target).text(moment(values.dr2from).format('ll')
                    + ' - '
                    + moment(values.dr2to).format('ll'));
                $('span.comparison', $target).show();
                $('span.comparison-divider', $target).show();
            } else {
                $('.date-range-field').removeClass('comparison-enabled');
                $('span.comparison-divider', $target).hide();
                $('span.comparison', $target).hide();
            }

            return true;
        },

        getDaterangePresetVal: function () {
            return $daterangePreset.val();
        },

        getDaterangePreset: function () {
            if (!$daterangePreset.val() || $daterangePreset.val() == 'custom') {
                return null;
            }

            return internal.options.ranges[$daterangePreset.val()];
        },

        setDaterangePreset: function (value) {
            $daterangePreset.val(value);
            $daterangePreset.change();
        },

        setComparisonEnabled: function (enabled) {
            if (enabled) {
                $('.comparison-daterange').show();
                $('.comparison-preset').removeProp('disabled');
            } else {
                $('.comparison-daterange').hide();
                $('.comparison-preset').prop('disabled', true);
            }
            $datepicker.DatePickerSetMode(enabled ? 'tworanges' : 'range');
        },

        getComparisonEnabled: function () {
            return $enableComparison.prop('checked');
        },

        getComparisonPreset: function () {
            return $comparisonPreset.val();
        },

        populateDateRangePresets: function (options) {
            var valueBackup = $daterangePreset.val();

            $daterangePreset.html('');

            $.each(options.ranges, function (text, dates) {
                $daterangePreset.append($("<option/>", {
                    value: text,
                    text:  text
                }));
            });

            $daterangePreset.append($("<option/>", {
                value: 'custom',
                text:  'Custom'
            }));

            $daterangePreset.val(valueBackup);
        },

        expandDropdown: function ($target) {
            var options = $target.data("DateRangesWidget").options;
            $currentTarget = $target;

            internal.populateDateRangePresets(options);

            internal.loadValues($target);

            // retract all other dropdowns
            $('.DRWOpened').each(function () {
                internal.retractDropdown($(this));
            });

            var leftDistance = $target.offset().left;
            var rightDistance = $(document).width() - $target.offset().left - $target.width();
            $dropdown.show();
            if (rightDistance > leftDistance) {
                $dropdown.offset({
                    left: $target.offset().left,
                    top:  $target.offset().top + $target.height() + 16
                });
            } else {
                // align right edges
                var fix = parseInt($dropdown.css('padding-left').replace('px', '')) +
                    parseInt($dropdown.css('padding-right').replace('px', '')) +
                    parseInt($dropdown.css('border-left-width').replace('px', '')) +
                    parseInt($dropdown.css('border-right-width').replace('px', ''))
                $dropdown.offset({
                    left: $target.offset().left + $target.width() - $dropdown.width() - fix,
                    top:  $target.offset().top + $target.height() + 16
                });
            }

            // switch to up-arrow
            $('.date-range-field a', $target);
            $target.addClass('DRWOpened');
            $target.removeClass('DRWClosed');

            // refresh
            internal.recalculateDaterange();
        },

        retractDropdown: function ($target) {
            //console.log('retract', $target);

            $dropdown.hide();
            $('.date-range-field', $target).css({borderBottomLeftRadius: 5, borderBottomRightRadius: 5});
            $target.addClass('DRWClosed');
            $target.removeClass('DRWOpened');
        },

        getMonday: function (d) {
            d = new Date(d);
            var day = d.getDay();
            var diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
            return new Date(d.setDate(diff));
        }

    };

    $.fn.DateRangesWidget = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.DateRangesWidget');
        }
    };
});
