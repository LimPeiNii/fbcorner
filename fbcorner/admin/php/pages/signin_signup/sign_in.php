<?php 

    session_start();

    if (!isset($_SESSION['login_rest_id']) && !isset($_SESSION['login_user_id'])) {

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>

</head>
<body class="background-img" style="background-image: url('../../../assest/rest-background.png'); ">
    <header>
        <!-- navigation bar -->   
        <?php include_once '../../common/navigation_bar.php'; ?>
    </header>

    <div class="d-flex justify-content-center align-items-center px-5 py-5">
        <div class="shadow px-5 py-5 color-2 input">
            <form action="../../helpers/sign_in.php" method="POST" id="signin-form">
                <h2 class="font-bernard text-center mt-3">Sign In</h2>

                <!-- success message -->
                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-0 mb-1 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?></div>
                <?php } else { ?>
                    <div class="alert form-message mt-0 mb-1" role="alert"></div>
                <?php } ?>

                <div class="mb-3">
                    <input type="text" style="display: none;" name="submit_form">
                    <label class="form-label" for="email">Restaurant Email <span class="required-star">*</span></label>
                    <input type="text" name="email" id="email" class="form-control width-100p">
                    <p class="d-block text-danger"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="username">Username <span class="required-star">*</span></label>
                    <input type="text" name="username" id="username" class="form-control">
                    <p class="d-block text-danger"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password <span class="required-star">*</span></label>
                    <input type="password" name="password" id="password" class="form-control">
                    <p class="d-block text-danger"></p>
                </div>

                <button type="submit" class="btn btn-dark width-100p mt-3 mb-2">Sign In</button>
                <div class="container gx-0 my-1">
                    <div class="row gx-0">
                      <div class="col">
                        <a href="sign_up.php" style="color: blue;">No Account? Join Us!</a>
                      </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
        
    <?php include_once '../../../../script.php'?>

    <script>
        $(document).ready(function() {
            $("#signin-form").submit(function(event){
                event.preventDefault();

                $('h2').removeClass('mb-4');
                $('.form-message').remove();
                $('.text-danger').text('');
                $('input').each(function() {
                    $(this).removeClass('red-box-shadow is-invalid');
                });

                $.ajax({ 
                    url: '../../helpers/sign_in.php',
                    data: {
                        email: $('#email').val(),
                        username: $('#username').val(),
                        password: $('#password').val(),
                        submit_check_sign_in: $("button[type=submit]").val()
                    },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);
                        
                        if (json['error']){
                            $('<div class="alert form-message mt-2 mb-1 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertAfter('h2');
                            $.each(json['error'], function(key, value) {
                                if (key != 'incorrect'){
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    $('#' + key).parent().find('p').text(value);
                                } else {
                                    $('.form-message').remove();
                                    $('<div class="alert form-message mt-2 mb-1 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + value + '</div>').insertAfter('h2');
                                }
                            });
                        } else {
                            window.location.href = "../../helpers/redirect.php";
                        }
                        
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php

    } else {
        header("Location: ../" . $_SESSION['redirect'] . ".php");
        exit;
    }

?>