function close_message_signin() {
    $('#sign-in-form .form-message').remove();
    $('#sign-in-form h2').removeClass('mb-2');
    $('#sign-in-form h2').addClass('mb-4');
}

$(document).ready(function(){
    //sign in trigger
    $('#sign-in-button').click(function() {
        $('main, header, footer').addClass('blur');
        $('#sign-in-popup').modal('show');
    });

    $('#sign-in-close').click(function() {
        $('main, header, footer').removeClass('blur');
    });

    //sign up trigger
    $('#sign-up-button').click(function() {
        $('main, header, footer').addClass('blur');
        $('#sign-up-popup').modal('show');
    });

    $('#sign-up-close').click(function() {
        $('main, header, footer').removeClass('blur');
    });
    
    $('.close-button').click(function() {
        $('form input').each(function(){
          $(this).val('');
          $(this).removeClass('red-box-shadow is-invalid');
        });

        $('.form-message').remove();
        $('.text-danger').text('');
    });
          
    $("#sign-up-form").submit(function(event){
        event.preventDefault();

        //process image name
        var img_name = '';
        var img_info = $("input[name='profile_pic']").get(0).files;
        if (img_info.length != 0)
            img_name = img_info[0]['name'];

        $('#sign-up-form .form-message').remove();
        $('#sign-up-form .text-danger').text('');
        $('input').each(function() {
            $(this).removeClass('red-box-shadow is-invalid');
        });

        $.ajax({ 
            url: '../../helpers/sign_up.php',
            data: {
                firstname: $('#firstname').val(),
                lastname: $('#lastname').val(),
                email: $('#email').val(),
                tel: $('#tel').val(),
                password: $('#password').val(),
                c_password: $('#c_password').val(),
                profile_pic: img_name,
                submit_check_sign_up_store: $("button[type=submit]").val()
            },
            type: 'post',
            success: function(output){
                console.log(output)
                var json = $.parseJSON(output);
                
                if (json['error']){
                    $('<div class="alert form-message mt-0 mb-2 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#sign-up-form .nested-wrap div:first-child');
                    $.each(json['error'], function(key, value) {
                        $('#' + key).addClass('red-box-shadow is-invalid');
                        $('#' + key).parent().find('.text-danger').text(value);
                    });
                    $('#sign-up-popup .modal-body').animate({ scrollTop: 0 }, 0);
                } else {
                    $('#sign-up-form').unbind().submit();
                }
            }
        });
    });

    $("#sign-in-form").submit(function(event){
        event.preventDefault();

        $('#sign-in-form .form-message').remove();
        $('#sign-in-form h2').removeClass('mb-2');
        $('#sign-in-form h2').addClass('mb-4');
        $('#sign-in-form .text-danger').text('');
        $('input').each(function() {
            $(this).removeClass('red-box-shadow is-invalid');
        });

        $.ajax({ 
            url: '../../helpers/sign_in.php',
            data: {
                email: $('#email_signin').val(),
                password: $('#password_signin').val(),
                submit_check_sign_in_store: $("button[type=submit]").val()
            },
            type: 'post',
            success: function(output){
                var json = $.parseJSON(output);
                
                if (json['error']){
                    $('<div class="alert form-message mt-0 mb-2 alert-danger" role="alert"><button type="button" onclick="close_message_signin();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#sign-in-form .nested-wrap');
                    $.each(json['error'], function(key, value) {
                        if (key != 'incorrect'){
                            $('#' + key + '_signin').addClass('red-box-shadow is-invalid');
                            $('#' + key + '_signin').parent().find('.text-danger').text(value);
                        } else {
                            $('#sign-in-form .form-message').remove();
                            $('<div class="alert form-message mt-0 mb-2 alert-danger" role="alert"><button type="button" onclick="close_message_signin();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + value + '</div>').insertBefore('#sign-in-form .nested-wrap');
                        }
                    });
                } else {
                    window.location.reload();
                }
            }
        });
    });
});