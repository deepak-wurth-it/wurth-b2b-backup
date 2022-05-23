/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_QuickOrder/js/item-table/mass-add-rows',
    'jquery-ui-modules/widget'
], function ($, _, massAddRows) {
    'use strict';

    /**
     * This widget is used in setting of a flag when a file is chosen.
     */
    $.widget('mage.quickOrderFile', {
        options: {
            fileNameSelector: null,
            newBlock: '[data-role="new-block"]',
            skuSelector: '[data-role="product-sku"]',
            qtySelector: '[data-role="product-qty"]',
            showError: '[data-role="show-errors"]',
            extErrorText: '',
            dataError: {
                text: null
            },
            urlSku: '',
            errorType: 'file'
        },

        /**
         * This method binds elements found in this widget
         *
         * @private
         */
        _bind: function () {
            var handlers = {};

            // since the first handler is dynamic, generate the object using array notation
            handlers['change ' + this.options.fileNameSelector] = '_onFileNameChanged';

            this._on(handlers);
        },

        /**
         * This method constructs a new widget
         *
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * Callback for adds file
         *
         * @param {Object} e
         * @private
         */
        _onFileNameChanged: function (e) {
            var file = e.target.files[0],
                reader,
                contents;

            if (!file) {
                return;
            }

            if (file.name.split('.').pop() != 'csv') { //eslint-disable-line eqeqeq
                $(this.options.showError).trigger('addErrors', {
                    text: this.options.extErrorText
                });
                $('#open-file').modal('closeModal');
                return;
            }
            reader = new FileReader();

            /** On load start */
            reader.onloadstart = function () {
                $('body').trigger('processStart');
            };
            reader.onload = function (ev) {
                contents = ev.target.result;
                this._displaySkus(contents);
            }.bind(this);
            reader.readAsText(file);
        },

        /**
         * Set SKU name and QTY to list
         *
         * @param {String} contents
         * @private
         */
        _displaySkus: function (contents) {
            var lines = contents.split(/[\r\n]+/g),
                postArray = [],
                skuArray = [],
                itemCsv,
                singleSkuInput,
                item,
                qty;

            lines.shift();

            $.each(lines, function (index, val) {
                itemCsv = val.split(',');
                singleSkuInput = this._getSingleSkuInput(itemCsv[0], true);

                if (!singleSkuInput && skuArray.indexOf(itemCsv[0]) === -1) {
                    skuArray.push(itemCsv[0]);
                }
                item = {
                    sku: itemCsv[0],
                    qty: parseFloat(itemCsv[1])
                };

                if (singleSkuInput && this._isInArray(postArray, itemCsv[0])) {
                    qty = parseFloat(itemCsv[1]) + singleSkuInput.qty;
                    item = {
                        'sku': itemCsv[0],
                        'qty': qty
                    };
                }
                postArray.push(item);
            }.bind(this));

            $.when($.post(
                this.options.urlSku, {
                    'items': JSON.stringify(postArray),
                    'errorType': this.options.errorType
                }
            ), massAddRows.addNewRows($(this.options.newBlock + ':first'), skuArray.length)).done(function (result) {
                var data = result[0];

                this.options.dataError.text = null;
                let successImportCount = 0;
                let errorImportCount = 0;
                $.each(data.items, function (index, it) {
                    // store import success and error count on page
                    if(it.code == "success"){
                        successImportCount++;

                        //magento default flow
                        singleSkuInput = this._getSingleSkuInput(it.sku);

                        if (singleSkuInput != false) { //eslint-disable-line eqeqeq
                            it.toRewriteQty = true;
                            singleSkuInput.trigger('addRow', it);
                        }
                    }else{
                        errorImportCount++;
                        $(".error-import").show();
                        $(".error-import .error-import-count").html(errorImportCount);
                        let trHtml = "<tr>";
                        trHtml += "<td>" + it.sku + "</td>";
                        trHtml += "<td>" + it.qty + "</td>";
                        trHtml += "<td>" + it.result + "</td>";
                        trHtml += "</tr>";
                        $('.error-import-product tr:last').after(trHtml);
                    }
                    // End store import success and error count on page


                }.bind(this));
                //display success and error product code count
                if(successImportCount){
                    $(".success-import").show();
                    $(".success-import .success-import-count").html(successImportCount);
                    $("#add_to_cart_upload").prop("disabled", false);
                }
                if(errorImportCount){
                    $(".error-import").show();
                    $(".error-import .error-import-count").html(errorImportCount);
                }
                //End display success and error product code count

                if (data && data.generalErrorMessage && data.generalErrorMessage !== '') {
                    this.options.dataError.text = data.generalErrorMessage;
                }

                /* $(this.options.showError).trigger('addErrors', {
                     text: this.options.dataError.text
                 });*/
                this._clearField();
                $('.form-addbysku .deletable-item:not(:last)').hide();
                $('body').trigger('processStop');
            }.bind(this));
        },

        /**
         * Get first empty field
         *
         * @param {String} sku
         * @param {Boolean} skipEmpty
         * @returns {Boolean|Object} get false if we need skip and value empty
         * @private
         */
        _getSingleSkuInput: function (sku, skipEmpty) {
            var allSkuInputs = $(this.options.skuSelector),
                self = this,
                elem = false;

            $.each(allSkuInputs, function () {
                if ($(this).val() == '' && !skipEmpty || $(this).val() == sku) { //eslint-disable-line eqeqeq
                    elem = $(this);
                    elem.qty = parseFloat(elem.closest('.deletable-item').find(self.options.qtySelector).val());

                    return false;
                }
            });

            return elem;
        },

        /**
         * Method for clearing input file
         *
         * @private
         */
        _clearField: function () {
            $(this.options.fileNameSelector).val('');
        },

        /**
         * Check if an item is in post array.
         *
         * @param {Array} items
         * @param {String} sku
         * @returns {Boolean}
         * @private
         */
        _isInArray: function (items, sku) {
            return _.find(items, function (item) {
                return item.sku === sku;
            }) === undefined;
        }
    });

    return $.mage.quickOrderFile;
});
