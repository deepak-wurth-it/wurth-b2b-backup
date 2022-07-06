define([
        'jquery',
        'uiComponent',
        'mage/url',
        'accordion'
    ],
    function ($, Component, urlBuilder) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
                this.addAccordion();
                this.increDecreQty();
                this.deleteItemList();
                this.addItemsToCart();
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
                        self.updateQty(item_id);
                    } else {
                        if (currentQty > 1) {
                            qty = parseInt(currentQty) - parseInt(1);
                            inputElem.val(parseInt(currentQty) - parseInt(1));
                            self.updateQty(item_id);
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
                        self.updateQty(item_id);
                    }

                });
            },
            updateQty: function (item_id){
                let self = this;
                let url = urlBuilder.build("wcbrequisition/items/updateitem");
                let unit_qty = parseFloat($("#qty-input-" + item_id).val());
                let minimum_qty = parseFloat($(".minimum-qty-" + item_id).val());
                let total_qty = unit_qty * minimum_qty;

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {item_id:item_id, qty: total_qty},
                    showLoader: true,
                }).success(function (response) {
                    if(response.success == 'true'){
                        self.updateColor(item_id);
                    }
                });
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
            deleteItemList: function(){
                $(document).on('click', '.delete-list', function () {
                    let listId = $(this).attr('data-id');
                    let url = urlBuilder.build("wcbrequisition/items/deletelist");
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {list_id : listId},
                        showLoader: true,
                    }).success(function (response) {
                        if(response.success == 'true'){
                            location.reload();
                        }
                    });

                });
            },
            addItemsToCart: function(){
                $(document).on('click', '.add-to-cart-list', function () {
                    let listId = $(this).attr('data-id');
                    let url = urlBuilder.build("wcbrequisition/items/addtocartlist");
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {list_id : listId},
                        showLoader: true,
                    }).success(function (response) {

                    });
                });
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
