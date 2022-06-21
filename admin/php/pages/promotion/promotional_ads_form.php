<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/promotion.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'promotion/promotional_ads', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['promotional_ads_id'])) {
                $promotional_ad = getPromotionalAd($connection, $_GET['promotional_ads_id']);
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotional Ads &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            .photo-img-wrapper:hover .overlay-text{
                opacity: 1 !important;
            }

            .photo-img-wrapper{
                cursor: pointer;
            }
        </style>
    <?php } ?>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar_2.php'; ?>

    <!-- side bar -->
    <div class="side-bar-body">
        <?php include_once '../../common/side_bar.php'; ?>

        <!-- content -->
        <div class="content p-4">
            <?php if ((int)$has_permission['has_permission']){ ?>
                <a href="promotional_ads.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="promotional-ads-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($promotional_ad)) { ?>
                        Edit Promotional Ads
                    <?php } else { ?>
                        Add Promotional Ads
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($promotional_ad)) { ?>
                        <form action="../../helpers/promotion.php?promotional_ads_id=<?=$promotional_ad['promotional_ads_id']?>" class="m-5" id="promotional-ads-form" method="POST">
                    <?php } else { ?> 
                        <form action="../../helpers/promotion.php" class="m-5" id="promotional-ads-form" method="POST">
                    <?php } ?>
                        <div class="mb-4">
                            <label for="title" class="form-label fw-bold">Title <span class="required-star">*</span></label>
                            <input class="form-control" autocomplete="off" name="title" id="title"<?=isset($promotional_ad) ? 'value="' . $promotional_ad['title'] . '"' : '' ?>>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="content" class="form-label fw-bold">Content <span class="required-star">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="15"><?=isset($promotional_ad) ? $promotional_ad['content'] : '' ?></textarea>
                        </div>
                        <div class="mb-4">
                            <div style="height: fit-content;" class="d-flex mb-2">
                                <label class="form-label fw-bold align-self-center m-0" for="photos">Photos</label>
                                <button type="button" id="add-photos-btn" onclick="$('#photos').click()" class="btn btn-outline-primary btn-sm ms-2"><small>Click to add</small></button>
                                <button type="button" id="reset-photos-btn" class="btn btn-outline-primary btn-sm ms-2"><small>Click to reset</small></button>
                                <input type="file" id="photos" class="d-none" accept="image/*" multiple>
                            </div>
                            <div id="photo-content" class="d-flex flex-wrap">
                                <?php if (isset($promotional_ad)) { ?>
                                    <?php $photos = json_decode($promotional_ad['photos'], true); ?>
                                    <?php foreach ($photos as $photo) { ?>
                                        <span class="h-100 d-inline-block position-relative photo-img-wrapper old-img me-3 mb-2 mt-1" id="img-<?=$photo?>"><img class="item d-inline-block photo-preview" src="../../../uploads/promo_ads_photo/<?=$photo?>"><span class="position-absolute overlay-text d-flex start-0 end-0 bottom-0 top-0 bg-dark text-white" style="opacity: 0; font-size: 0.8rem;"><span class="align-self-center text-center w-100">Remove</span></span></span>                            
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1"<?=isset($promotional_ad) && $promotional_ad['status'] == 1 ? ' selected="selected"' : '' ?>>Available</option>
                                <option value="0"<?=isset($promotional_ad) && $promotional_ad['status'] == 0 ? ' selected="selected"' : '' ?>>Unavailable</option>
                            </select>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="text-center mt-3">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script>
            function close_message() {
                $('.form-message').remove();
                $('#promotional-ads-form').removeClass('mt-4');
            }

            $(document).ready(function() {
                temp_uploaded_photos = [];

                $('#photos').change(function() {
                    $('.form-message').remove();

                    var upload_img = this.files;
                    var allowed_ext = ["jpg", "jpeg", "png"]; 
                    var error = false;
                    for (var i=0; i<upload_img.length; i++){
                        var img_name = upload_img[i].name;
                        if ($.inArray(img_name.substring(img_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
                            $('<div class="alert form-message mb-3 mt-4 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;You can only select files with type "jpg", "jpeg", "png"!</div>').insertBefore('#promotional-ads-form');
                            $('#promotional-ads-form').addClass('mt-4');
                            $('html, body').animate({ scrollTop: 0 }, 0);
                            error = true;
                            $('#upload-imgs').val('');
                            break;
                        }
                    }

                    if (!error) {
                        for (var i=0; i<upload_img.length; i++){
                            var uploaded_file_names = temp_uploaded_photos.map(item => item['name']);

                            if ($.inArray(upload_img[i].name, uploaded_file_names) == -1){
                                temp_uploaded_photos.push(upload_img[i]);
                                let reader = new FileReader();
                                reader.onload = function(event){
                                    $('#photo-content').append('<span class="h-100 d-inline-block position-relative photo-img-wrapper new-img me-3 mb-2 mt-1"><img class="item d-inline-block photo-preview" src="' + event.target.result + '"><span class="position-absolute overlay-text d-flex start-0 end-0 bottom-0 top-0 bg-dark text-white" style="opacity: 0; font-size: 0.8rem;"><span class="align-self-center text-center w-100">Remove</span></span></span>');
                                }
                                reader.readAsDataURL(upload_img[i]);
                            }
                        }
                    }
                });

                //remove image
                $(document).on('click', '.photo-img-wrapper', function() {
                    if ($(this).hasClass('new-img')){
                        var index = $('.photo-img-wrapper').index($(this));
                        temp_uploaded_photos.splice(index, 1);
                    }
                    $(this).remove();
                });

                $('#reset-photos-btn').click(function() {
                    $('.photo-img-wrapper').remove();
                    temp_uploaded_photos = [];
                });

                $("#promotional-ads-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('input, textarea').removeClass('is-invalid red-box-shadow');
                    $('.text-danger').text('');

                    var data = new FormData(); 
                    var count = 0;
                    $.each(temp_uploaded_photos, function(i, img){
                        data.append(count, img);
                        count++;
                    });

                    <?php if (isset($_GET['promotional_ads_id'])) { ?>
                        data.append('promotional_ads_id', '<?=$_GET['promotional_ads_id']?>');
                        var current_old_imgs = {};
                        var counter = 0;
                        $('.old-img').each(function() {
                            current_old_imgs[counter] = $(this).attr('id').substring($(this).attr('id').indexOf('-') + 1)
                            counter++;
                        });
                        <?php if (empty($promotional_ad['photos'])) { ?>
                            data.append('old_imgs', '{}')
                        <?php } else { ?>
                            data.append('old_imgs', '<?=$promotional_ad['photos']?>')
                        <?php } ?>
                        data.append('current_old_imgs', JSON.stringify(current_old_imgs));
                    <?php } ?>
                    data.append('title', $('#title').val());
                    data.append('content', $('#content').val());
                    data.append('status', $('#status').val());
                    data.append('submit_form_ads', true);

                    $.ajax({
                        url: '../../helpers/promotion.php',
                        data: data,
                        processData: false,
                        contentType: false,
                        method: 'post',
                        success: function(output) {
                            var json = $.parseJSON(output);

                            if (json['error']){
                                if (json['error']['upload-imgs']){
                                    $('<div class="alert form-message mb-3 mt-4 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + json['error']['upload-imgs'] + '</div>').insertBefore('#promotional-ads-form');
                                } else {
                                    $('<div class="alert form-message mb-3 mt-4 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#promotional-ads-form');
                                    $.each(json['error'], function(key, value) {
                                        $('#' + key).addClass('is-invalid red-box-shadow');
                                        $('#' + key).parent().find('p').text(value);
                                    });
                                }
                                $('#promotional-ads-form').addClass('mt-4');
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                window.location.href = 'promotional_ads.php';
                            }
                        }
                    });
                });
            });
        </script>
    <?php } ?>
</body>
</html>

<?php

    } else {
        header("Location: ../signin_signup/sign_in.php");
        exit;
    }

?>