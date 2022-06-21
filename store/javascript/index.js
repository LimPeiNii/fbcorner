$(document).ready(function(){
    //owl carousel - banners
    $('#banner .owl-carousel').owlCarousel({
        dots: true,
        loop: true,
        nav: false,
        lazyLoad: true,
        lazyLoadEager: 1,
        stagePadding: 0,
        items: 1,
        margin: 0,
        autoplay:true,
        autoplaySpeed:1000,
        autoplayTimeout:5000,
        autoplayHoverPause:true
    });

    //owl carousel - top rated restaurants
    $('#top-rated .owl-carousel').owlCarousel({
        nav: true,
        dots: false,
        margin: 10,
        loop: ( $('#top-rated .owl-carousel .items').length > 5 ),
        responsive: {   //how many items to display based on viewport size
            0: {
                items: 1
            },
            600: {
                items: 3
            },
            1000: {
                items: 5
            },
            2000: {
                items: 7
            }
        }
    });

    //owl carousel - on promotion restaurants
    $('#on-promotion .owl-carousel').owlCarousel({
        nav: true,
        dots: false,
        margin: 10,
        loop: ( $('#on-promotion .owl-carousel .items').length > 5 ),
        responsive: {   //how many items to display based on viewport size
            0: {
                items: 1
            },
            600: {
                items: 3
            },
            1000: {
                items: 5
            },
            2000: {
                items: 7
            }
        }
    });

    //owl carousel - most favoured restaurants
    $('#most-favoured .owl-carousel').owlCarousel({
        nav: true,
        dots: false,
        margin: 10,
        loop: ( $('#most-favoured .owl-carousel .items').length > 5 ),
        responsive: {   //how many items to display based on viewport size
            0: {
                items: 1
            },
            600: {
                items: 3
            },
            1000: {
                items: 5
            },
            2000: {
                items: 7
            }
        }
    });

    //owl carousel - newly joined restaurants
    $('#new-restaurants .owl-carousel').owlCarousel({
        nav: true,
        dots: false,
        margin: 10,
        loop: ( $('#new-restaurants .owl-carousel .items').length > 5 ),
        responsive: {   //how many items to display based on viewport size
            0: {
                items: 1
            },
            600: {
                items: 3
            },
            1000: {
                items: 5
            },
            2000: {
                items: 7
            }
        }
    });
})

