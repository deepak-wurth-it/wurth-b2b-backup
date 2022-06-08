require(
    [
        'jquery',
        'wcbglobal',
        "mage/url"
    ],
    function($, wcbglobal,urlBuilder) {
        "use strict";

        var loginCheckUrl = urlBuilder.build('/wcbglobal/customer/logincheck');
        $.ajax({
                method: "GET",
                url: loginCheckUrl
            })
            .done(function(data) {
                if (data.success == true) {
                    $(".wcb-logged").show();
                    $(".wcb-not-logged").hide();
                    // Category page
                    $(".product-item-inner").addClass("logdIn");
                    $(".variation-box").addClass("logdIn");
                    // Level Six
                    $(".attrow.loginprice").addClass("logdIn");
                    $(".accordion.level-six").addClass("userlog");
                    $(".boxcontaner").addClass("logdIn");
                    $(".attrow.qtywrapp").addClass("logdIn");
                    $(".attrow.pc").addClass("logdIn");
                    $(".veCount").addClass("logdIn");
                    $(".attrow.cart-color").addClass("bluecol");
                    // Header
                    $(".account-wraper").addClass("log-in");
                    // console.log('user loged in');
                } else {
                    $(".wcb-not-logged").show();
                    $(".wcb-logged").hide();
                    // Category page
                    $(".product-item-inner").addClass("notLog");
                    $(".variation-box").addClass("notLog");
                    // Level Six
                    $(".attrow.cart-color").addClass("redcol");
                    // Header
                    $(".account-wraper").addClass("log-out");
                    // console.log('user not loged');
                }
            });

            $('.qty-default').keyup(function (e) {
                var value = $(this).val();
                 if (value <= 1 ) {
                    this.value = this.value.replace(value, '1');
                }
            });
            $('.input-text.qty').keyup(function (e) {
                if($(this).hasClass('cart-item-qty-box')){
                    return;
                }
                var value = $(this).val();
                 if (value <= 1 ) {
                    this.value = this.value.replace(value, '1');
                }
            });
            
            // On checkout page button enable disable start
				$('#accept_terms').click(function() {
					if ($(this).is(':checked')) {
				$('.action.primary.checkout').prop("disabled", false);
			 } else {
					$('.action.primary.checkout').attr('disabled',true);}
			});
            // On checkout page button enable disable end

    });
