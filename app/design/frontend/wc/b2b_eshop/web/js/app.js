require(
  [
      'jquery', 
      'wcbglobal'
  ],
function ($, wcbglobal) {
          "use strict";
          var logged=0;
          if(logged==1){
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
          }else{
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
          
          // js start for mobilemenu.phtml

          $(document).on("click",".mob-tabs", function(evt){
            let cityName = $(this).attr("data-cityname");
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
          });  
          $("#defaultOpen").click();   
           // js end for mobilemenu.phtml       
      });
