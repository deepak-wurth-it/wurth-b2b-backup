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

			// counter in about us  page

            $(window).scroll(startCounter);
            function startCounter() {
                if($('.wcb-counter').length == 0){
            		return;
            	}
                var hT = $('.wcb-counter').offset().top,
                    hH = $('.wcb-counter').outerHeight(),
                    wH = $(window).height();
                if ($(window).scrollTop() > hT+hH-wH) {
                    $(window).off("scroll", startCounter);
                    $('.wcb-counter').each(function () {
                        var $this = $(this);
                        $({ Counter: 0 }).animate({ Counter: $this.text() }, {
                            duration: 2000,
                            easing: 'swing',
                            step: function () {
                                $this.text(Math.ceil(this.Counter));
                            }
                        });
                    });
                }
            }

			// counter in about us page

        // where-do-we-meet js start for search
        $('.wcb-no-record').hide();
        $("#wcb-search").keyup(function() {
            var filter = $(this).val(),
              count = 0;
            // Loop through the comment list
            $('.wcb-card-body').each(function() {
              console.log(count);
              if(count > 0){
                $('.wcb-no-record').hide();
              }
              else{
                $('.wcb-no-record').show();
              }
              if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).hide();
              } else {
                $(this).show();
                count++;
              }

            });

          });

        // where-do-we-meet end

          // for navigation
          $(document).ready(function(){
            //alert(window.location.href);
            $(".parentMenu a").each(function(){
                if($(this).attr("href")==window.location.href)
                    $(this).addClass("actv");
            })
        })

    });
