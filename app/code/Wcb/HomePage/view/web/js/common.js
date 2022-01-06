/**
 * Created By : Rohan Hapani
 */
 require(['jquery', 'owlcarousel'], function($) {
    $(document).ready(function() {
        $('.owl-carousels').owlCarousel({
	        loop: true,
	        margin: 30,
	        nav: true,
	        navText: [
		        "<span class='icon-interface-left'></span>",
		        "<span class='icon-interface-right'></span>"
	        ],
	        autoplay: true,
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
    });
});
