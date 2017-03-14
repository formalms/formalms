                                                    
    	//Start Fix MegaNavbar on scroll page
    	var navHeight = $('#main_navbar').offset().top;
    	FixMegaNavbar(navHeight);
    	$(window).bind('scroll', function() {FixMegaNavbar(navHeight);});

    	function FixMegaNavbar(navHeight) {
    	    if (!$('#main_navbar').hasClass('navbar-fixed-bottom')) {
    	        if ($(window).scrollTop() > navHeight) {
    	            $('#main_navbar').addClass('navbar-fixed-top')
    	            $('body').css({'margin-top': $('#main_navbar').height()+'px'});
    	            if ($('#main_navbar').parent('div').hasClass('container')) $('#main_navbar').children('div').addClass('container').removeClass('container-fluid');
    	            else if ($('#main_navbar').parent('div').hasClass('container-fluid')) $('#main_navbar').children('div').addClass('container-fluid').removeClass('container');
    	        }
    	        else {
    	            $('#main_navbar').removeClass('navbar-fixed-top');
    	            $('#main_navbar').children('div').addClass('container-fluid').removeClass('container');
    	            $('body').css({'margin-top': ''});
    	        }
    	    }
    	}
    	//Start Fix MegaNavbar on scroll page


    	//Start Fix MegaNavbar on scroll page
    	var tocHeight = $('#Table_of_Contents').offset().top;
    	FixTOC(tocHeight);
    	$(window).bind('scroll', function() {FixTOC(tocHeight);});

    	function FixTOC(tocHeight) {

    	        if ($(window).scrollTop() > (tocHeight-75)) {
                    $('#Table_of_Contents').css({'position':'relative', 'top':(($(window).scrollTop()-tocHeight)+90)+"px", 'right':'auto'});
    	        }
    	        else {
                    $('#Table_of_Contents').css({'position':'relative', 'top':'0px', 'right':'auto'});
    	        }
    	}
    	//Start Fix MegaNavbar on scroll page


    	//Next code used to prevent unexpected menu close when using some components (like accordion, tabs, forms, etc), please add the next JavaScript to your page
    	$( window ).load(function() {
    	    $(document).on('click', '.navbar .dropdown-menu', function(e) {e.stopPropagation();});
    	});

        /*SCROLL PAGE TO TOP*/
        $(document).ready(function() {
            $(".toTop").css("display", "none");

            $(window).scroll(function(){
                if($(window).scrollTop() > 0){$(".toTop").fadeIn("slow");} else {$(".toTop").fadeOut("slow");}
            });

            $(".toTop").click(function(){
                event.preventDefault();
                $("html, body").animate({scrollTop:0},"slow");
            });
        });

