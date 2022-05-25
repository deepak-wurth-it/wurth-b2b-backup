require(
  [
      'jquery', 
      'mobilemenu'
  ],
function ($, mobilemenu) {
          "use strict";          
          // js start for mobilemenu.phtml
          console.log('mobile menu js loading');
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
