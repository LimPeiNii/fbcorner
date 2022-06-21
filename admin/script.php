<script>
    $(document).ready(function() {
        $('#btn-sign-out').click(function() {
            localStorage.setItem('logout-event', 'logout' + Math.random());
            
            $.ajax({
                url: '../../helpers/sign_out.php',
                success: function(){
                    window.location.href = "../signin_signup/sign_in.php";
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