(function ($) {
    "use strict";

    /*===============================
    =         Wow Active            =
    ================================*/

    new WOW().init();
    
    /*=============================================
    =       Menu sticky & Scroll to top          =
    =============================================*/
	var windows = $(window);
	var screenSize = windows.width();
	var sticky = $('.header-sticky');
	var $html = $('html');
	var $body = $('body');

	windows.on('scroll', function () {
		var scroll = windows.scrollTop();
		var headerHeight = sticky.height();

		if (screenSize >= 320) {
			if (scroll < headerHeight) {
				sticky.removeClass('is-sticky');
			} else {
				sticky.addClass('is-sticky');
            }
		}

    });
    /*----------  Scroll to top  ----------*/
    function scrollToTop() {
        var $scrollUp = $('#scroll-top'),
            $lastScrollTop = 0,
            $window = $(window);

        $window.on('scroll', function () {
            var st = $(this).scrollTop();
            if (st > $lastScrollTop) {
                $scrollUp.removeClass('show');
            } else {
                if ($window.scrollTop() > 200) {
                    $scrollUp.addClass('show');
                } else {
                    $scrollUp.removeClass('show');
                }
            }
            $lastScrollTop = st;
        });

        $scrollUp.on('click', function (evt) {
            $('html, body').animate({scrollTop: 0}, 600);
            evt.preventDefault();
        });
    }
    scrollToTop();
    
    
    /*==========================================
    =            mobile menu active            =
    ============================================*/
    
    $("#mobile-menu-trigger").on('click', function(){
        $("#mobile-menu-overlay").addClass("active");
        $body.addClass('no-overflow');
    });
    
    $("#mobile-menu-close-trigger").on('click', function(){
        $("#mobile-menu-overlay").removeClass("active");
        $body.removeClass('no-overflow');
    });
    
    /*Close When Click Outside*/
    $body.on('click', function(e){
        var $target = e.target;
        if (!$($target).is('.mobile-menu-overlay__inner') && !$($target).parents().is('.mobile-menu-overlay__inner') && !$($target).is('#mobile-menu-trigger') && !$($target).is('#mobile-menu-trigger i')){
            $("#mobile-menu-overlay").removeClass("active");
            $body.removeClass('no-overflow');
        }
    });
    
      
    /*=========================================
    =             open menu Active            =
    ===========================================*/
    
    $("#menu-close-trigger").on('click', function(){
        $("#page-oppen-off-sidebar-overlay").removeClass("active");
        $body.removeClass('no-overflow');
    });
    
    
    /*=============================================
    =            background image            =
    =============================================*/

    var bgSelector = $(".bg-img");
    bgSelector.each(function (index, elem) {
        var element = $(elem),
            bgSource = element.data('bg');
        element.css('background-image', 'url(' + bgSource + ')');
    });
    $(document).ready(function(){
        

    /*=============================================
    =            swiper slider activation            =
    =============================================*/

 
    var brandLogoSlider = new Swiper('.brand-logo-slider__container', {
        slidesPerView : 6,
        loop: true,
        speed: 1000,
        spaceBetween : 30,
        autoplay: {
            delay: 3000,
        },

        breakpoints: {
            1499:{
                slidesPerView : 6
            },

            991:{
                slidesPerView : 4
            },

            767:{
                slidesPerView : 3

            },

            575:{
                slidesPerView : 2
            }
        }
    });

    var carouselSlider = new Swiper('.carousel-slider__container', {
        slidesPerView : 3,
        slidesPerGroup: 1,
        loop: true,
        speed: 1000,
        spaceBetween : 30,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination-9',
            type: 'bullets',
            clickable: true
        },
        breakpoints: {
            1499:{
                slidesPerView : 3
            },

            991:{
                slidesPerView : 2
            },

            767:{
                slidesPerView : 1

            },

            575:{
                slidesPerView : 1
            }
        }
    });

        /*=====  End of swiper slider activation  ======*/
    });
    
    
    /*=================================- 
    =        Scroll Up Color Change    =
    ==================================-*/

    $('.slide-scroll-bg').height('.slide-scroll-bg').scrollie({
        scrollOffset: 0,
        scrollingInView: function (elem) {
            console.log(elem);
            var bgColor = elem.data('background');
            $('.bg-body-color').css('background-color', bgColor);

        }
    });

    /*=============================================
    =            light gallery active            =
    =============================================*/
    
    $('.popup-images').lightGallery(); 

    $('.video-popup').lightGallery(); 
    
})(jQuery);
