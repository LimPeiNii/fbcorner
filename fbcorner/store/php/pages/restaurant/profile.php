<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/message.php';
    include_once '../../helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/food_menu.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/settings.php';
    include_once '../../../../admin/php/helpers/category.php';
    include_once '../../../../admin/php/helpers/promotion.php';
    include_once '../../../../admin/php/helpers/rating_review.php';
    include_once '../../../../admin/php/helpers/message.php';

    $success_msg = '';
    $error_msg = '';
    $info_msg = '';
    if (isset($_SESSION['success'])){
        $success_msg = $_SESSION['success'];
        unset($_SESSION['success']);
    } elseif (isset($_SESSION['error'])){
        $error_msg = $_SESSION['error'];
        unset($_SESSION['error']);
    } elseif (isset($_SESSION['info'])){
        $info_msg = $_SESSION['info'];
        unset($_SESSION['info']);
    }

    $restaurant_info = getRestaurant($connection, $_GET['visit_rest_id']);
    $food_menu_items_results = getFoodMenuItems($connection, $_GET['visit_rest_id'], ['order_by' => 'item_code', 'asc_desc' => 'ASC']);
    $food_menu_items = [];
    foreach ($food_menu_items_results as $result){
        $food_menu_items[$result['category_id']][] = $result;
    }

    $promo_codes_info = getPromotions($connection, $_GET['visit_rest_id'], ['type_code' => 'promo_code', 'status' => '1']);
    date_default_timezone_set("Asia/Kuala_Lumpur");
    $ratings_reviews_results = getRatingsReviews($connection, $_GET['visit_rest_id'], ['order_by' => 'dine_date', 'asc_desc' => 'DESC']);
    $reviews = [];
    $ratings_group_total = array(
        5 => 0,
        4 => 0,
        3 => 0,
        2 => 0,
        1 => 0
    );
    $total_ratings = 0;
    $total_reviews = 0;
    foreach ($ratings_reviews_results as $result){
        $ratings_group_total[$result['rating']]++;
        if (!empty($result['review'])){
            $total_reviews++;
            $reviews[] = $result;
        }
        $total_ratings += $result['rating'];
    }

    if ($ratings_reviews_results){
        $avg_rating = $total_ratings / count($ratings_reviews_results);
    }

    if (isset($_SESSION['login_cus_id'])){
        $chats = getCustomerChats($connection, $_SESSION['login_cus_id'], $_GET['visit_rest_id']);
        $favourite_restaurant = getFavouriteRestaurants($connection, $_SESSION['login_cus_id'], ['rest_id' => $_GET['visit_rest_id']]);
    }

    $online_login_activities = getRestaurantLoginActivity($connection, $_GET['visit_rest_id'], ['online' => true]);
    $rest_login_activity = getRestaurantLoginActivity($connection, $_GET['visit_rest_id'], ['last_login_activity' => true]);
    if ($rest_login_activity){
        $rest_login_activity = $rest_login_activity[0];
    }

    $sharing_post_settings = getSharingPostSettings($connection, $_GET['visit_rest_id']);
    if (!empty($sharing_post_settings)){
        $sharing_post_settings_data = json_decode($sharing_post_settings['value'], true);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$restaurant_info['rest_name']?> &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>

    <style>
        body{
            height: 100%;
        }
        
        #cover-photo{
            overflow: hidden;
            z-index: 1;
            max-height: 460px;
        }

        #profile-photo{
            position: relative;
            border-radius: 5px;
            width: 30vw;
            height: 30vw;
            max-width: 250px;
            max-height: 250px;
            min-width: 170px;
            min-height: 170px;
            z-index: 999;
        }

        #slide-tag-wrapper{
            border-top-right-radius: 10px; 
            border-bottom-right-radius: 10px; 
            width: fit-content;
            transition: all 0.3s ease;
        }

        #slide-tag-wrapper.unactive{
            margin-left: -137px;
            border-top-right-radius: 0px;
        }

        @media (max-width: 600px){
            #profile-content{
                margin-top: 6rem !important;
            }
        }
        @media (min-width: 600px){
            #profile-content{
                margin-top: 7rem !important;
            }
        }
        @media (min-width: 700px){
            #profile-content{
                margin-top: 8rem !important;
            }
        }
        @media (min-width: 800px){
            #profile-content{
                margin-top: 9rem !important;
            }
        }

        .content-section:before {
            display: block;
            height: 60px;   /* equal to the header height */
            margin-top: -60px;  /* negative margin equal to the header height */
            visibility: hidden;
            content: "";
        }

        #profile-content-navbar .nav-link, #profile-content-navbar .nav-link.active{
            background-color: grey !important;
            color: white;
        }

        #profile-content-navbar .dropdown-menu{
            background-color: black;
        }

        #profile-content-navbar .dropdown-item.active, #profile-content-navbar .dropdown-item:active{
            background-color: grey;
        }

        #profile-content-navbar .dropdown-item:hover{
            color: black;
        }

        #profile-content-navbar .dropdown-item, #profile-content-navbar .dropdown-item:active, #profile-content-navbar .dropdown-item.active:hover{
            color: white;
        }

        #close-photo-btn:hover{
            opacity: 0.9 !important;
        }

        #close-photo-btn i:before{
            color: #fff;
            background-color: #212529;
            border-color: #212529;
            border-radius: 0.25rem;
        }

        .progress{
            margin-bottom: 0.5rem;
        }

        @media (min-width: 900px){
            #rest-name-wrapper{
                margin-left: 2rem !important;
            }
        }
        @media (max-width: 780px){
            #profile-parent{
                margin-left: 3rem !important;
                margin-right: 3rem !important;
            }
        }        
        @media (max-width: 500px){
            #profile-parent{
                flex-direction: column;
                margin-left: 3rem !important;
                margin-right: 3rem !important;
                right: 0;
                left: 0;
            }

            #rest-name-wrapper{
                margin-left: 0 !important;
                margin-top: 0.5rem !important;
            }

            #profile-content{
                margin-top: 7.5rem !important;
            }

            #rest-name-wrapper div span{
                margin-top: 3px !important;
            }
        }

        @media (max-width: 460px){
            .food-menu-items-details{
                flex-direction: column;
            }

            .food-menu-items-details img, .food-menu-items-details img+div{
                margin-left: auto !important;
                margin-right: auto;
                width: 90px;
            }

            .food-menu-items-details img+div>*{
                width: fit-content;
            }
        }

        #chat-btn:hover{
            transform: scale(1.1);
        }

        .chat-room-header-footer{
            background: linear-gradient(90.05deg, #7400AB -9.36%, #C968E5 59.69%, #F9F3FC 133.7%);
            height: 15%;
        }

        .chat-room-header-footer button:not(.btn-close):active, #upload-files-btns button{
            background-color: rgba(36, 36, 36, 0.58);
            border-radius: 10px !important;
        }

        .chat-msgs{
            max-width: 80%;
            color: #444;
            margin-bottom: 0.5rem !important;
            overflow-wrap: break-word;
        }

        .chat-msgs small{
            color: #6f6f6f;
            font-size: 0.7rem;
        }

        @media (max-width: 490px){
            #chat-btn-parent{
                display: none;
            }

            #chat-btn-sm-parent{
                display: block !important;
            }
        }

        .owl-carousel .owl-nav button{
            position: absolute;
            top: 10%;
            outline: none;
        }

        .owl-carousel .owl-nav button.owl-prev{
            left: 0;
        }

        .owl-carousel .owl-nav button.owl-next{
            right: 0;
        }

        .owl-carousel .owl-nav button.owl-prev span,
        .owl-carousel .owl-nav button.owl-next span{
            font-size: 35px;
        }

        .owl-carousel .owl-nav button.owl-prev span{
            margin-left: -60px;
        }

        .owl-carousel .owl-nav button.owl-next span{
            margin-right: -60px;
        }

        .owl-stage{
            max-height: 80px;
            overflow-y: hidden;
        }

        .owl-item{
            margin-right: 10px;
        }

        .item, .chat-img-span{
            cursor: pointer;
            margin: auto;
        }

        .chat-img-span{
            display: flex !important;
        }

        .item:hover .overlay-text, .chat-img-span:hover .overlay-text{
            opacity: 1 !important;
        }

        #share-btns-wrapper div{
            box-shadow: 0 1px 5px rgba(0,0,0,0.4);
            transition: all ease-in-out 0.3s;
            font-size: 35px;
            margin-top: 1rem; 
            margin-left: 3px;
            margin-right: 1rem; 
            cursor: pointer;
            width: fit-content;
            height: fit-content;
        }

        #share-btns-wrapper div:nth-child(1) { transition-delay: 0.1s; }
        #share-btns-wrapper div:nth-child(2) { transition-delay: 0.15s; }
        #share-btns-wrapper div:nth-child(3) { transition-delay: 0.2s; }
        #share-btns-wrapper div:nth-child(4) { transition-delay: 0.25s; }

        #share-btn:not(:checked) ~ #share-btns-wrapper div{
            transform: translateY(-5px) scale(0);
        }

        #share-btn:checked ~ #share-btns-wrapper div{
            transform: translateY(0px) scale(1);
        }

        @media (max-width: 450px) {
            .a_review{
                flex-wrap: wrap !important;
            }
        }

        @media (max-width: 768px) {
            .progress{
                width: 90% !important;
            }
        }
    </style>
</head>
<body data-bs-spy="scroll" data-bs-target="#profile-content-navbar" data-bs-offset="2" tabindex="0">
    <!-- navigation bar -->
    <div class="position-relative" style="z-index: 3;">
    <?php include_once '../../common/navigation_bar.php'; ?>
    </div>
    
    <?php include_once '../../common/signin_signup.php'; ?>
    
    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main class="position-relative">
        <div class="position-relative">
            <div id="cover-photo">
                <img src="../../../../admin/uploads/cover_photo/<?=$restaurant_info['cover_photo']?>" class="w-100" style="z-index: 1; max-height: 500px;">
            </div>
            <div class="top-100 position-absolute p-4 d-flex" id="profile-parent" style="transform: translate(0%,-50%); margin-left: 5%; margin-right: 5%;">
                <div id="profile-photo" class="align-self-center">
                    <img src="../../../../admin/uploads/profile_pic/<?=$restaurant_info['rest_profile']?>" class="w-100 img-fluid" style="z-index: 100; border: 3px solid #d0efff; max-height: 230px;">
                </div>
                <span class="text-nowrap align-self-center" id="rest-name-wrapper" style="margin-top: 6rem; margin-left: 1rem;">
                    <div class="d-flex flex-column">
                        <div class="d-flex">
                            <span class="font-bernard" style="font-size: min(3.5vw, 2rem);"><?=$restaurant_info['rest_name']?></span>
                            <?php if (!(!empty($restaurant_info['opening_time']) && !empty($restaurant_info['closing_time']) && strtotime(date('H:i')) >= strtotime($restaurant_info['opening_time']) && strtotime(date('H:i')) < strtotime($restaurant_info['closing_time']))) { ?>
                                <span class="px-2 ms-2 py-1 d-inline-block rounded bg-danger bg-opacity-75 text-white align-self-center" style="font-size: min(2vw, 12px); height: fit-content;">CLOSED</span>
                            <?php } ?>
                        </div>
                        <?php if (!empty($restaurant_info['opening_time']) && !empty($restaurant_info['closing_time'])) { ?>
                            <span class="text-muted" style="font-size: min(2vw, 0.875em);">
                                <i class="fas fa-clock"></i>&nbsp;<?=date('l', strtotime(date('Y-m-d'))) . ' : ' . date('h:i a', strtotime($restaurant_info['opening_time']))?> to <?=date('h:i a', strtotime($restaurant_info['closing_time']))?>
                            </span>
                        <?php } ?>
                    </div>
                </span>
            </div>
        </div>

        <input type="checkbox" hidden id="share-btn">
        <label class="d-inline-block position-absolute end-0 top-0 font-size-35" for="share-btn" style="margin-top: 1rem; margin-right: 1rem; cursor: pointer;"><i class="fas fa-share-alt rounded-circle text-white" style="padding: 0.8rem; padding-right: 1rem; background-color: rgba(36, 36, 36, 0.58);"></i></label>
        <div class="d-inline-block position-absolute end-0 top-0 font-size-35" id="favourite-btn" style="margin-top: 1rem; margin-right: 5.5rem; cursor: pointer;"><i class="<?=isset($favourite_restaurant) && $favourite_restaurant ? 'fas' : 'far' ?> fa-heart rounded-circle<?=isset($favourite_restaurant) && $favourite_restaurant ? '' : ' text-white' ?>" style="padding: 0.8rem; background-color: rgba(36, 36, 36, 0.58);<?=isset($favourite_restaurant) && $favourite_restaurant ? ' color: rgb(243, 77, 77);' : '' ?>"></i></div>

        <div class="position-absolute end-0" id="share-btns-wrapper" style="top: 76.6px;">
            <div class="rounded-circle" id="facebook"><a href="#" target="_blank"><i class="fab fa-facebook-f rounded-circle text-white" style="padding: 0.8rem; padding-left: 19.43px; padding-right: 19.3px; background-color: RGB(0, 112, 230);"></i></a></div>
            <div class="rounded-circle" id="twitter"><a href="#" target="_blank"><i class="fab fa-twitter rounded-circle text-white" style="padding: 0.8rem; background-color: RGB(29, 161, 242);"></i></a></div>
            <div class="rounded-circle" id="pinterest"><a href="#" target="_blank"><i class="fab fa-pinterest rounded-circle text-white" style="padding: 0.8rem; padding-right: 0.869rem; background-color: RGB(217, 5, 26);"></i></a></div>
            <div class="rounded-circle text-dark" id="whatsapp"><a href="#" target="_blank"><i class="fab fa-whatsapp rounded-circle" style="padding: 0.8rem; padding-right: 0.9rem; padding-left: 0.974rem; background-color: RGB(217, 253, 211);"></i></a></div>
        </div>

        <?php if (getSetting($connection, $_GET['visit_rest_id'], 'time_slot') || (!empty($restaurant_info['opening_time']) && !empty($restaurant_info['closing_time']) && strtotime(date('H:i')) >= strtotime($restaurant_info['opening_time']) && strtotime(date('H:i')) < strtotime($restaurant_info['closing_time']) && $food_menu_items)) $slide_in_tag = true; ?>
        <?php if (isset($slide_in_tag) && $slide_in_tag) { ?>
        <div class="position-fixed start-0" style="top: 90px; z-index: 2; height: 100px;">
            <div id="profile-bookmark-control-btn" class="ps-3 pb-2 pt-3 bg-dark text-white d-inline-block" style="border-top-right-radius:10px; cursor: pointer; padding-right: 1.25rem;" onclick="showHideSlideTag($(this).find('i'));">&nbsp;<i class="bi bi-caret-left-fill"></i></div>
            <div class="bg-dark" id="slide-tag-wrapper">
        <?php } ?>
        <?php if (getSetting($connection, $_GET['visit_rest_id'], 'time_slot')) { ?>
            <a href="../reservation/reservation.php?visit_rest_id=<?=$restaurant_info['rest_id']?>"><div class="font-size-20 py-2 text-white ps-2" style="z-index: 2; padding-right: 1.17rem;" id="book-table-btn">Book a Table&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-calendar-plus"></i></div></a>
        <?php } ?>
        <?php if (!empty($restaurant_info['opening_time']) && !empty($restaurant_info['closing_time']) && strtotime(date('H:i')) >= strtotime($restaurant_info['opening_time']) && strtotime(date('H:i')) < strtotime($restaurant_info['closing_time']) && $food_menu_items) { ?>
            <a href="../order/food_list.php?visit_rest_id=<?=$restaurant_info['rest_id']?>"><div class="font-size-20 py-2 text-white ps-2" style="z-index: 2; padding-right: 1.2rem;" id="order-food-btn">Order Food&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-cart-plus"></i></div></a>
        <?php } ?>
        <?php if (isset($slide_in_tag) && $slide_in_tag) { ?>
            </div>
            </div>
        <?php } ?>

        
        <div id="chat-room" class="box-style position-fixed border border-0" style="right: 25px; bottom: 25px; z-index: 3; max-width: 374px; max-height: 504px; border-radius: 15px;">
            <div class="bg-light h-100 position-relative d-none" id="chat-room-content" style="border-radius: 15px;">
                <div class="chat-room-header-footer d-flex" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <img src="../../../../admin/uploads/profile_pic/<?=$restaurant_info['rest_profile']?>" height="55" width="55" class="rounded-circle align-self-center m-3">
                    <div class="align-self-center flex-grow-1 fw-bold text-white font-century">
                        <div class="d-flex flex-column">
                            <?=$restaurant_info['rest_name']?>
                            <span class="d-flex" id="chat-room-online-status">
                                <?php if ($online_login_activities) { ?>
                                    <i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i>
                                    <small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>
                                <?php } elseif (empty($rest_login_activity)) { ?>
                                    <i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i>
                                    <small class="ms-2 text-white" style="font-weight: 500 !important;">Inactive</small>
                                <?php } else { ?>
                                    <?php $online_status = calcTimeAgo(strtotime($rest_login_activity['logout_time'])); ?>
                                    <?php if ($online_status == 'Active') { ?>
                                        <i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i>
                                        <small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>
                                    <?php } else { ?>
                                        <i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i>
                                        <small class="ms-2 text-white" style="font-weight: 500 !important;"><?=$online_status?></small>
                                    <?php } ?>
                                <?php } ?>
                            </span>
                        </div>
                    </div>
                    <div class="align-self-center">
                        <a href="../message/chatroom.php?rest_id=<?=$restaurant_info['rest_id']?>"><i class="fas fa-external-link-alt" style="color: #6f5378;"></i></a>
                    </div>
                    <button type="button" id="close-chat-btn" class="btn-close align-self-center m-3"></button>
                </div>
                <div id="chat-content-wrapper" style="overflow-y: auto; min-height: 70%; max-height: 70%;">
                    <div id="chat-content" style="background-color: white; min-height: 352.8px;" class="p-3 d-flex flex-column">
                        <?php if (isset($chats) && !empty($chats)) { ?>
                            <?php foreach ($chats as $chat) { ?>
                                <?php if (!empty($chat['file'])) { ?>
                                    <?php 
                                        $file_name = substr($chat['file'], (strpos($chat['file'],'-')) + 1);
                                        if (strlen($file_name) > 18){
                                            $file_name_1 = substr($file_name, 0, 18) . '...';
                                        } else{
                                            $file_name_1 = $file_name;
                                        }
                                    ?>
                                    <div class="chat-msgs <?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? 'align-self-end' : 'align-self-start color-2' ?> border border-2 rounded p-2 mb-1"<?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? ' style="background-color: #f0f0f0;"' : ' style="border-color: #edd8ed !important;"' ?> id="chat-<?=$chat['chat_id']?>">
                                        <a href="../../../<?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? '' : '../admin/' ?>uploads/chat_files/<?=$chat['file']?>" download="<?=$file_name?>">
                                            <div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;">
                                                <i class="fas fa-file m-2" style="font-size: 25px;"></i>
                                                <span class="me-2 align-self-center"><?=$file_name_1?></span>
                                            </div>
                                        </a>
                                        <small class="d-block text-end">
                                            <i><?=date('d/m/Y h:i a', strtotime($chat['sent_at']))?></i>
                                            <?php if ($chat['sender_id'] == $_SESSION['login_cus_id']) { ?>
                                            &nbsp;
                                            <i class="fas fa-eye<?=$chat['seen'] == '0' ? ' d-none' : '' ?>" id="chat-<?=$chat['chat_id']?>-read"></i>
                                            <i class="fas fa-check<?=$chat['seen'] == '1' ? ' d-none' : '' ?>" id="chat-<?=$chat['chat_id']?>-sent"></i>
                                            <?php } ?>
                                        </small>
                                    </div>
                                <?php } else { ?>
                                    <p class="chat-msgs <?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? 'align-self-end' : 'align-self-start color-2' ?> border border-2 rounded p-2 mb-1"<?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? ' style="background-color: #f0f0f0;"' : ' style="border-color: #edd8ed !important;"' ?> id="chat-<?=$chat['chat_id']?>">
                                        <?=$chat['message']?>
                                        <small class="d-block text-end">
                                            <i><?=date('d/m/Y h:i a', strtotime($chat['sent_at']))?></i>
                                            <?php if ($chat['sender_id'] == $_SESSION['login_cus_id']) { ?>
                                            &nbsp;
                                            <i class="fas fa-eye<?=$chat['seen'] == '0' ? ' d-none' : '' ?>" id="chat-<?=$chat['chat_id']?>-read"></i>
                                            <i class="fas fa-check<?=$chat['seen'] == '1' ? ' d-none' : '' ?>" id="chat-<?=$chat['chat_id']?>-sent"></i>
                                            <?php } ?>
                                        </small>
                                    </p>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <div id="upload-files-btns" class="position-absolute d-flex flex-column" style="bottom: 16%">
                    <button type="button" id="upload-img-btn" class="text-white px-3 m-2 mb-1" style="display: none; padding-top: 0.6rem; padding-bottom: 0.65rem; background-color: rgba(36, 36, 36); z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="right" title="Images" onclick="$('#upload-imgs').click(); showHideChatRoomUploadBtns();"><i class="fas fa-images"></i></button>
                    <button type="button" id="upload-file-btn" class="text-white px-3 m-2" style="display: none; padding-top: 0.6rem; padding-bottom: 0.65rem; background-color: rgba(36, 36, 36); z-index: 2;" data-bs-toggle="tooltip" data-bs-placement="right" title="Files" onclick="$('#upload-files').click(); showHideChatRoomUploadBtns();"><i class="fas fa-file-alt"></i></button>
                </div>
                <div class="position-absolute d-none" id="scroll-btn-wrapper" style="bottom: 90px; right: 25px;">
                    <button type="button" id="scroll-to-bottom" class="position-relative px-3 m-2 mb-1 rounded-circle text-white" style="padding-top: 0.7rem; display: none; padding-bottom: 0.65rem; z-index: 2; background-color: #dc9dee;">
                        <i class="fas fa-angle-down" style="font-size: 20px;"></i>
                        <span class="position-absolute d-none translate-middle p-2 bg-danger border border-light rounded-circle" id="has-msg-symbol" style="left: 90%; top: 15%">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    </button>
                </div>
                <div id="chat-room-upload-file-preview" class="position-absolute w-100 bg-dark bg-opacity-50" style="bottom: 15%">
                    <div class="px-5 py-3 d-none">
                        <div class="owl-carousel owl-theme"></div>
                    </div>
                </div>
                <div class="chat-room-header-footer d-flex position-absolute bottom-0 w-100" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <div class="align-self-center">
                        <button type="button" id="btn-plus" class="text-white px-3 mx-2" style="padding-top: 0.6rem; padding-bottom: 0.65rem;" onclick="showHideChatRoomUploadBtns();"><i class="fas fa-plus"></i></button>
                    </div>
                    <input type="file" id="upload-imgs" class="d-none" accept="image/*" capture multiple>
                    <input type="file" id="upload-files" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple>
                    <textarea class="form-control my-3 border border-0" id="chat-message" style="border-radius: 10px; line-height: 30px;" placeholder="Write a message..."></textarea>
                    <div class="align-self-center">
                        <button type="button" id="send-msg-btn" class="text-white px-3 mx-2" style="padding-top: 0.6rem; padding-bottom: 0.65rem;"><i class="bi bi-send-fill"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-fixed" id="chat-btn-parent" style="right: 25px; bottom: 25px; z-index: 2; border-radius: 45px;">
            <button type="button" id="chat-btn" class="px-3 py-2" style="border-radius: 45px; background-color: #C968E5;"><i class="bi bi-chat-dots-fill text-white" style="font-size: 40px;"></i></button>
            <span class="position-absolute d-none translate-middle p-2 bg-danger border border-light rounded-circle has_unseen_msg" style="left: 90%; top: 15%">
                <span class="visually-hidden">New alerts</span>
            </span>
        </div>
        <div class="position-fixed" id="chat-btn-sm-parent" style="right: 25px; bottom: 25px; z-index: 2; border-radius: 45px; display: none;">
            <a href="../message/chatroom.php?rest_id=<?=$restaurant_info['rest_id']?>"><button type="button" id="chat-btn-sm" class="px-3 py-2" style="border-radius: 45px; background-color: #C968E5; "><i class="bi bi-chat-dots-fill text-white" style="font-size: 40px;"></i></button></a>
            <span class="position-absolute d-none translate-middle p-2 bg-danger border border-light rounded-circle has_unseen_msg" style="left: 90%; top: 15%">
                <span class="visually-hidden">New alerts</span>
            </span>
        </div>
        

        <div class="mx-5 px-3" id="profile-content" style="min-height: 100vh;">
            <nav id="profile-content-navbar" class="navbar navbar-light px-3 position-sticky top-0" style="background-color: #dadcde; z-index: 1;">
                <ul class="nav nav-pills">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false"><?=$restaurant_info['rest_name']?></a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#restaurant-details"><?=$restaurant_info['rest_name']?></a></li>
                          <?php $photos = json_decode($restaurant_info['photos'], true); ?>
                          <?php if ($photos) { ?>
                            <li><a class="dropdown-item" href="#photos">Photos</a></li>
                          <?php } ?>
                          <?php if ($food_menu_items) { ?>
                            <li><a class="dropdown-item" href="#food-menu">Food Menu</a></li>
                          <?php } ?>
                            <li><a class="dropdown-item" href="#offer">Available Offers</a></li>
                            <li><a class="dropdown-item" href="#additional-info">Additional Information</a></li>
                            <li><a class="dropdown-item" href="#reviews">Ratings and Reviews</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <div class="bg-light p-4 pt-2" style="border: 1px solid rgba(0,0,0,0.15); margin-bottom: 100px;">
                <div id="restaurant-details" class="content-section mb-3">
                    <!-- average ratings -->
                    <span class="me-3">
                        <?php if (isset($avg_rating)) { ?>
                            <?php 
                                if (is_int($avg_rating)) { 
                                    $final_avg_rating = $avg_rating;
                                } else {
                                    $final_avg_rating = floor($avg_rating);
                                }
                            ?>
                            <?php for ($i=0; $i<$final_avg_rating; $i++) { ?>
                                <i class="fas fa-star text-danger"></i>
                            <?php } ?>
                            <?php if (!is_int($avg_rating)) { ?>
                                <i class="fas fa-star-half-alt text-danger"></i>
                                <?php for ($i=0; $i<(4-$final_avg_rating); $i++) { ?>
                                    <i class="fas fa-star text-secondary"></i>
                                <?php } ?>
                            <?php } else { ?>
                                <?php for ($i=0; $i<(5-$final_avg_rating); $i++) { ?>
                                    <i class="far fa-star text-danger"></i>
                                <?php } ?>
                            <?php } ?>
                        <?php } else { ?>
                            <?php for ($i=0; $i<5; $i++) { ?>
                                <i class="far fa-star text-danger"></i>
                            <?php } ?>
                            &nbsp; <span class="text-muted">0 Ratings</span>
                        <?php } ?>
                    </span>
                    <!-- total reviews -->
                    <span class="text-muted me-3">
                        <i class="bi bi-chat-right-text-fill d-inline-block" style="transform: translate(0%,2px);"></i>&nbsp;&nbsp;<span><?=$total_reviews?> Reviews</span>
                    </span>
                    <!-- dining style -->
                    <?php if ($restaurant_info['dining_style_id'] != '0') { ?>
                        <span class="text-muted">
                            <i class="fas fa-utensils"></i>&nbsp;&nbsp;<span style="text-transform: capitalize;"><?=getDiningStyle($connection, $restaurant_info['dining_style_id'])['dining_style']?></span>
                        </span>
                    <?php } ?>
                    <!-- top tags -->
                    <div class="d-flex mt-2">
                        <?php $tags = json_decode($restaurant_info['tags'], true); ?>
                        <?php if ($tags) { ?>
                            <span class="align-self-center mb-2">Top Tags:</span>
                            <div class="flex-grow-1 flex-wrap">
                            <?php foreach ($tags as $key => $tag) { ?>
                                <?php if ($key == 5) break; ?>
                                <div class="d-inline-block mb-2">
                                <div class="d-flex bg-white border border-danger border-2 p-0 m-0 ms-3" style="height: 40px; border-radius: 5rem; cursor: default;">
                                    <div class="d-inline-block px-2 align-self-center"><small><?=$tag?></small></div>
                                </div>
                                </div>
                            <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <!-- description -->
                    <?php if (!empty($restaurant_info['description'])) { ?>
                        <p class=""><?=$restaurant_info['description']?></p>
                    <?php } ?>
                </div>
                <!-- photos -->
                <?php if ($photos) { ?>
                <div id="photos" class="content-section mb-3">
                    <h4><span class="d-inline-block pt-2 fw-bold">Photos</span></h4>
                    <div class="d-flex flex-wrap mt-2">
                        <?php foreach ($photos as $key => $photo) {?>
                            <?php if ($key == 7) break; ?>
                            <?php if ($key == 6) { ?>
                            <div class="position-relative text-center text-white" onclick="$('.carousel').carousel(<?=$key?>)" data-bs-toggle="modal" data-bs-target="#photoModal">
                                <div class="position-absolute top-0 m-1 bg-dark bg-opacity-75 d-flex" style="width: 150px; height: 150px; cursor: pointer;"><span class="w-100 align-self-center">+ <?=(string)(count($photos) - 6)?> more</span></div>
                            <?php } ?>
                                <img src="../../../../admin/uploads/photos/<?=$photo?>" width="150" height="150" style="cursor: pointer;" class="m-1"<?=$key < 6 ? 'onclick=$(".carousel").carousel(' . $key . ') data-bs-toggle="modal" data-bs-target="#photoModal"' : ''?>>
                            <?php if ($key == 6) { ?>
                            </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <!-- food menu -->
                <?php if ($food_menu_items) { ?>
                <div id="food-menu" class="content-section mb-3">
                    <h4><span class="d-inline-block pt-2 fw-bold">Food Menu</span></h4>
                    <div class="w-100 p-2 pb-0">
                    <?php foreach ($food_menu_items as $key => $category_foods) { ?>
                    <?php $category_name = getCategory($connection, $key)['category_name']; ?>
                        <div>
                            <span style="font-size: 1rem;" class="ps-2"><?=ucwords($category_name)?></span>
                            <hr style="border-width: 3px;" class="my-2">
                        </div>
                        <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 w-100 mx-0">
                            <?php foreach ($category_foods as $key => $food_menu_item) {?>
                                <?php
                                    $promotion_infos = getPromotions($connection, $_GET['visit_rest_id'], ['food_item_id' => $food_menu_item['item_id'], 'status' => '1']);

                                    $buy_free = '';
                                    $discount_price = '';
                                    if (!empty($promotion_infos)){
                                        foreach ($promotion_infos as $promotion_info){
                                            if (isset($promotion_info['settings_data'])){
                                                $settings_data = json_decode($promotion_info['settings_data'], true);
                                            }
                                            if ($promotion_info['type_code'] == 'BOGO'){
                                                $buy_free = 'Buy 1 Get 1 Free';
                                            } elseif ($promotion_info['type_code'] == 'multi_buy'){
                                                $buy_free = 'Buy ' . $settings_data['amount_1'] . ' Get ' . $settings_data['amount_2'] . ' Free';
                                            } elseif ($promotion_info['type_code'] == 'percent_off'){
                                                $discount_price = $food_menu_item['price'] * (1-((int)$settings_data['percentage'] / 100));
                                            } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                                                $discount_price = $settings_data['discounted_price'];
                                            } 
                                        }
                                    }
                                ?>
                                <div class="col p-2 food-menu-items">
                                    <a href="../order/food_list.php?visit_rest_id=<?=$_GET['visit_rest_id']?>#food-<?=$food_menu_item['item_id']?>">
                                        <div class="p-3 border border-3 rounded" style="background-color: white;">
                                            <div class="d-flex food-menu-items-details"<?=$food_menu_item['status'] == '0'? ' style="opacity: 0.5;"' : '' ?>>
                                                <img src="../../../../admin/uploads/food_menu_photo/<?=$food_menu_item['image']?>" height="90" width="90">
                                                <div class="ms-3 flex-grow-1 d-flex flex-column">
                                                    <?=$food_menu_item['item_name']?><br>
                                                    <span class="<?=$food_menu_item['status'] == '0'? '' : 'text-muted' ?><?=(isset($discount_price) && !empty($discount_price) && $food_menu_item['status'] == '1' ? ' text-decoration-line-through' : '')?>"><small>RM <?=number_format($food_menu_item['price'], 2, '.', '')?>&nbsp;</small></span>
                                                    <?php if (isset($discount_price) && !empty($discount_price) && $food_menu_item['status'] == '1') { ?>
                                                        <span class="text-danger"><small>RM <?=number_format($discount_price, 2, '.', '')?></small></span>
                                                    <?php } ?>
                                                    <?php if ($food_menu_item['status'] == '0') {?>
                                                    <span class="text-danger d-inline-block mt-auto" style="font-size: 0.9rem;">Unavailable</span>
                                                    <?php } else { ?>
                                                        <?php if (isset($buy_free) && !empty($buy_free)) { ?>
                                                            <span class="text-danger d-inline-block mt-auto"><small>(<?=$buy_free?>)</small></span>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <!-- available offer/promo code -->
                <div id="offer" class="content-section mb-3">
                    <div class="d-flex flex-wrap">
                        <h4><span class="d-inline-block pt-2 fw-bold me-2">Available Offers</span></h4>
                        <?php if (isset($_SESSION['login_cus_id']) && $promo_codes_info) { ?>
                        <button class="btn btn-sm btn-outline-primary align-self-center" onclick="showRedemptionChanceModal()">Check Redemption Chance Left</button>
                        <?php } ?>
                    </div>
                    <div class="d-flex flex-column mt-2 m-1">
                        <?php if ($promo_codes_info) { ?>
                            <?php foreach ($promo_codes_info as $promo_code) {?>
                                <div class="p-3 mb-2 border border-3 rounded d-flex flex-wrap" style="background-color: white; cursor: default;">
                                    <?php $promo_code_settings_data = json_decode($promo_code['settings_data'], true); ?>
                                    <i class="fas fa-tags font-size-35 m-3 text-success align-self-center"></i>
                                    <div><strong class="font-size-25"><?=$promo_code_settings_data['promo_code_name']?></strong><br><span class="text-muted">minimum spend</span> RM <?=$promo_code_settings_data['min_price']?><br><span class="text-muted">redemption limit</span> <?=$promo_code_settings_data['max_redemption']?><br><span class="text-muted">discount</span> RM <?=$promo_code_settings_data['actual_discount']?></div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="p-3 mb-2 border border-3 rounded text-center" style="background-color: white; cursor: default;">
                                No Available Offers
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <!-- additional information -->
                <div id="additional-info" class="content-section mb-3">
                    <h4><span class="d-inline-block pt-2 fw-bold">Additional Information</span></h4>
                    <div class="row m-1 mt-3">
                        <div class="d-flex mb-3 col-12 col-sm-12 col-md-6">
                            <span class="text-center"><i class="fas fa-phone-alt pt-1" style="width: 20px;"></i></span>
                            <span class="ms-2">Contact Number<br><span class="text-muted"><?=$restaurant_info['contact_num']?></span></span>
                        </div>
                        <div class="d-flex mb-3 col-12 col-sm-12 col-md-6">
                            <span class="text-center"><i class="fas fa-envelope pt-1" style="width: 20px;"></i></span>
                            <span class="ms-2">Email Address<br><span class="text-muted"><a href="mailto:<?=$restaurant_info['email']?>" style="color: blue;"><u class="text-break d-inline-block" id="email-address"><?=$restaurant_info['email']?></u></a></span></span>
                        </div>
                        <div class="d-flex mb-3 col-12 col-sm-12 col-md-6">
                            <span class="text-center"><i class="fas fa-map-marker-alt pt-1" style="width: 20px;"></i></span>
                            <span class="ms-2">Address<br><span class="text-muted"><?=$restaurant_info['address']?></span></span>
                        </div>
                        <div class="d-flex mb-3 col-12 col-sm-12 col-md-6">
                            <span class="text-center"><i class="fas fa-city pt-1"></i></span>
                            <span class="ms-2">City<br><span class="text-muted"><?=$restaurant_info['city']?></span></span>
                        </div>
                        <div class="d-flex mb-3 col-12 col-sm-12 col-md-12">
                            <?php 
                                $opening_hours = json_decode($restaurant_info['opening_hours'], true);
                                $day = array(
                                    '1' => 'Monday',
                                    '2' => 'Tuesday',
                                    '3' => 'Wednesday',
                                    '4' => 'Thursday',
                                    '5' => 'Friday',
                                    '6' => 'Saturday',
                                    '7' => 'Sunday'
                                );
                            ?>
                            <span class="text-center"><i class="fas fa-clock pt-1" style="width: 20px;"></i></span>
                            <span class="ms-2">
                                Opening Hours
                                <span class="text-muted">
                                <?php foreach ($opening_hours as $opening_hour) { ?>
                                    <?php if ($opening_hour['to'] != '0') { ?>
                                        <br><?=$day[$opening_hour['from']]?> to <?=$day[$opening_hour['to']]?> : <?=date('h:i a', strtotime($opening_hour['open']))?> to <?=date('h:i a', strtotime($opening_hour['close']))?>
                                    <?php } else { ?>
                                        <br><?=$day[$opening_hour['from']]?> : <?=date('h:i a', strtotime($opening_hour['open']))?> to <?=date('h:i a', strtotime($opening_hour['close']))?>
                                    <?php } ?>
                                <?php } ?>
                                </span>
                            </span>
                        </div>
                        <?php if (!empty($restaurant_info['additional_notes'])) { ?>
                        <div class="d-flex mb-3 col-12 col-sm-12 col-md-12">
                            <span class="text-center"><i class="fas fa-tag pt-1" style="width: 20px;"></i></span>
                            <span class="ms-2">Additional<br><span class="text-muted"><?=$restaurant_info['additional_notes']?></span></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <!-- ratings and reviews -->
                <div id="reviews" class="content-section mb-3">
                    <h4><span class="d-inline-block pt-2 fw-bold">Ratings and Reviews</span></h4>
                    <div class="row m-1 mt-2">
                        <div class="text-muted mb-2"><i class="fas fa-user"></i>&nbsp;&nbsp;<?=count($ratings_reviews_results)?> people has given ratings/reviews</div>
                        <div class="d-flex flex-column">
                            <?php foreach ($ratings_group_total as $key => $ratings_group) { ?>
                            <div class="d-flex">
                                <span class="me-2" style="width: 10.06px;<?=$key == 1 ? ' padding-left: 0.15rem;' : '' ?>"><?=$key?></span>
                                <div class="progress" style="width: 50%">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?=$ratings_group/count($ratings_reviews_results)*100?>%" aria-valuenow="<?=$ratings_group/count($ratings_reviews_results)*100?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                        <?php if ($total_reviews == 0) { ?>
                            <div class="p-3 mb-2 mt-4 border border-3 rounded" style="background-color: white; cursor: default;">
                                At present, <?=$restaurant_info['rest_name']?> has no reviews. Please add a review after your dining experience to help others make a decision about where to eat.
                            </div>
                        <?php } else { ?>
                            <div class="p-3 mb-2 mt-4 border border-3 rounded" style="background-color: white; cursor: default;">
                                <?php foreach ($reviews as $key => $rating_review) { ?>
                                    <div class="d-flex a_review<?=$key == 0 ? '' : ' mt-3 pt-3 border-top border-3'?>">
                                        <div class="d-flex flex-column me-3 mb-3">
                                            <img src="../../../uploads/profile_pic/<?=$rating_review['profile_pic']?>" height="80" width="80" class="rounded-circle" style="border: 3px solid #d0efff;">
                                            <span class="align-self-center text-center"><?=$rating_review['cus_name']?></span>
                                        </div>                                 
                                        <div class="d-flex flex-column align-self-center">
                                            <div>
                                                <div class="d-flex mb-2">
                                                <?php for ($i=0; $i<$rating_review['rating']; $i++) { ?>
                                                    <i class="fas fa-star text-danger me-1"></i>
                                                <?php } ?>
                                                <?php for ($i=0; $i<(5-$rating_review['rating']); $i++) { ?>
                                                    <i class="fas fa-star text-secondary"></i>
                                                <?php } ?>
                                                </div>
                                                <span>Dined on <?=date('d/m/Y', strtotime($rating_review['dine_date']))?></span>
                                            </div>
                                            <p class="mt-2 reviews"><?=$rating_review['review']?></p>
                                            <p class="replys bg-warning bg-opacity-25 p-3 border border-2 rounded"><b>Restaurant Response:</b><br><?=$rating_review['reply']?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <!-- Photo Modal + Carousel -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark position-relative">
                <div id="carouselPhotoFade" class="carousel slide carousel-fade" data-bs-interval="false">
                    <div class="carousel-inner">
                        <?php foreach ($photos as $key => $photo) { ?>
                            <div class="carousel-item<?=$key == 0 ? ' active' : ''?>">
                                <img src="../../../../admin/uploads/photos/<?=$photo?>" class="d-block m-auto carousel-img">
                            </div>
                        <?php } ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotoFade" data-bs-slide="prev">
                        <span class="p-4 d-inline-block" aria-hidden="true"><i class="bi bi-caret-left-fill btn-dark rounded" style="font-size: 50px;"></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotoFade" data-bs-slide="next">
                        <span class="p-4 d-inline-block" aria-hidden="true"><i class="bi bi-caret-right-fill btn-dark rounded" style="font-size: 50px;"></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <button type="button" class="position-absolute top-0 end-0 p-3 pt-0 text-white" data-bs-dismiss="modal" aria-label="Close" id="close-photo-btn" style="z-index: 1; opacity: 0.5"><i class="bi bi-x" style="font-size: 50px;"></i></button>
            </div>
        </div>
    </div>
    
    <?php if (isset($_SESSION['login_cus_id']) && $promo_codes_info) { ?>
        <div class="modal fade p-0" id="redemptionChanceModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">My Redemption Chance Left</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php foreach ($promo_codes_info as $promo_code) {?>
                            <div class="p-3 mb-2 border border-3 rounded d-flex flex-wrap" style="background-color: white; cursor: default;">
                                <?php $promo_code_settings_data = json_decode($promo_code['settings_data'], true); ?>
                                <i class="fas fa-tags font-size-35 m-3 text-success align-self-center"></i>
                                <div><span class="promo-code-id d-none"><?=$promo_code['promotion_id']?></span><strong class="font-size-25"><?=$promo_code_settings_data['promo_code_name']?></strong><br><span class="text-muted">Redemption Chance Left</span>&nbsp;&nbsp;<span id="redemption-left-<?=$promo_code['promotion_id']?>"><?=$promo_code_settings_data['max_redemption']?></span></div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php }  ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>

    <script src="../../../javascript/chatroom.js"></script>

    <script>
        if (localStorage.getItem('profile-bookmark') == 'hide'){
            $('#slide-tag-wrapper').addClass('unactive');
            $('#profile-bookmark-control-btn i').toggleClass('bi-caret-left-fill bi-caret-right-fill');
        }

        function showHideSlideTag(arrow_icon){
            if (!$('#slide-tag-wrapper').hasClass('unactive')){
                $('#slide-tag-wrapper').addClass('unactive');
                localStorage.setItem('profile-bookmark', 'hide');
            } else {
                $('#slide-tag-wrapper').removeClass('unactive');
                localStorage.setItem('profile-bookmark', 'show');
            }
            arrow_icon.toggleClass('bi-caret-left-fill bi-caret-right-fill');
        }

        function chatRoomPreviewFiles(){
            $('#chat-room-upload-file-preview .owl-carousel').owlCarousel({
                nav: true,
                dots: false,
                margin: 10,
                loop: ( $('#chat-room-upload-file-preview .owl-carousel .items').length > 3 ),
                items: 3
            });
        }

        function chatOnlineStatusUpdate(){
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant_info['rest_id']?>',
                    update_online_status_store: true
                },
                method: 'post',
                success: function(output) {
                    $('#chat-room-online-status').html(output);
                }
            });
        }

        function chatReadStatusUpdate(){
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant_info['rest_id']?>',
                    update_read_status_store: true
                },
                method: 'post',
                success: function(output) {
                    var json = $.parseJSON(output);

                    if (json.length > 0){
                        $.each(json, function(key, value) {
                            $('#chat-' + value + '-read').removeClass('d-none');
                            $('#chat-' + value + '-sent').addClass('d-none');
                        })
                    }
                }
            });
        }

        function checkHasNewMessages(){
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant_info['rest_id']?>',
                    check_has_new_msg: true
                },
                method: 'post',
                success: function(output) {
                    if (output == 'true'){
                        $('.has_unseen_msg').removeClass('d-none');
                    } else {
                        $('.has_unseen_msg').addClass('d-none');
                    }
                }
            });
        }

        function newChatsUpdate(){
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant_info['rest_id']?>',
                    update_new_chats_store: true
                },
                method: 'post',
                success: function(output) {
                    var json = $.parseJSON(output);
                    var has_new_chat = false;

                    if (json.length > 0){
                        $.each(json, function(key, value) {
                            if ($('#chat-' + value['chat_id']).length == 0){
                                if (value['message']){
                                    $('#chat-content').append('<p class="chat-msgs align-self-start color-2 border border-2 rounded p-2 mb-1" style="border-color: #edd8ed !important;" id="chat-' + value['chat_id'] + '">' + value['message'] + '<small class="d-block text-end"><i>' + value['sent_at'] + '</i></small></p>');
                                    has_new_chat = true;
                                } else if (value['file']){
                                    var file_name = value['file'].substring((value['file'].indexOf('-') + 1));
                                    if (file_name.length > 18){
                                        file_name_1 = file_name.substring(0, 18) + '...';
                                    } else {
                                        file_name_1 = file_name;
                                    }

                                    $('#chat-content').append('<div class="chat-msgs align-self-start color-2 border border-2 rounded p-2 mb-1" style="border-color: #edd8ed !important;" id="chat-' + value['chat_id'] + '"><a href="../../../../admin/uploads/chat_files/' + value['file'] + '" download="' + file_name + '"><div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;"><i class="fas fa-file m-2" style="font-size: 25px;"></i><span class="me-2 align-self-center">' + file_name_1 + '</span></div></a><small class="d-block text-end"><i>' + value['sent_at'] + '</i></small></div>');
                                    has_new_chat = true;
                                }
                            }
                        });

                        if (has_new_chat){
                            seenMessage();
                            if ($('#chat-room-content').hasClass('d-none')){
                                $('.has_unseen_msg').removeClass('d-none');
                            }
                        }

                        if (has_new_chat && ($('#chat-content').height() - $('#chat-content-wrapper').height() - $('#chat-content-wrapper').scrollTop() < $('#chat-content-wrapper').height())){
                            chatRoomScrollDown();
                        } else if (has_new_chat){
                            $('#chat-content-wrapper').scrollTop($('#chat-content-wrapper').scrollTop() + 10);
                            $('#has-msg-symbol').removeClass('d-none');
                        }
                    }
                }
            });
        }

        function seenMessage(){
            if (!$('#chat-room-content').hasClass('d-none')){
                $.ajax({
                    url: '../../helpers/message.php',
                    data: {
                        rest_id: '<?=$restaurant_info['rest_id']?>',
                        seen_message: true
                    },
                    method: 'post'
                });
            }
        }

        function showRedemptionChanceModal(){
            $('#redemptionChanceModal .promo-code-id').each(function() {
                var promo_id = $(this).text();
                $.ajax({
                    url: '../../helpers/restaurant.php',
                    data: {
                        promotion_id: promo_id,
                        check_redemption_chance: true
                    },
                    async: false,
                    method: 'post',
                    success: function(output) {
                        if (output)
                            $('#redemption-left-' + promo_id).text(output);
                    }
                });
            });

            $('nav, main, footer').addClass('blur');
            $('#redemptionChanceModal').modal('show');
        }

        $(document).ready(function() {
            $('.close-details-modal').click(function() {
                $('nav, main, footer').removeClass('blur');
            })

            $('#favourite-btn').click(function() {
                <?php if (!isset($favourite_restaurant)) { ?>
                    slideInMsg('error', 'Please sign in to your account to save the restaurant!');
                <?php } else { ?>
                    var this_element = $(this);
                    if ($(this).find('i').hasClass('text-white')){
                        //save restaurant
                        $.ajax({
                            url: '../../helpers/restaurant.php',
                            data: {
                                rest_id: '<?=$restaurant_info['rest_id']?>',
                                save_favourite_rest: true
                            },
                            method: 'post',
                            success: function() {
                                this_element.find('i').css('color', 'rgb(243, 77, 77)');
                                this_element.find('i').removeClass('far text-white');
                                this_element.find('i').addClass('fas');
                            }
                        });
                    } else {
                        //remove restaurant
                        $.ajax({
                            url: '../../helpers/restaurant.php',
                            data: {
                                rest_id: '<?=$restaurant_info['rest_id']?>',
                                remove_favourite_rest: true
                            },
                            method: 'post',
                            success: function() {
                                this_element.find('i').css('color', '');
                                this_element.find('i').removeClass('fas');
                                this_element.find('i').addClass('far text-white');
                            }
                        });
                    }
                <?php } ?>
            });
                           
            $('.carousel-img').each(function() {
                var this_element = $(this);
                $("<img>").attr('src', $(this).attr('src'))
                .on('load', function() {
                    if (parseInt(`${this.height}`) > parseInt(`${this.width}`)){
                        this_element.css({
                            'height': '100vh',
                            'max-width': '100%'
                        });
                    } else if (parseInt(`${this.height}`) == parseInt(`${this.width}`)){
                        this_element.css({
                            'height': '100vh',
                            'max-width': '100vw'
                        });
                    } else {
                        this_element.css({
                            'height': '100vh',
                            'width': '100vw'
                        });
                    }
                });
            });

            $('.reviews').each(function() {
                if ($(this).text().length > 209){
                    var show = $(this).text().substring(0, 209);
                    var hide = $(this).text().substring(209, $(this).text().length);
                    var new_reviews_content = show + "<span>...</span><span style='display: none;'>" + hide + "</span><button class='mt-2 btn btn-sm btn-outline-danger d-block read-more-btn'>Read <span>More</span><span style='display: none;'>Less</span></button>";
                    $(this).html(new_reviews_content);
                }
            });

            $('.read-more-btn').click(function() {
                $(this).parent().find('span').toggle();
            });

            $("#chat-btn").click(function(){
                $(this).parent().toggle();
                $('#chat-room').animate({opacity: 1}, {queue: false, duration: 'slow'});
                $("#chat-room").animate({
                    height: '+=504px',
                    width: '+=374px',
                    duration: 'slow'
                }, function() {
                    chatRoomScrollDown();
                    $('#scroll-btn-wrapper').removeClass('d-none');
                });
                $('#chat-room-content').removeClass('d-none');
                $('#chat-message').focus();
                seenMessage();
                $('.has_unseen_msg').addClass('d-none');
            });

            $("#close-chat-btn").click(function(){
                $('#chat-room').animate({opacity: 0}, {queue: false, duration: 'slow'});
                $("#chat-room").animate({
                    height: '-=504px',
                    width: '-=374px',
                    duration: 'slow'
                }, function() {
                    $('#chat-room-content').addClass('d-none');
                    $('#scroll-btn-wrapper').addClass('d-none');
                    $("#chat-btn").parent().slideToggle("slow");
                });
                $('#chat-message').blur();
                $('#chat-message, #upload-imgs, #upload-files').val('');
                temp_uploaded_files = [];
                
                <?php if (!isset($_SESSION['login_cus_id'])) { ?>
                $('#chat-content').children().remove();
                <?php } ?>
                
                //remove guest id
                $.ajax({
                    url: '../../helpers/message.php',
                    data: {
                        rest_id: '<?=$restaurant_info['rest_id']?>',
                        remove_guest_id: true
                    },
                    method: 'post'
                });

                //remove preview section's files
                $('#chat-room-upload-file-preview .owl-carousel').remove();
                $('#chat-room-upload-file-preview div').addClass('d-none');
                $('#chat-room-upload-file-preview div').append('<div class="owl-carousel owl-theme"></div>');
            });

            $(window).on('unload', function() {
                //remove guest id
                var data = new FormData();
                data.append('rest_id', '<?=$restaurant_info['rest_id']?>');
                data.append('remove_guest_id', true);

                navigator.sendBeacon('../../helpers/message.php', data);
            });

            $('#upload-imgs').change(function() {
                $return_msg = previewImages($('#upload-imgs'));
                if ($return_msg != ''){
                    slideInMsg('error', $return_msg);
                }
            });

            $('#upload-files').change(function() {
                $return_msg = previewFiles($('#upload-files'));
                if ($return_msg != ''){
                    slideInMsg('error', $return_msg);
                }
            });            
            
            $('#send-msg-btn').click(function() {
                if (!$.trim($('#chat-message').val()) && temp_uploaded_files.length == 0){
                    $('#chat-message').focus();
                    return;
                }

                //files
                if (temp_uploaded_files.length > 0){
                    var data = new FormData(); 
                    var count = 0;
                    $.each(temp_uploaded_files, function(i, file){
                        data.append(count, file);
                        count++;
                    });

                    data.append('rest_id', '<?=$restaurant_info['rest_id']?>');
                    data.append('send_file_store', true);

                    $.ajax({
                        url: '../../helpers/message.php',
                        data: data,
                        processData: false,
                        contentType: false,
                        method: 'post',
                        async: false,
                        success: function(output) {
                            var json = $.parseJSON(output);

                            $.each(json, function(key, value) {
                                if (!value['error']){
                                    var file_name = value['name'].substring((value['name'].indexOf('-') + 1));
                                    if (file_name.length > 18){
                                        file_name_1 = file_name.substring(0, 18) + '...';
                                    } else {
                                        file_name_1 = file_name;
                                    }

                                    $('#chat-content').append('<div class="chat-msgs align-self-end border border-2 rounded p-2 mb-1" style="background-color: #f0f0f0;" id="chat-' + value['chat_id'] + '"><a href="../../../uploads/chat_files/' + value['name'] + '" download="' + file_name + '"><div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;"><i class="fas fa-file m-2" style="font-size: 25px;"></i><span class="me-2 align-self-center">' + file_name_1 + '</span></div></a><small class="d-block text-end"><i>' + datetimeFormatter(new Date(), 'datetime') + '</i>&nbsp;&nbsp;<i class="fas fa-eye d-none" id="chat-' + value['chat_id'] + '-read"></i><i class="fas fa-check" id="chat-' + value['chat_id'] + '-sent"></i></small></div>');

                                    $('#upload-imgs, #upload-files').val('');

                                    //remove preview section's files
                                    $('#chat-room-upload-file-preview .owl-carousel').remove();
                                    $('#chat-room-upload-file-preview div').addClass('d-none');
                                    $('#chat-room-upload-file-preview div').append('<div class="owl-carousel owl-theme"></div>');

                                    chatRoomScrollDown();
                                    temp_uploaded_files = [];
                                }
                            });
                        }
                    });
                }

                //message
                if ($('#chat-message').val()){
                    $.ajax({
                        url: '../../helpers/message.php',
                        data: {
                            message: $('#chat-message').val(),
                            rest_id: '<?=$restaurant_info['rest_id']?>',
                            send_msg_store: true
                        },
                        method: 'post',
                        async: false,
                        success: function(output) {
                            var json = $.parseJSON(output);

                            if (!json['error']){
                                $('#chat-content').append('<p class="chat-msgs align-self-end border border-2 rounded p-2 mb-1" style="background-color: #f0f0f0;" id="chat-' + json['chat_id'] + '">' + $('#chat-message').val() + '<small class="d-block text-end"><i>' + datetimeFormatter(new Date(), 'datetime') + '</i>&nbsp;&nbsp;<i class="fas fa-eye d-none" id="chat-' + json['chat_id'] + '-read"></i><i class="fas fa-check" id="chat-' + json['chat_id'] + '-sent"></i></small></p>');
                                $('#chat-message').val('');
                                $('#chat-message').focus();
                                chatRoomScrollDown();
                            }
                        }
                    });
                }
            });

            $('#share-btns-wrapper div').each(function() {
                var url = encodeURI(document.location.href);
                var title = encodeURI('Hi everyone, please check this out: ');
                var image = encodeURI('../../../../admin/uploads/profile_pic/' + '<?=$restaurant_info['rest_profile']?>');

                <?php if (isset($sharing_post_settings_data)) { ?>
                    <?php if (isset($sharing_post_settings_data['title'])) { ?>
                        <?php $sharing_post_settings_data['title'] = str_replace('&', '', $sharing_post_settings_data['title']); ?>
                        var title = encodeURI('<?=$sharing_post_settings_data['title']?>');
                    <?php } ?>
                    <?php if (isset($sharing_post_settings_data['image'])) { ?>
                        var image = encodeURI('../../../../admin/uploads/sharing_post_img/' + '<?=$sharing_post_settings_data['image']?>');
                    <?php } ?>
                <?php } ?>

                if ($(this).attr('id') == 'facebook'){
                    $(this).find('a').attr('href', `https://www.facebook.com/sharer.php?u=${url}`);
                } else if ($(this).attr('id') == 'twitter'){
                    $(this).find('a').attr('href', `https://twitter.com/share?url=${url}&text=${title}`);
                } else if ($(this).attr('id') == 'pinterest'){
                    $(this).find('a').attr('href', `https://pinterest.com/pin/create/bookmarklet/?media=${image}&url=${url}&description=${title}`);
                } else if ($(this).attr('id') == 'whatsapp'){
                    $(this).find('a').attr('href', `https://api.whatsapp.com/send?text=${title} ${url}`);
                }
            });

            $('#share-btns-wrapper div').click(function() {
                $.ajax({
                    url: '../../helpers/restaurant.php',
                    data: {
                        rest_id: '<?=$restaurant_info['rest_id']?>',
                        update_sharing_count: true
                    },
                    method: 'post'
                });
            });

            //update visitor
            $.ajax({
                url: '../../helpers/restaurant.php',
                data: {
                    rest_id: '<?=$restaurant_info['rest_id']?>',
                    update_visitor_count: true
                },
                method: 'post'
            })

            checkHasNewMessages();
        });

        $(window).on('activate.bs.scrollspy', function () {
            $('.dropdown-toggle.active').text($('.dropdown-item.active').text());
        });
    </script>
  </body>
</html>