define([
        "jquery",
        "mage/url",
        "wcbglobal"
    ],
    function($, urlBuilder, wcbglobal) {
        "use strict";

        let wcsoap = new Object()
        wcsoap.soapPrice = function(productCode) {
            var GetItemEShopSalesPriceAndDisc = urlBuilder.build('/wcbcatalog/ajax/GetItemEShopSalesPriceAndDisc');
            console.log(wcbglobal.isLogin());
            $.ajax({

                type: "POST",
                url: GetItemEShopSalesPriceAndDisc,
                data: {
                    skus: productCode
                },
                cache: false,
                async: false,
                success: function(result) {
                    if (result.success) {
                        var finalResult = result.success;

                        if (finalResult.suggestedPriceAsTxtP) {
                            $('#suggestedPriceAsTxtP').html(finalResult.suggestedPriceAsTxtP);
                            $('#suggestedDiscountAsTxtP').html(finalResult.suggestedDiscountAsTxtP);
                            $('#suggestedSalesPriceInclDiscAsTxtP').html(finalResult.suggestedSalesPriceInclDiscAsTxtP);
                            $("#price_soap").css({
                                display: "block"
                            });
                            $("#price_loader").css({
                                display: "none"
                            });
                        }

                        console.log(result.success);
                    } else {

                    }
                }

            });
        };

        wcsoap.soapStock = function(productCode) {
            var GetItemAvailabilityOnLocation = urlBuilder.build('/wcbcatalog/ajax/GetItemAvailabilityOnLocation');
            //console.log(wcbglobal.isLogin());
            $.ajax({

                type: "POST",
                url: GetItemAvailabilityOnLocation,
                data: {
                    sku: productCode
                },
                cache: false,
                async: false,
                success: function(result) {
                    if (result.success) {
                        var finalResult = result.success;

                        if (finalResult.availableQtyAsTxtP) {
                            $('#deliverydayP').html(finalResult.remain_days);
                            var buttonPlus = $('.increaseQty');
                            var buttonMinus = $('.decreaseQty');
                            var avlQty = parseInt(finalResult.availableQtyAsTxtP);
						
                            wcsoap.setStockColor(avlQty);
                            $(buttonMinus).on('click', function(e) {
								
								  wcsoap.setStockColor(avlQty);
								  
							  
						     });
                         
                            $(buttonPlus).on('click', function(e) {
								  wcsoap.setStockColor(avlQty);
							});
                        }

                        //console.log(result.success);
                    } else {

                    }
                }

            });
        };

        wcsoap.setStockColor = function(avlQty) {
            var buttonPlus = $('.increaseQty');
            var buttonMinus = $('.decreaseQty');
            var qtyInput = $("#cart-79-qty");
            var qtyInputName = 'qty';
            var minSaleQty = $("#minimum_sales_quantity_qty");
            var baseUnitMId = $("#base_unit_of_measure_id_qty");
            var actualQuantity;
            var baseunit, minsales, qtyinput;
            baseunit = parseInt(baseUnitMId.val());
            minsales = parseInt(minSaleQty.val());
            qtyinput = parseInt(qtyInput.val());
            avlQty = parseInt(avlQty);
            actualQuantity = qtyinput;
            
            

            if (baseunit == '2' && minsales && actualQuantity) {
                var baseunit = parseInt('100');
                actualQuantity = baseunit * minsales * qtyinput;
            }
            if ((minsales && qtyinput) && (baseUnitMId.val() != '2' || baseUnitMId.val() == '')) {
                actualQuantity = minsales * qtyinput;
            }

			if(actualQuantity < avlQty ){
				  //then show Stock status color - green and no display of delivery days
				  $("#stockStatusP").addClass( "greenbox" );
								
							  
			}
			
			if(actualQuantity == avlQty){
				  // then show Stock status color - yellow and no display of delivery days		
				   $("#stockStatusP").addClass( "yellowbox" );
							  
			}
			
			if(actualQuantity > avlQty && avlQty != '0'){
				    //then show Stock status color - Blue and show delivery days	  
				    $("#stockStatusP").addClass( "blubox" );
				    $("#deliverydayVan").addClass( "bluevan" );

				    $("#deliverydayP").css("display", "block");
				    $("#deliverydayVan").css("display", "block");
				    
					 
			}
			
			if(avlQty == '0'){
					//then show Stock status color - Red and show delivery days
				    $("#stockStatusP").addClass( "redbox" );
				    $("#deliverydayVan").addClass( "redvan" );
				    
				    $("#deliverydayP").css("display", "block");
				    $("#deliverydayVan").css("display", "block");
			}
			console.log(actualQuantity);
            return actualQuantity;
        };


        return wcsoap;

    });
