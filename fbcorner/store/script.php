<script>
    function slideInMsg(type, message){
      var color, icon;
      if (type == 'error'){
        color = 'RGB(220,53,69)';
        icon = 'exclamation';
      } else if (type == 'info'){
        color = 'RGB(13,110,253)';
        icon = 'info';
      } else {
        color = 'RGB(25,135,84)';
        icon = 'check'
      }

      var html = '';

      html += '<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="border-left-color: ' + color + '" data-bs-delay="3000">';
      html +=   '<div class="toast-body d-flex" style="padding-right: 1.3rem">';
      html +=     '<i class="fas fa-' + icon + '-circle align-self-center font-size-28" style="color: ' + color + '"></i>';
      html +=     '<span class="fw-bold align-self-center mx-2">' + message + '</span>';
      html +=     '<button type="button" class="btn-close ms-auto align-self-center" data-bs-dismiss="toast" aria-label="Close"></button>';
      html +=   '</div>';
      html += '</div>';

      $('.toast-container').prepend(html);
      $('.toast-body a').css('color', color);
      var toast = new bootstrap.Toast($('.toast-container .toast:nth-child(1)'));
      toast.show();

      $('.toast-container .toast:nth-child(1)').on('hidden.bs.toast', function () {
        $(this).remove();
      })
    }

    $('.fa-shopping-cart').css('margin-left', '-2px');
    $('.fa-ticket-alt').css('margin-right', '-2px');

    $(document).ready(function() {
        if ('<?=$success_msg?>' != ''){
          slideInMsg('success', '<?=$success_msg?>');
        }

        if ('<?=$error_msg?>' != ''){
          slideInMsg('error', '<?=$error_msg?>');
        }

        if ('<?=$info_msg?>' != ''){
          slideInMsg('info', '<?=$info_msg?>');
        }

        $('#btn-sign-out').click(function() {
            localStorage.setItem('logout-event', 'logout' + Math.random());
            
            $.ajax({
                url: '../../helpers/sign_out.php',
                success: function(){
                    window.location.reload();
                }
            })
        });

        $('#nav-bar-dropdown').click(function() {
          $.ajax({
            url: '../../helpers/order.php',
            data: {get_cart_total: true},
            method: 'post',
            success: function(output){
              $('#my_cart_total').text(output);
            }
          });
        });

        $('#footer-form').submit(function(e) {
            e.preventDefault();

            $('#footer-spinner').removeClass('d-none');
            $('#footer-spinner').parent().prop('disabled', true);

            $.ajax({
                url: '../../helpers/homepage.php',
                data: {
                    'footer-name': $('#footer-name').val(),
                    'footer-phone': $('#footer-phone').val(),
                    'footer-message': $('#footer-message').val(),
                    'send-contact-form': $('#footer-submit').val(),
                },
                method: 'post',
                success: function() {
                    slideInMsg('success', 'Thanks for filling out the form!');
                    $('#footer-spinner').addClass('d-none');
                    $('#footer-spinner').parent().prop('disabled', false);
                    $('#footer-form input, #footer-form textarea').val('');
                }
            })
        });

        setInterval(function() {
          $.ajax({
              url: '../../helpers/message.php',
              data: {update_nav_bar_msg: true},
              method: 'post',
              success: function(output){
                  if (output == 'hide')
                      $('#my_message_noti').addClass('d-none');
                  else if (output == 'show')
                      $('#my_message_noti').removeClass('d-none');
              }
          });
        }, 1000);
    });
</script>