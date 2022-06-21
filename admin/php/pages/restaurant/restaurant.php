<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/restaurant.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'restaurant/restaurant', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'restaurant/restaurant', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            $restaurant = getRestaurant($connection, $_SESSION['login_rest_id']);
            $dining_styles = getDiningStyles($connection);
            $sharing_post_result = getSharingPostSettings($connection, $_SESSION['login_rest_id']);
            if (!empty($sharing_post_result)){
                $sharing_post_data = json_decode($sharing_post_result['value'], true);
            }

            $cities = array(
                'Kuala Lumpur', 
                'Selangor', 
                'Johor', 
                'Kedah', 
                'Kelantan', 
                'Melacca', 
                'Negeri Sembilan', 
                'Pahang', 
                'Penang', 
                'Perak', 
                'Perlis', 
                'Terengganu', 
                'Sabah', 
                'Sarawak'
            );
        }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            .nav-tabs .nav-link.active{
                border-color: black black #fff;;
            }

            .nav-tabs{
                border-bottom: 1px solid black;
            }

            .tab-content{
                border-left: 1px solid black;
                border-right: 1px solid black;
                border-bottom: 1px solid black;
                min-height: 50vh;
            }

            .content{
                min-height: 90vh;
            }

            .opening-hour-time, .opening-hour-day{
                display: inline-block;
            }

            .opening-hour-day select, .opening-hour-time input{
                width: 145px;
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
                <button type="submit" form="restaurant-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Restaurant
                </h3>

                <ul class="nav nav-tabs mt-3" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="owner-tab" data-bs-toggle="tab" data-bs-target="#owner" type="button" role="tab" aria-controls="owner" aria-selected="false">Owner</button>
                    </li>
                </ul>

                <form action="../../helpers/restaurant.php" method="post" id="restaurant-form" enctype="multipart/form-data">
                    <input type="text" style="display: none;" name="submit_form_restaurant">
                    <div class="tab-content p-5" style="overflow-x: auto" id="myTabContent">

                        <?php if (isset($_SESSION['success'])) { 
                            $success = $_SESSION['success'];
                            unset($_SESSION['success']);
                        ?>
                            <div class="alert form-message alert-success" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?></div>
                        <?php } ?>

                        <?php if (isset($_SESSION['error'])) { 
                            $error = $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                            <div class="alert form-message alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;<?=$error?></div>
                        <?php } ?>

                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <div class="m-0 w-100 profile-tab-content">
                                <div class="mb-4">
                                    <div class="d-flex mb-2">
                                        <label class="form-label fw-bold align-self-center mb-0" for="profile_pic">Profile Picture</label>
                                        <button type="button" class="btn btn-outline-primary img-preview-btn btn-sm ms-2"><small>Click to edit</small></button>
                                        <button type="button" class="btn btn-outline-primary remove-img btn-sm ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Set to default profile picture"><small>Click to remove</small></button>
                                        <input type="file" id="profile_pic" class="d-none picture-upload" name="profile_pic" accept="image/*">
                                    </div>
                                    <div class="img-content">
                                        <input type="text" name="reset_profile" class="d-none">
                                        <img id="img-preview" src="../../../uploads/profile_pic/<?=$restaurant['rest_profile']?>">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex mb-2">
                                        <label class="form-label fw-bold align-self-center mb-0" for="cover_photo">Cover Photo</label>
                                        <button type="button" class="btn btn-outline-primary img-preview-btn btn-sm ms-2"><small>Click to edit</small></button>
                                        <button type="button" class="btn btn-outline-primary remove-img btn-sm ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Set to default cover photo"><small>Click to remove</small></button>
                                        <input type="file" id="cover_photo" class="d-none picture-upload" name="cover_photo" accept="image/*">
                                    </div>
                                    <div class="img-content">
                                        <input type="text" name="reset_cover" class="d-none">
                                        <img id="cover-photo-preview" src="../../../uploads/cover_photo/<?=$restaurant['cover_photo']?>">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="rest_name">Restaurant Name <span class="required-star">*</span></label></div>
                                    <div>
                                        <input type="text" id="rest_name" name="rest_name" class="form-control" value="<?=$restaurant['rest_name']?>">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="description">Description</label></div>
                                    <div>
                                        <textarea id="description" name="description" class="form-control" style="resize: vertical;"><?=$restaurant['description']?></textarea>
                                        <small><div class="char-counter" style="color: grey;"></div></small>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="contact_num">Contact Number</label></div>
                                    <div>
                                        <input type="text" id="contact_num" name="contact_num" placeholder="E.g. 03-12345678" class="form-control" value="<?=$restaurant['contact_num']?>">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="email">Email <span class="required-star">*</span></label></div>
                                    <div>
                                        <input type="text" id="email" name="email" class="form-control" value="<?=$restaurant['email']?>">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="address">Address <span class="required-star">*</span></label></div>
                                    <div>
                                        <textarea id="address" name="address" class="form-control" style="resize: vertical;"><?=$restaurant['address']?></textarea>
                                        <small><div class="char-counter" style="color: grey;"></div></small>
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="city">City</label></div>
                                    <div>
                                        <select name="city" id="city" class="form-select">
                                            <?php if (empty($restaurant['city'])) { ?>
                                                <option value="*" selected>-- Please Select --</option>
                                                <?php foreach ($cities as $city) { ?>
                                                    <option value="<?=$city?>"><?=$city?></option>     
                                                <?php } ?>
                                            <?php } else { ?>
                                                <option value="*">-- Please Select --</option>
                                                <?php foreach ($cities as $city) { ?>
                                                    <?php if ($restaurant['city'] == $city) { ?>
                                                        <option value="<?=$city?>" selected><?=$city?></option>  
                                                    <?php } else { ?>
                                                        <option value="<?=$city?>"><?=$city?></option>  
                                                    <?php } ?>   
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4" style="margin-bottom: 40px !important;">
                                    <div class="d-flex mb-2">
                                        <label class="form-label fw-bold align-self-center text-nowrap mb-0">Opening Hours <span class="required-star">*</span></label>
                                        <button type="button" class="btn btn-outline-primary add-opening-hours btn-sm ms-2"><small>Click to add</small></button>
                                    </div>
                                    <div id="opening-hour-content">
                                        <input type="text" id="opening-hours-counter" name="opening_hours_counter" class="d-none">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="d-flex">
                                        <label class="form-label fw-bold align-self-center mb-0" for="search_keyword">Search Keyword (Tags) <span data-bs-toggle="tooltip" data-bs-placement="top" title="Add tags to increase your restaurant's chances of appearing in search results"><i class="fas fa-question-circle"></i></span></label>
                                        <button type="button" class="btn btn-outline-primary add-search-keyword btn-sm ms-2"><small>Click to add</small></button>
                                    </div>
                                    <div id="tags-input">
                                        <?php $tags = json_decode($restaurant['tags'], true); ?>
                                        <?php if (count($tags) == 0) { ?>
                                            <input type="text" id="search_keyword" placeholder="tag 1" class="form-control" name="search_keyword[]">
                                        <?php } else { ?>
                                            <?php foreach ($tags as $key => $tag) { ?>
                                                <input type="text" id="search_keyword_<?=$key+1?>" placeholder="tag <?=$key+1?>" class="form-control mt-2" name="search_keyword[]" value="<?=$tag?>">
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="dining_style">Dining Style <span class="required-star">*</span></label></div>
                                    <div>
                                        <select name="dining_style[]" id="dining_style" class="form-select">
                                            <?php if ($restaurant['dining_style_id'] == 0) { ?>
                                                <option value="0" selected>-- Please Select --</option>
                                                <?php foreach ($dining_styles as $dining_style) { ?>
                                                    <option value="<?=$dining_style['dining_style_id']?>"><?=$dining_style['dining_style']?></option>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <option value="0">-- Please Select --</option>
                                                <?php foreach ($dining_styles as $dining_style) { ?>
                                                    <?php if ($restaurant['dining_style_id'] == $dining_style['dining_style_id']) { ?>
                                                        <option value="<?=$dining_style['dining_style_id']?>" selected><?=$dining_style['dining_style']?></option>
                                                    <?php } else { ?>
                                                        <option value="<?=$dining_style['dining_style_id']?>"><?=$dining_style['dining_style']?></option>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <option value="others">Others</option>
                                        </select>
                                        <input type="text" id="dining_style_others" placeholder="Dining Style" class="form-control mt-2 d-none" name="dining_style[]">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex mb-2">
                                        <label class="form-label fw-bold align-self-center mb-0" for="photos">Photos</label>
                                        <button type="button" id="photos-img-preview-btn" class="btn btn-outline-primary btn-sm ms-2"><small>Click to add</small></button>
                                        <button type="button" id="reset-photos-btn" class="btn btn-outline-primary btn-sm ms-2"><small>Click to reset</small></button>
                                        <input type="file" id="photos" name="photos[]" class="d-none" accept="image/*" multiple>
                                    </div>
                                    <div id="photo-content">
                                        <?php
                                            $photos = json_decode($restaurant['photos'], true);
                                            $photo_names = '';
                                            foreach ($photos as $photo){
                                                $photo_names .= $photo;
                                            }
                                        ?>
                                        <input type="text" id="current-photos" name="photo_name" class="d-none" value="<?=$photo_names?>">
                                        <?php foreach ($photos as $photo) { ?>
                                            <img src="../../../uploads/photos/<?=$photo?>" class="photo-preview">
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex mb-2">
                                        <label class="form-label fw-bold align-self-center mb-0" for="sharing_post_img">Sharing Post <span data-bs-toggle="tooltip" data-bs-placement="top" title="Set a title and image for customer sharing posts on social medias."><i class="fas fa-question-circle"></i></span></label>
                                        <button type="button" class="btn btn-outline-primary img-preview-btn btn-sm ms-2"><small>Add / Edit Image</small></button>
                                        <button type="button" class="btn btn-outline-primary remove-img btn-sm ms-2"><small>Remove Image</small></button>
                                        <input type="file" id="sharing_post_img" class="d-none picture-upload" name="sharing_post_img" accept="image/*">
                                    </div>
                                    <div class="form-floating"><input type="text" id="sharing_post_title" name="sharing_post_title" placeholder="Title" class="form-control mt-1"<?=isset($sharing_post_data) && isset($sharing_post_data['title']) ? ' value="' . $sharing_post_data['title'] . '"'  : '' ?>><label for="sharing_post_title">Title</label></div>
                                    <div class="img-content">
                                        <input type="text" name="reset_sharing_post_img" class="d-none">
                                        <?php if (isset($sharing_post_data) && isset($sharing_post_data['image'])) { ?>
                                            <img id="sharing_post_img-preview" class="photo-preview mt-2" src="../../../uploads/sharing_post_img/<?=$sharing_post_data['image']?>">
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="notes">Additional Notes</label></div>
                                    <div>
                                        <textarea id="notes" name="notes" class="form-control" style="resize: vertical;"><?=$restaurant['additional_notes']?></textarea>
                                        <small><div class="char-counter" style="color: grey;"></div></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="owner" role="tabpanel" aria-labelledby="owner-tab">
                            <div class="m-0 w-100 owner-tab-content">
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="owner_name">Owner Name <span class="required-star">*</span></label></div>
                                    <div>
                                        <input type="text" id="owner_name" name="owner_name" class="form-control" value="<?=$restaurant['owner_name']?>">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="owner_contact_num">Owner Contact Number <span class="required-star">*</span></label></div>
                                    <div>
                                        <input type="text" id="owner_contact_num" name="owner_contact_num" placeholder="E.g. 0123456789 / 012-3456789" class="form-control" value="<?=$restaurant['owner_contact_num']?>">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div><label class="form-label fw-bold" for="owner_email">Owner Email</label></div>
                                    <div>
                                        <input type="text" id="owner_email" name="owner_email" class="form-control" value="<?=$restaurant['owner_email']?>">
                                        <p class="d-block text-danger"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
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
            }

            function close_openingHours(current_this, number) {
                $(current_this).next().remove();
                $(current_this).remove();
                var opening_hours_counter = $('#opening-hours-counter').val();
                $('#opening-hours-counter').val($('#opening-hours-counter').val().replace(number+',', ''));
            }

            function openingHourInputs(counter) {
                var html = '';
                html += '<button type="button" onclick="close_openingHours(this, '+ counter.toString() +');" class="btn-close btn float-end" aria-label="Close"></button>';
                html += '<div class="row row-cols-1 row-cols-md-4 mb-4">';
                html +=     '<div class="opening-hour-day d-flex flex-column">';
                html +=         '<div>';
                html +=         '<label class="form-label me-2 align-self-center mb-0" for="from-' + counter + '">From <span class="required-star">*</span></label>';
                html +=         '<select name="opening-hour-' + counter + '[0]" id="from-' + counter + '" class="form-select d-inline from me-2">';
                html +=             '<option value="0">-- Select --</option>';
                html +=             '<option value="1">Monday</option>';
                html +=             '<option value="2">Tuesday</option>';
                html +=             '<option value="3">Wednesday</option>';
                html +=             '<option value="4">Thursday</option>';
                html +=             '<option value="5">Friday</option>';
                html +=             '<option value="6">Saturday</option>';
                html +=             '<option value="7">Sunday</option>';
                html +=         '</select>';
                html +=         '</div>';
                html +=         '<div>';
                html +=         '<label style="margin-right: 2.6rem !important" class="form-label align-self-center mb-0" for="to-' + counter + '">To </label>';
                html +=         '<select name="opening-hour-' + counter + '[1]" id="to-' + counter + '" class="form-select d-inline to">';
                html +=             '<option value="0">-- Select --</option>';
                html +=             '<option value="1">Monday</option>';
                html +=             '<option value="2">Tuesday</option>';
                html +=             '<option value="3">Wednesday</option>';
                html +=             '<option value="4">Thursday</option>';
                html +=             '<option value="5">Friday</option>';
                html +=             '<option value="6">Saturday</option>';
                html +=             '<option value="7">Sunday</option>';
                html +=         '</select>';
                html +=         '</div>';
                html +=     '</div>';
                html +=     '<div class="opening-hour-time d-flex flex-column">';
                html +=         '<div>';
                html +=         '<label class="form-label align-self-center mb-0 me-2" for="opening_hours_open_' + counter + '">Open <span class="required-star">*</span></label>';
                html +=         '<input type="time" name="opening-hour-' + counter + '[2]" id="opening_hours_open_' + counter + '" class="form-control d-inline">';
                html +=         '</div>';
                html +=         '<div>';
                html +=         '<label style="margin-right: 0.4rem !important" class="form-label align-self-center mb-0" for="opening_hours_close_' + counter + '">Close <span class="required-star">*</span></label>';
                html +=         '<input type="time" name="opening-hour-' + counter + '[3]" id="opening_hours_close_' + counter + '" class="form-control d-inline">';
                html +=         '</div>';
                html +=     '</div>';
                html += '</div>';

                $(html).insertBefore('#opening-hour-content .text-danger')
            }

            var opening_hours_json = $.parseJSON('<?=$restaurant['opening_hours']?>');
            if (opening_hours_json.length == 0){
                openingHourInputs(1);
                $('#opening-hours-counter').val('1,');
            } else {
                for (i in opening_hours_json){
                    i = parseInt(i);
                    openingHourInputs(i+1);
                    $('#from-' + (i+1).toString() + ' option[value='+ opening_hours_json[i]["from"] +']').prop('selected', true);
                    $('#to-' + (i+1).toString() + ' option[value='+ opening_hours_json[i]["to"] +']').prop('selected', true);
                    $('#opening_hours_open_' + (i+1).toString()).val(opening_hours_json[i]["open"]);
                    $('#opening_hours_close_' + (i+1).toString()).val(opening_hours_json[i]["close"]);
                    $('#opening-hours-counter').val($('#opening-hours-counter').val() + (i+1).toString() + ",");
                }
            }


            $(document).ready(function() {
                $('.img-preview-btn').click(function() {
                    $(this).parent().find('input').trigger('click');
                });

                $('.picture-upload').change(function() {
                    $('.form-message').remove();

                    var upload_img = this.files[0];
                    var img_name = upload_img.name
                    var allowed_ext = ["jpg", "jpeg", "png"]; 
                    var current_element = $(this);

                    if ($.inArray(img_name.substring(img_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
                        $('<div class="alert form-message mb-3 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;You can only select files with type "jpg", "jpeg", "png"!</div>').insertBefore('.profile-tab-content');
                        current_element.val('');
                    } else if (upload_img){
                        let reader = new FileReader();
                        reader.onload = function(event){
                            if (current_element.parent().parent().find('.img-content').children().length > 1)
                                current_element.parent().parent().find('img').attr('src', event.target.result);
                            else if (current_element.parent().find('input').attr('id') == 'cover_photo')
                                current_element.parent().parent().find('.img-content').append('<img class="me-2 mb-2" id="cover-photo-preview" src="' + event.target.result + '">');
                            else if (current_element.parent().find('input').attr('id') == 'profile_pic')
                                current_element.parent().parent().find('.img-content').append('<img class="me-2 mb-2" id="img-preview" src="' + event.target.result + '">');
                            else if (current_element.parent().find('input').attr('id') == 'sharing_post_img')
                                current_element.parent().parent().find('.img-content').append('<img class="me-2 mb-2 mt-2 photo-preview" id="sharing_post_img-preview" src="' + event.target.result + '">');
                        }
                        reader.readAsDataURL(upload_img);
                    }                
                });

                $('#photos-img-preview-btn').click(function() {
                    $(this).parent().find('input').trigger('click');
                });

                $('#photos').change(function() {
                    $('.form-message').remove();
                    $('#photo-content img.newly-added').each(function() {
                        $(this).remove();
                    });

                    var upload_img = this.files;
                    var allowed_ext = ["jpg", "jpeg", "png"]; 
                    var error = false;
                    for (var i=0; i<upload_img.length; i++){
                        var img_name = upload_img[i].name;
                        if ($.inArray(img_name.substring(img_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
                            $('<div class="alert form-message mb-3 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;You can only select files with type "jpg", "jpeg", "png"!</div>').insertBefore('.profile-tab-content');
                            $('html, body').animate({ scrollTop: 0 }, 0);
                            error = true;
                            $('#photos').val('');
                            break;
                        }
                    }

                    if (!error) {
                        for (var i=0; i<upload_img.length; i++){
                            let reader = new FileReader();
                            reader.onload = function(event){
                                $('#photo-content').append('<img class="me-2 mb-2 mt-2 newly-added photo-preview" src="' + event.target.result + '">')
                            }
                            reader.readAsDataURL(upload_img[i]);
                        }
                    }
                });

                $('.add-search-keyword').on('click', function () {
                    var addTo = $(this).parent().parent().find('input:last-child');
                    var counter = 0;
                    $('#tags-input input').each(function(){
                        counter++;
                    });
                    $('<input type="text" placeholder="tag ' + (counter+1) + '" class="form-control mt-2" name="search_keyword[]">').insertAfter(addTo);
                });

                $('.add-opening-hours').click(function() {
                    var counter = 1;
                    $('.opening-hour-day').each(function(){
                        counter++;
                    });

                    openingHourInputs(counter);
                    $('#opening-hours-counter').val($('#opening-hours-counter').val() + (counter).toString() + ",");
                });

                $('#dining_style').change(function(){
                    if ($(this).val() == 'others'){
                        $('#dining_style_others').removeClass('d-none');
                    } else {
                        $('#dining_style_others').addClass('d-none');
                    }
                });

                $('textarea').each(function() {
                    var limit;
                    if ($(this).attr("id") == 'address'){
                        limit = 400;
                    } else {
                        limit = 1000;
                    }
                    var current_char = $(this).val().length;
                    $(this).parent().find('.char-counter').text((limit - current_char).toString() + ' characters');                
                });

                $('textarea').on('input', function(){
                    var current_char = $(this).val().length;
                    var limit;
                    if ($(this).attr("id") == 'address'){
                        limit = 400;
                    } else {
                        limit = 1000;
                    }
                    if (current_char > limit) {
                        $(this).val($(this).val().substring(0, limit));
                    }
                    var char_left = limit - current_char;
                    if (char_left < 0)
                        char_left = 0;
                    $(this).parent().find('.char-counter').text(char_left.toString() + ' characters');
                    
                });

                $('.remove-img').click(function() {
                    $(this).parent().find('input').val('');
                    $(this).parent().parent().find('img').remove();
                    $(this).parent().parent().find('.img-content input').val('remove');
                });

                $('#reset-photos-btn').click(function() {
                    $('#current-photos').val('');
                    $(this).parent().find('input').val('');
                    $('#photo-content img').each(function () {
                        $(this).remove();
                    });
                });

                $("#restaurant-form").submit(function(event){
                    event.preventDefault();

                    <?php if (!(int)$has_modify_permission['has_permission']){ ?>
                        $('<div class="alert form-message mb-3 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to modify the restaurant data.</div>').insertBefore('.profile-tab-content');
                        $('<div class="alert form-message mb-3 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to modify the restaurant data.</div>').insertBefore('.owner-tab-content');
                    <?php } else { ?>
                        $('.form-message').remove();
                        $('.text-danger').text('');
                        $('input').each(function() {
                            $(this).removeClass('red-box-shadow is-invalid');
                        });
                        $('select').each(function() {
                            $(this).removeClass('red-box-shadow is-invalid');
                        });
                        $('textarea').each(function() {
                            $(this).removeClass('red-box-shadow is-invalid');
                        });

                        //opening hours data
                        var opening_hours_counter = $('#opening-hours-counter').val().substring(0, $('#opening-hours-counter').val().length-1);
                        opening_hours_counter = opening_hours_counter.split(",");
                        var opening_hours_data = {};
                        for (i in opening_hours_counter){
                            opening_hours_data[opening_hours_counter[i]] = [$('#from-'+opening_hours_counter[i]).val(), $('#to-'+opening_hours_counter[i]).val(), $('#opening_hours_open_'+opening_hours_counter[i]).val(), $('#opening_hours_close_'+opening_hours_counter[i]).val()];
                        }

                        $.ajax({ 
                            url: '../../helpers/restaurant.php',
                            data: {
                                rest_name: $('#rest_name').val(),
                                contact_num: $('#contact_num').val(),
                                email: $('#email').val(),
                                address: $('#address').val(),
                                opening_hours: JSON.stringify(opening_hours_data),
                                dining_style: [$('#dining_style').val(), $('#dining_style_others').val()],
                                owner_name: $('#owner_name').val(),
                                owner_contact_num: $('#owner_contact_num').val(),
                                owner_email: $('#owner_email').val(),
                                submit_check_restaurant: $("button[type=submit]").val()
                            },
                            type: 'post',
                            success: function(output){
                                var json = $.parseJSON(output);
                                
                                if (json['error'] || json['opening_hours']){
                                    $('<div class="alert form-message mb-3 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('.profile-tab-content');
                                    $('<div class="alert form-message mb-3 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('.owner-tab-content');

                                    $.each(json['error'], function(key, value) {
                                        $('#' + key).addClass('red-box-shadow is-invalid');
                                        $('#' + key).parent().find('p').text(value);
                                    });

                                    $.each(json['opening_hours'], function(key, value) {
                                        $('#' + key).addClass('red-box-shadow is-invalid');
                                        $('#opening-hour-content').find('p').text(value);
                                    });

                                    $('html, body').animate({ scrollTop: 0 }, 0);
                                } else {
                                    $('#restaurant-form').unbind().submit();
                                }
                            }
                        });
                    <?php } ?>
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