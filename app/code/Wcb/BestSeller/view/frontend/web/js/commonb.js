/**
 * Created By : Rohan Hapania
 */
require(['jquery', 'owlcarousel'], function($) {
    $(document).ready(function() {
        $('.bestSeller .owl-carousel').owlCarousel({
	        loop: true,
	        margin: 30,
	        nav: true,
	        navText: [
		        "<span class='icon-interface-left'></span>",
		        "<span class='icon-interface-right'></span>"
	        ],
	        autoplay: true,
			items : 1, // THIS IS IMPORTANT
			responsiveClass:true,
	        autoplayHoverPause: true,
			pagination: false,
	        responsive: {
	            0: {
	              items: 2
	            },
	            600: {
	              items: 3
	            },
	            1000: {
	              items: 6
	            }
	        }
	    });
		$('.owl-carousel').trigger('refresh.owl.carousel');
        window.dispatchEvent(new Event('resize'));
    });
});
