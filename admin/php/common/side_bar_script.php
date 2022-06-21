<script>
    if (localStorage.getItem('side-bar-active') == 'true' && $(window).width() > 481){
        $('.side-bar, .content').addClass('active');
        $('#side-bar-expand i').addClass('fas fa-stream');
    } else {
        $('#side-bar-expand i').addClass('fas fa-bars');
    }

    //side bar highlight current page
    var current_url = window.location.href;
    current_url = current_url.substring(0, current_url.lastIndexOf("/"));
    var current_file = current_url.substring(current_url.lastIndexOf("/") + 1);

    $('.side-bar #'+current_file).css("background","RGB(235, 210, 255,0.66)");
    $('.side-bar #'+current_file).css("border-left", "10px solid #b04fffa8");

    //special sidebar adjust icon size
    $('.side-bar .fa-clipboard, .side-bar .fa-user-tie').css('font-size', '23px');
    $('.side-bar .fa-user-tie, .side-bar .fa-calendar-alt').css('font-size', '21px');
    $('.side-bar .fa-calendar-check, .side-bar .fa-book, .side-bar .fa-user-friends').css('font-size', '20px');
    $('.side-bar .fa-comments').css('font-size', '20px');
    $('.side-bar .fa-star-half-alt').css({
        'font-size': '22px',
        'margin-left': '-3px',
        'margin-right': '3px'
    });
    $('.side-bar .fa-comments, .side-bar .fa-user-friends').css({
        'margin-left': '-2px',
        'margin-right': '2px'
    });

    $(document).ready(function() {
        $('#side-bar-expand').on('click', function() {
            $('.side-bar, .content').toggleClass('active');
            $('.side-bar').addClass('transition');
            $('.content').addClass('transition');
            $('#side-bar-expand i').toggleClass('fas fa-bars fas fa-stream');

            $('.dropdown-container').each(function() {
                $(this).removeClass('active');
                $(this).parent().find('.fa-caret-up').toggleClass('fa-caret-down fa-caret-up');
            });

            if ($('.side-bar').hasClass('active')){
                localStorage.setItem('side-bar-active', 'true');
                
                $('.tool-tip').each(function() {
                    $(this).css('display', 'none');
                });
            } else {
                localStorage.setItem('side-bar-active', 'false');

                $('.tool-tip').each(function() {
                    $(this).css('display', 'block');
                });
            }
        });

        $('.dropdown-btn').on('click', function () {
            var current_container = $(this).parent().parent().children('.dropdown-container');
            $('.dropdown-container').each(function(){
                if (!current_container.is($(this))){
                    $(this).removeClass('active');
                    $(this).parent().find('.fa-caret-up').toggleClass('fa-caret-down fa-caret-up');
                }
            });

            current_container.toggleClass('active');
            if ($(this).find('.fa').hasClass('fa-caret-down'))
                $(this).find('.fa').toggleClass('fa-caret-down fa-caret-up');
            else
                $(this).find('.fa').toggleClass('fa-caret-down fa-caret-up');

            if (current_container.hasClass('active') || $('.side-bar').hasClass('active')){
                $('.tool-tip').each(function() {
                    $(this).css('display', 'none');
                });
            } else {
                $('.tool-tip').each(function() {
                    $(this).css('display', 'block');
                });
            }
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.side-bar, .navbar').length) {
                if ($('.dropdown-container').hasClass('active') && !$('.side-bar').hasClass('active'))
                $('.dropdown-container').removeClass('active');
                $('.tool-tip').each(function() {
                    $(this).css('display', 'block');
                });
            }
        });
    });
</script>