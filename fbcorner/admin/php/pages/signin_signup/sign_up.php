<?php 

    session_start();

    if (!isset($_SESSION['login_rest_id']) && !isset($_SESSION['login_user_id'])) {

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
</head>
<body>
    <header>
        <!-- navigation bar -->   
        <?php include_once '../../common/navigation_bar.php'; ?>
    </header>

    <div class="d-flex justify-content-center align-items-center px-5 py-5 background-img" style="background-image: url('../../../assest/rest-background.png');">
        <div class="shadow px-5 py-5 color-2 input">
            <form action="../../helpers/sign_up_success.php" method="POST" id="signup-form" enctype="multipart/form-data">
                <h2 class="font-bernard text-center mt-3 mb-2">Join F&amp;B Corner !</h2>
                
                <!-- success message -->
                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-0 mb-1 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?></div>
                <?php } else { ?>
                    <div class="alert form-message mt-0 mb-1" role="alert"></div>
                <?php } ?>

                <!-- error message -->
                <?php if (isset($_SESSION['error'])) { 
                    $error = $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
                    <div class="alert form-message mt-0 mb-1 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;<?=$error?></div>
                <?php } ?>

                <div class="mb-3">
                    <label class="form-label required">Restaurant Name <span class="required-star">*</span></label>
                    <input type="text" name="rest-name" class="form-control">
                    <p class="d-block text-danger" id="rest-name"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Owner Name <span class="required-star">*</span></label>
                    <input type="text" name="owner-name" class="form-control">
                    <p class="d-block text-danger" id="owner-name"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Owner Contact Number <span class="required-star">*</span></label>
                    <input type="text" name="tel" class="form-control" placeholder="E.g. 0123456789 / 012-3456789">
                    <p class="d-block text-danger" id="contact-num"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Restaurant Email <span class="required-star">*</span></label>
                    <input type="text" name="email" class="form-control">
                    <p class="d-block text-danger" id="email"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Address <span class="required-star">*</span></label>
                    <textarea name="address" rows="5" class="d-block width-100p form-control"></textarea>
                    <small><div class="char-counter" style="color: grey;">400 characters</div></small>
                    <p class="d-block text-danger" id="address"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="profile_pic" class="form-control">
                    <p class="d-block text-danger" id="p_p"></p>
                </div>

                <div class="mb-3">
                    <label class="form-label">Documents <span class="required-star">*</span></label>
                    <input type="file" name="documents[]" class="form-control" multiple>
                    <p class="d-block text-danger" id="docs"></p>
                </div>

                <button type="submit" class="btn btn-dark width-100p mt-3 mb-2"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" role="status" aria-hidden="true"></span>Sign Up</button>
            </form>
        </div>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <script>
        $(document).ready(function() {
            $('textarea').on('input', function(){
                var current_char = $(this).val().length;
                var limit = 400;
                if (current_char > limit) {
                    $(this).val($(this).val().substring(0, limit));
                } else {
                    $(this).parent().find('.char-counter').text((limit - current_char).toString() + ' characters');
                }
            });

            $("#signup-form").submit(function(event){
                event.preventDefault();

                $('.form-message').removeClass('alert-danger');
                $('.form-message').removeClass('alert-success');
                $('.form-message').text('');
                $('#signup-form').removeClass('hasError');
                $('.text-danger').each(function() {
                    $(this).text('');
                });
                $('input').each(function() {
                    $(this).removeClass('red-box-shadow is-invalid');
                });
                $('textarea').each(function() {
                    $(this).removeClass('red-box-shadow is-invalid');
                });

                //process image name
                var img_name = '';
                var img_info = $("input[name='profile_pic']").get(0).files;
                if (img_info.length != 0)
                    img_name = img_info[0]['name'];

                //process uploaded file names
                var documents_names = [];
                var documents_info = ($("input[name='documents[]']").get(0).files);
                if (documents_info.length != 0){
                    for (var i = 0; i < documents_info.length; i++){
                        documents_names.push(documents_info[i]['name']);
                    }
                }

                $.when($(".form-message").load("../../helpers/sign_up.php", {
                    rest_name: $("input[name='rest-name']").val(),
                    owner_name: $("input[name='owner-name']").val(),
                    contact_num: $("input[name='tel']").val(),
                    email: $("input[name='email']").val(),
                    address: $("textarea[name='address']").val(),
                    p_p: img_name,
                    docs: documents_names,
                    submit: $("button[type=submit]").val()
                }))
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