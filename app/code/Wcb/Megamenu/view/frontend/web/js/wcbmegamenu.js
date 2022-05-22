require(
  [
      'jquery', 
      'wcbmegamenu'
  ], function(jQuery){
				
  (function(jQuery){
       jQuery.fn.extend({  
         mobilemenu: function() {       
          return this.each(function() {
            
            var jQueryul = jQuery(this);
            
            if(jQueryul.data('accordiated'))
              return false;
                              
            jQuery.each(jQueryul.find('ul, li>div'), function(){
              jQuery(this).data('accordiated', true);
              jQuery(this).hide();
            });
            
            jQuery.each(jQueryul.find('span.head'), function(){
              jQuery(this).click(function(e){
                activate(this);
                return void(0);
              });
            });
            
            var active = (location.hash)?jQuery(this).find('a[href=' + location.hash + ']')[0]:'';

            if(active){
              activate(active, 'toggle');
              jQuery(active).parents().show();
            }
            
            function activate(el,effect){
              jQuery(el).parent('li').toggleClass('active').siblings().removeClass('active').children('ul, div').slideUp('fast');
              jQuery(el).siblings('ul, div')[(effect || 'slideToggle')]((!effect)?'fast':null);
            }
            
          });
        } 
      }); 
    })(jQuery);

    jQuery("ul.mobilemenu li.level1, ul.level2 li").each(function(){ 
      if(jQuery(this).find('li').length > 0) {
        jQuery(this).addClass('have_sub');
        jQuery(this).append('<span class="head"><a href="javascript:void(0)"></a></span>');
      }
      });			
    jQuery('ul.mobilemenu').mobilemenu();
    jQuery("ul.mobilemenu li.active").each(function(){
      jQuery(this).children().next("ul").css('display', 'block');
    });
    
    //mobile
    jQuery('.btn-navbar').click(function() {
      
      var chk = 0;
      if ( jQuery('#navbar-inner').hasClass('navbar-inactive') && ( chk==0 ) ) {
        jQuery('#navbar-inner').removeClass('navbar-inactive');
        jQuery('#navbar-inner').addClass('navbar-active');
        jQuery('#ma-mobilemenu').css('display','block');
        chk = 1;
      }
      if (jQuery('#navbar-inner').hasClass('navbar-active') && ( chk==0 ) ) {
        jQuery('#navbar-inner').removeClass('navbar-active');
        jQuery('#navbar-inner').addClass('navbar-inactive');			
        jQuery('#ma-mobilemenu').css('display','none');
        chk = 1;
      }
    }); 
    
    

    require(["jquery"], function(jQuery){



      jQuery("#pt_menu_link ul li").each(function(){
        var url = document.URL;
        jQuery("#pt_menu_link ul li a").removeClass("act");
        jQuery('#pt_menu_link ul li a[href="'+url+'"]').addClass('act');
      }); 
      
      jQuery('.pt_menu_no_child').hover(function(){
        jQuery(this).addClass("active");
      },function(){
        jQuery(this).removeClass("active");
      })
      
      jQuery('.pt_menu').hover(function(){
        if(jQuery(this).attr("id") != "pt_menu_link"){
          jQuery(this).addClass("active");
        }
      },function(){
        jQuery(this).removeClass("active");
      })
      
      jQuery('.pt_menu').hover(function(){
         /*show popup to calculate*/
         jQuery(this).find('.popup').css('display','inline-block');
         
         /* get total padding + border + margin of the popup */
         var extraWidth       = 0
         var wrapWidthPopup   = jQuery(this).find('.popup').outerWidth(true); /*include padding + margin + border*/
         var actualWidthPopup = jQuery(this).find('.popup').width(); /*no padding, margin, border*/
         extraWidth           = wrapWidthPopup - actualWidthPopup;    
         
         /* calculate new width of the popup*/
         var widthblock1 = jQuery(this).find('.popup .block1').outerWidth(true);
         var widthblock2 = jQuery(this).find('.popup .block2').outerWidth(true);
         var new_width_popup = 0;
         if(widthblock1 && !widthblock2){
           new_width_popup = widthblock1;
         }
         if(!widthblock1 && widthblock2){
           new_width_popup = widthblock2;
         }
         if(widthblock1 && widthblock2){
          if(widthblock1 >= widthblock2){
            new_width_popup = widthblock1;
          }
          if(widthblock1 < widthblock2){
            new_width_popup = widthblock2;
          }
         }
         var new_outer_width_popup = new_width_popup + extraWidth;
         
         /*define top and left of the popup*/
         var wraper = jQuery('.pt_custommenu');
         var wWraper = wraper.outerWidth();
         var posWraper = wraper.offset();
         var pos = jQuery(this).offset();
         
         var xTop = pos.top - posWraper.top + CUSTOMMENU_POPUP_TOP_OFFSET;
         var xLeft = pos.left - posWraper.left;
         if ((xLeft + new_outer_width_popup) > wWraper) xLeft = wWraper - new_outer_width_popup;
         
         /*show hide popup*/
         if(CUSTOMMENU_POPUP_EFFECT == 0) jQuery(this).find('.popup').stop(true,true).slideDown('slow');
         if(CUSTOMMENU_POPUP_EFFECT == 1) jQuery(this).find('.popup').stop(true,true).fadeIn('slow');
         if(CUSTOMMENU_POPUP_EFFECT == 2) jQuery(this).find('.popup').stop(true,true).show();
      },function(){
         if(CUSTOMMENU_POPUP_EFFECT == 0) jQuery(this).find('.popup').stop(true,true).slideUp();
         if(CUSTOMMENU_POPUP_EFFECT == 1) jQuery(this).find('.popup').stop(true,true).fadeOut('slow');
         if(CUSTOMMENU_POPUP_EFFECT == 2) jQuery(this).find('.popup').stop(true,true).hide('fast');
      })

    });

    
          jQuery(".mobile-menu .pt_menu .withchilds").click(function(){	
      var myId = jQuery(this).closest('div').next().attr('id');                          										         			
             jQuery('#' + myId).addClass('onn');
        // jQuery(".mob-popup").addClass('onn');
              
               });
      jQuery(".tablinks").click(function(){
       jQuery(".mob-popup").removeClass('onn');						
         jQuery(".mob-popup").addClass('off');
    });
    jQuery(".mobile-bar-close").click(function(){
       jQuery(".mob-popup").removeClass('onn');
     jQuery(".mob-popup").addClass('off');
    });
    

jQuery(".mobile-menu #haschilds .parentMenu a").attr('href', '#');
jQuery(".mobile-menu .cmsPop").css("display","block");
jQuery('.mobile-menu .cmsPop').removeClass('popup').addClass('mob-popup');
jQuery(".mobile-menu .pt_menu .haschilds").click(function(){			
            jQuery(this).closest("div").prev().addClass('onn');					 				 
               });
    
  });