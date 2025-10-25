jQuery(document).ready(function ($) {
    // document start


    // Navbar
    $("<span class='clickD'></span>").insertAfter(".navbar-nav li.menu-item-has-children > a");
    $('.navbar-nav li .clickD').click(function (e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.next().hasClass('show')) {
            $this.next().removeClass('show');
            $this.removeClass('toggled');
        }
        else {
            $this.parent().parent().find('.sub-menu').removeClass('show');
            $this.parent().parent().find('.toggled').removeClass('toggled');
            $this.next().toggleClass('show');
            $this.toggleClass('toggled');
        }
    });

    $(window).on('resize', function () {
        if ($(this).width() < 1025) {
            $('html').click(function () {
                $('.navbar-nav li .clickD').removeClass('toggled');
                $('.toggled').removeClass('toggled');
                $('.sub-menu').removeClass('show');
            });
            $(document).click(function () {
                $('.navbar-nav li .clickD').removeClass('toggled');
                $('.toggled').removeClass('toggled');
                $('.sub-menu').removeClass('show');
            });
            $('.navbar-nav').click(function (e) {
                e.stopPropagation();
            });
        }
    });
    // Navbar end



    /* ===== For menu animation === */
    $(".navbar-toggler").click(function () {
        $(".navbar-toggler").toggleClass("open");
        $(".navbar-toggler .stick").toggleClass("open");
        $('body,html').toggleClass("open-nav");
    });

    // Navbar end





    // to make sticky nav bar
    $(window).scroll(function () {
        var scroll = $(window).scrollTop();
        if (scroll > 200) {
            $(".main-head").addClass("fixed");
        }
        else {
            $(".main-head").removeClass("fixed");
        }
    })
    

    // home product slider start
    if ($('.featured-product-silder-wpr').length) {
        $('.featured-product-silder-wpr').slick({
            infinite: true,
            speed: 500,
            slidesToShow: 4,
            slidesToScroll: 1,
            dots: true,
            prevArrow: ".slick-nav-prev",
            nextArrow: ".slick-nav-next",
            // centerMode: true,
            //   centerPadding: '30px',
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                    }
                },
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 2,
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    }
    // home product slider end



    $('.p-quality .product-quality-accordion-item:first-child').find('.product-quality-accordion-content').slideDown();
    $('.p-quality .product-quality-accordion-item:first-child').find('.product-quality-accordion-title').addClass('active');
    $('.p-quality .product-quality-accordion-item:first-child').toggleClass('active');

    $('.product-quality-accordion-title').on('click', function () {
        $(this).next().stop(true, true).slideToggle();
        $(this).toggleClass('active');
        $(this).parents('.product-quality-accordion-item').toggleClass('active');
        $(this).parents('.product-quality-accordion-item').siblings().find('.product-quality-accordion-content').slideUp();
        $(this).parents('.product-quality-accordion-item').siblings().find('.product-quality-accordion-title').removeClass('active');
        $(this).parents('.product-quality-accordion-item').siblings().removeClass('active');
    });





})


// header search start
let mobileSearch = document.querySelector(".mobile-search");
let headerSearchClose = document.querySelector(".header-search-close");
let headerSearchFld = document.querySelector(".header-search-form");

if (mobileSearch) {
    mobileSearch.onclick = () => {
        headerSearchFld.classList.add("show");
    }
}

if (headerSearchClose) {
    headerSearchClose.onclick = () => {
        headerSearchFld.classList.remove("show");
    }
}
// header search end

jQuery(document).ready(function($) {
    $('body').on('click', '.quantity .plus, .quantity .minus', function() {
        var input = $(this).siblings('.qty');
        var currentValue = parseInt(input.val(), 10);
        var max = parseInt(input.attr('max'), 10) || 999;
        var min = parseInt(input.attr('min'), 10) || 1;
        var step = parseInt(input.attr('step'), 10) || 1;

        if ($(this).hasClass('plus')) {
            if (currentValue < max) {
                input.val(currentValue + step).change();
            }
        } else {
            if (currentValue > min) {
                input.val(currentValue - step).change();
            }
        }
    });
});

jQuery(document).ready(function($) {
    function toggleAccordion() {
        if ($(window).width() > 991) { // Desktop view
            $('.categories-list-outer').show(); // Always visible on desktop
            $('.toggle-icon').text('-'); // Show minus icon
        } else { // Mobile view
            $('.categories-list-outer').hide(); // Hidden by default
            $('.toggle-icon').text('+'); // Show plus icon
        }
    }

    toggleAccordion(); // Initial call on page load

    // Toggle accordion only on mobile
    $('.categories-tle h3').on('click', function() {
        if ($(window).width() <= 991) { // Only toggle on mobile
            $('.categories-list-outer').slideToggle(300, function() {
                var isVisible = $('.categories-list-outer').is(':visible');
                $('.toggle-icon').text(isVisible ? '-' : '+'); // Switch icon
            });
        }
    });

    // Re-check window size on resize
    $(window).resize(function() {
        toggleAccordion();
    });
});




/*jQuery(function($) {
    var $window = $(window),
	$footer = $('footer.cmn-gap.pb-0'), // The footer element
        $content = $('.products-crd-row'), // The section where products are displayed
        loading = false;

    // Function to check if the footer is visible
    function isFooterVisible() {
        var footerTop = $footer.offset().top,
            footerHeight = $footer.outerHeight(),
            windowHeight = $window.height(),
            scrollTop = $window.scrollTop();

        // Check if the footer is within the viewport
        return (scrollTop + windowHeight) >= footerTop && (scrollTop + windowHeight) <= (footerTop + footerHeight);
    }

    // Function to load more products when the footer is visible
    function loadMoreProducts() {
        var currentPage = parseInt(infinite_scroll_params.current_page);
        var maxPages = parseInt(infinite_scroll_params.max_pages);

        if (loading || currentPage >= maxPages) return;

        loading = true;
        infinite_scroll_params.current_page++;

        // Show a loading spinner or message (optional)
        $('body').addClass('loading');

        // Use pretty permalink structure for pagination
        var nextPageUrl = infinite_scroll_params.base_url + 'page/' + infinite_scroll_params.current_page + '/';

        $.ajax({
            url: infinite_scroll_params.ajax_url,
            type: 'GET',
            data: {
                action: 'load_more_products',
                paged: infinite_scroll_params.current_page,
                posts_per_page: 12, // Adjust as needed
            },
            success: function(response) {
                if (response) {
                    $content.append(response);
                    $('body').removeClass('loading');

                    // Update the URL with the new page number using pretty permalinks
                    history.pushState(null, null, nextPageUrl);

                    // If we've reached the last page, remove the pagination
                    if (infinite_scroll_params.current_page >= maxPages) {
                        $('.woocommerce-pagination').remove();
                    }
                }
                loading = false;
            }
        });
    }

    // Listen to scroll event to detect when the footer appears
    $window.scroll(function() {
        if (isFooterVisible()) {
			//alert(infinite_scroll_params.base_url);
            loadMoreProducts();
        }
    });
});*/


