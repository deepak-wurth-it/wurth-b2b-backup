define(['jquery'], function($){
    "use strict";
    return function hello()
    {
		jQuery('.nav-menu').css('display','none');
		jQuery('.minicart-wrap').css('display','none');
		var listss = jQuery('.opc-progress-bar li');
        listss.filter(':nth-child(2)').insertBefore(listss.filter(':nth-child(1)'));
    }
});