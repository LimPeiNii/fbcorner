<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/restaurant.php';
    include_once '../../helpers/message.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
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

    $restaurant = getRestaurant($connection, $_GET['rest_id']);
    $online_login_activities = getRestaurantLoginActivity($connection, $_GET['rest_id'], ['online' => true]);
    $rest_login_activity = getRestaurantLoginActivity($connection, $_GET['rest_id'], ['last_login_activity' => true]);
    if ($rest_login_activity){
        $rest_login_activity = $rest_login_activity[0];
    }

    if (isset($_SESSION['login_cus_id']))
        $chats = getCustomerChats($connection, $_SESSION['login_cus_id'], $_GET['rest_id']);
    else
        $chats = [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
    
    <style>
        body{
            height: 100%;
        }
        
        .chat-room-header-footer{
            background: linear-gradient(90.05deg, #7400AB -9.36%, #C968E5 59.69%, #F9F3FC 133.7%);
            height: 77.4px;
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

        .chat-room-header-footer button:active, .chat-room-header-footer a:active, #upload-files-btns button{
            background-color: rgba(36, 36, 36, 0.58);
            border-radius: 10px !important;
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
    </style>

</head>
<body>
    <?php include_once '../../common/navigation_bar.php'; ?>

    <?php include_once '../../common/signin_signup.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
        <div class="w-100" style="min-height: calc(100vh - 76px);">
            <div id="chat-room" class="position-relative" style="min-height: inherit">
                <div class="bg-light" id="chat-room-content">
                    <div class="chat-room-header-footer d-flex">
                        <a href="<?=isset($_SESSION['login_cus_id']) ? 'my_message.php' : '../restaurant/profile.php?visit_rest_id=' . $_GET['rest_id']?>" class="h-100 d-flex"><button type="button" class="mx-3 text-white align-self-center"><i class="fas fa-arrow-left"></i></button></a>
                        <img src="../../../../admin/uploads/profile_pic/<?=$restaurant['rest_profile']?>" height="55" width="55" class="rounded-circle align-self-center m-3 ms-0" style="border: 3px solid #d0efff">
                        <div class="align-self-center flex-grow-1 fw-bold text-white font-century">
                            <div class="d-flex flex-column">
                                <?=$restaurant['rest_name']?>
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
                    </div>
                    <div id="chat-content-wrapper" class="w-100 position-absolute" style="overflow-y: auto; min-height: calc((100vh - 76px) * 0.7); bottom: 77.4px; top: 77.4px;">
                        <div id="chat-content" style="background-color: white;" class="p-3 d-flex flex-column">
                            <?php foreach ($chats as $chat) { ?>
                                <?php if (!empty($chat['file'])) { ?>
                                    <?php $file_name = substr($chat['file'], (strpos($chat['file'],'-')) + 1); ?>
                                    <div class="chat-msgs <?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? 'align-self-end' : 'align-self-start color-2' ?> border border-2 rounded p-2 mb-1"<?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? ' style="background-color: #f0f0f0;"' : ' style="border-color: #edd8ed !important;"' ?> id="chat-<?=$chat['chat_id']?>">
                                        <a href="../../../<?=$chat['sender_id'] == $_SESSION['login_cus_id'] ? '' : '../admin/' ?>uploads/chat_files/<?=$chat['file']?>" download="<?=$file_name?>">
                                            <div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;">
                                                <i class="fas fa-file m-2" style="font-size: 25px;"></i>
                                                <span class="me-2 align-self-center text-truncate"><?=$file_name?></span>
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
                        </div>
                    </div>
                    <div id="upload-files-btns" class="position-absolute d-flex flex-column" style="bottom: 80px">
                        <button type="button" id="upload-img-btn" class="text-white px-3 m-2 mb-1" style="display: none; padding-top: 0.6rem; padding-bottom: 0.65rem; background-color: rgba(36, 36, 36); z-index: 3;" data-bs-toggle="tooltip" data-bs-placement="right" title="" onclick="$('#upload-imgs').click(); showHideChatRoomUploadBtns();" data-bs-original-title="Images" aria-label="Images"><i class="fas fa-images"></i></button>
                        <button type="button" id="upload-file-btn" class="text-white px-3 m-2" style="display: none; padding-top: 0.6rem; padding-bottom: 0.65rem; background-color: rgba(36, 36, 36); z-index: 3;" data-bs-toggle="tooltip" data-bs-placement="right" title="" onclick="$('#upload-files').click(); showHideChatRoomUploadBtns();" data-bs-original-title="Files" aria-label="Files"><i class="fas fa-file-alt"></i></button>
                    </div>
                    <div class="position-absolute" style="bottom: 90px; right: 25px;">
                        <button type="button" id="scroll-to-bottom" class="position-relative px-3 m-2 mb-1 rounded-circle text-white" style="padding-top: 0.7rem; display: none; padding-bottom: 0.65rem; z-index: 2; background-color: #dc9dee;">
                            <i class="fas fa-angle-down" style="font-size: 20px;"></i>
                            <span class="position-absolute d-none translate-middle p-2 bg-danger border border-light rounded-circle" id="has-msg-symbol" style="left: 90%; top: 15%">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        </button>
                    </div>
                    <div id="chat-room-upload-file-preview" class="position-absolute w-100 bg-dark bg-opacity-50" style="bottom: 77.4px; z-index: 2;">
                        <div class="px-5 py-3 d-none">
                            
                        <div class="owl-carousel owl-theme"></div></div>
                    </div>
                    <div class="chat-room-header-footer d-flex w-100 position-absolute bottom-0">
                        <div class="align-self-center">
                            <button type="button" id="btn-plus" class="text-white px-3 mx-2" style="padding-top: 0.6rem; padding-bottom: 0.65rem;" onclick="showHideChatRoomUploadBtns();"><i class="fas fa-plus"></i></button>
                        </div>
                        <input type="file" id="upload-imgs" class="d-none" accept="image/*" capture="" multiple>
                        <input type="file" id="upload-files" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple>
                        <textarea class="form-control my-3 border border-0" id="chat-message" style="border-radius: 10px; line-height: 30px;" placeholder="Write a message..."></textarea>
                        <div class="align-self-center">
                            <button type="button" id="send-msg-btn" class="text-white px-3 mx-2" style="padding-top: 0.6rem; padding-bottom: 0.65rem;"><i class="bi bi-send-fill"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>

    <script src="../../../javascript/chatroom.js"></script>

    <script>
        function chatRoomPreviewFiles(){
            $('#chat-room-upload-file-preview .owl-carousel').owlCarousel({
                nav: true,
                dots: false,
                margin: 10,
                loop: ( $('#chat-room-upload-file-preview .owl-carousel .items').length > 3 ),
                responsive: {   //how many items to display based on viewport size
                    360: {
                        items: 2
                    },
                    460:{
                        items: 3
                    },
                    600: {
                        items: 4
                    },
                    700:{
                        items: 5
                    },
                    1000: {
                        items: 7
                    },
                    1500: {
                        items: 11
                    },
                    2000: {
                        items: 14
                    }
                }
            });
        }

        function chatOnlineStatusUpdate(){
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant['rest_id']?>',
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
                    rest_id: '<?=$restaurant['rest_id']?>',
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

        function newChatsUpdate(){
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant['rest_id']?>',
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

                                    $('#chat-content').append('<div class="chat-msgs align-self-start color-2 border border-2 rounded p-2 mb-1" style="border-color: #edd8ed !important;" id="chat-' + value['chat_id'] + '"><a href="../../../../admin/uploads/chat_files/' + value['file'] + '" download="' + file_name + '"><div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;"><i class="fas fa-file m-2" style="font-size: 25px;"></i><span class="me-2 align-self-center text-truncate">' + file_name + '</span></div></a><small class="d-block text-end"><i>' + value['sent_at'] + '</i></small></div>');
                                    has_new_chat = true;
                                }
                            }
                        });

                        if (has_new_chat){
                            seenMessage();
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
            $.ajax({
                url: '../../helpers/message.php',
                data: {
                    rest_id: '<?=$restaurant['rest_id']?>',
                    seen_message: true
                },
                method: 'post'
            });
        }

        $(document).ready(function() {
            chatRoomScrollDown();

            $('#chat-message').focus();
                
            seenMessage();

            $('#upload-files').change(function() {
                $return_msg = previewFiles($('#upload-files'));
                if ($return_msg != ''){
                    slideInMsg('error', $return_msg);
                }
            });

            $('#upload-imgs').change(function() {
                $return_msg = previewImages($('#upload-imgs'));
                if ($return_msg != ''){
                    slideInMsg('error', $return_msg);
                }
            });

            $(window).on('unload', function() {
                //remove guest id
                var data = new FormData();
                data.append('rest_id', '<?=$_GET['rest_id']?>');
                data.append('remove_guest_id', true);

                navigator.sendBeacon('../../helpers/message.php', data);
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

                    data.append('rest_id', '<?=$restaurant['rest_id']?>');
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

                                    $('#chat-content').append('<div class="chat-msgs align-self-end border border-2 rounded p-2 mb-1" style="background-color: #f0f0f0;" id="chat-' + value['chat_id'] + '"><a href="../../../uploads/chat_files/' + value['name'] + '" download="' + file_name + '"><div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;"><i class="fas fa-file m-2" style="font-size: 25px;"></i><span class="me-2 align-self-center text-truncate">' + file_name + '</span></div></a><small class="d-block text-end"><i>' + datetimeFormatter(new Date(), 'datetime') + '</i>&nbsp;&nbsp;<i class="fas fa-eye d-none" id="chat-' + value['chat_id'] + '-read"></i><i class="fas fa-check" id="chat-' + value['chat_id'] + '-sent"></i></small></div>');

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
                            rest_id: '<?=$restaurant['rest_id']?>',
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
        });
    </script>
  </body>
</html>