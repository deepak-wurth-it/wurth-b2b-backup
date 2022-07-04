define([
        'jquery',
        'uiComponent',
        'accordion'
    ],
    function ($, Component) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                this.addAccordion();
                this.increDecreQty();
            },
            increDecreQty: function(){
                let self = this;
                $(document).on('click', '.increaseQty, .decreaseQty', function () {
                    let inputElem = $(this).parents('.boxContaner').find('.qty-input');
                    let currentQty = inputElem.val();
                    if (currentQty < 1) {
                        inputElem.val(1);
                        return;
                    }
                    let qty = currentQty;
                    let item_id = $(this).attr("data-item-id");
                    if ($(this).hasClass('increaseQty')) {
                        qty = parseInt(currentQty) + parseInt(1);
                        inputElem.val(qty);
                        self.updateQty(qty, item_id);
                    } else {
                        if (currentQty > 1) {
                            qty = parseInt(currentQty) - parseInt(1);
                            inputElem.val(parseInt(currentQty) - parseInt(1));
                            self.updateQty(qty, item_id);
                        }
                    }
                });

                // on field value up
                $(document).on('keyup', '.qty-input', function () {
                    let item_id = $(this).attr("data-item-id");
                    let qty = $(this).val();
                    if (qty < 1) {
                        $(this).val(1);
                    }
                    if(qty && qty != "0" && item_id){
                        self.updateQty($(this).val(), item_id);
                    }

                });
            },
            updateQty: function (qty, item_id){
                let self = this;
                self.updateColor(item_id);
                /*let self = this;
                let url = urlBuilder.build("wcbrequisition/items/updateitem");
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {item_id:item_id, qty:qty},
                    showLoader: true,
                }).success(function (response) {
                    self.updateColor(item_id);
                });
                */

            },
            updateColor: function(item_id){
                //Update qty field
                let totalQtyUpdate = parseFloat($("#qty-input-" + item_id).val()) * $(".minimum-qty-" + item_id).val();
                let availableQty = parseFloat($('#qty-input-' + item_id).attr("data-availableqty"));
                $(".total-qty-" + item_id).val(totalQtyUpdate);

                $("#stock-color-" + item_id).removeClass('redBox');
                $("#stock-color-" + item_id).removeClass('yellowBox');
                $("#stock-color-" + item_id).removeClass('greenBox');
                $("#stock-color-" + item_id).removeClass('blueBox');

                if (totalQtyUpdate < availableQty) {
                    $("#stock-color-" + item_id).addClass('greenBox');
                }
                if (totalQtyUpdate == availableQty) {
                    $("#stock-color-" + item_id).addClass('yellowBox');
                }
                if (totalQtyUpdate > availableQty) {
                    $("#stock-color-" + item_id).addClass('blueBox');
                }
                if (availableQty == 0) {
                    $("#stock-color-" + item_id).addClass('redBox');
                }
            },
            addAccordion: function(){
                $("#elements").accordion({
                    multipleCollapsible: true,
                    collapsible: true,
                    active: false,
                    animate: {
                        duration: 400
                    }
                });
            }
        });
    });
