<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/message.php';
        include_once '../../helpers/user.php';
        include_once '../../../../store/php/helpers/customer.php';
        include_once '../../../../store/php/helpers/message.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'message/message', 'modify_permission');
    
        if ((int)$has_permission['has_permission']){
            if (!isset($_GET['cus_id'])){
                header("Location: message.php");
                exit;
            }

            if ($_GET['cus_id'][0] == 'C'){
                $customer = getCustomer($connection, $_GET['cus_id']);
                $name = $customer['firstname'] . ' ' . $customer['lastname'];
                $cus_login_activity = getCustomerLoginActivity($connection, $_GET['cus_id']);
            } else {
                $name = 'Guest ' . substr($_GET['cus_id'], strlen($_SESSION['login_rest_id']) + 1);
                $guest_online_status = getGuestOnlineStatus($connection, $_GET['cus_id'], $_SESSION['login_rest_id']);
            }

            $chats = getCustomerChats($connection, $_GET['cus_id'], $_SESSION['login_rest_id']);
        }
        
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            .chat-room-header-footer{
                background: linear-gradient(90.05deg, #7400AB -9.36%, #C968E5 59.69%, #F9F3FC 133.7%);
                height: 77.4px;
            }

            .content{
                padding-left: 80px !important;
            }

            .content.active{
                padding-left: 240px !important;
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

            .chat-room-header-footer button:active:enabled, .chat-room-header-footer a:active, #upload-files-btns button{
                background-color: rgba(36, 36, 36, 0.58);
                border-radius: 10px !important;
            }

            .chat-room-header-footer button:disabled, #chat-message:disabled{
                opacity: 0.5 !important;
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

            @media (max-width: 482px){
                .side-bar-body .side-bar ~ .content {
                    padding-left: 0 !important;
                }
            }
        </style>
    <?php } ?>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar_2.php'; ?>

    <!-- side bar -->
    <div class="side-bar-body d-flex" style="min-height: calc(100vh - 76px);">
        <?php include_once '../../common/side_bar.php'; ?>

        <!-- content -->
        <?php if ((int)$has_permission['has_permission']){ ?>
            <div class="content w-100" style="min-height: calc(100vh - 76px);">
                <div id="chat-room" class="position-relative" style="min-height: 100%">
                <div class="bg-light" id="chat-room-content">
                    <div class="chat-room-header-footer d-flex">
                        <a href="message.php" class="h-100 d-flex"><button type="button" class="mx-3 text-white align-self-center"><i class="fas fa-arrow-left"></i></button></a>
                        <img src="../../../../store/uploads/profile_pic/<?=isset($customer) ? $customer['profile_pic'] : 'default_pp.png'?>" height="55" width="55" class="rounded-circle align-self-center m-3 ms-0" style="border: 3px solid #d0efff">
                        <div class="align-self-center flex-grow-1 fw-bold text-white font-century">
                            <div class="d-flex flex-column">
                                <?=$name?>
                                <span class="d-flex" id="chat-room-online-status">
                                    <?php if (isset($guest_online_status)) { ?>
                                        <i class="fas fa-circle align-self-center<?=$guest_online_status['online'] == 1 ? '' : ' d-none' ?>" style="font-size: 10px; color: #31d459;"></i>
                                        <small class="ms-2 text-white<?=$guest_online_status['online'] == 1 ? '' : ' d-none' ?>" style="font-weight: 500 !important;">Active</small>
                                    <?php } else if (empty($cus_login_activity)){ ?>
                                        <i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i>
                                        <small class="ms-2 text-white" style="font-weight: 500 !important;">Inactive</small>
                                    <?php } else if (empty($cus_login_activity['logout_time'])){ ?>
                                        <i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i>
                                        <small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>
                                    <?php } else { ?>
                                        <?php $online_status = calcTimeAgo(strtotime($cus_login_activity['logout_time'])); ?>
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
                                    <div class="chat-msgs <?=$chat['sender_id'] == $_SESSION['login_rest_id'] ? 'align-self-end' : 'align-self-start color-2' ?> border border-2 rounded p-2 mb-1"<?=$chat['sender_id'] == $_SESSION['login_rest_id'] ? ' style="background-color: #f0f0f0;"' : ' style="border-color: #edd8ed !important;"' ?> id="chat-<?=$chat['chat_id']?>">
                                        <a href="../../../<?=$chat['sender_id'] == $_SESSION['login_rest_id'] ? '' : '../store/' ?>uploads/chat_files/<?=$chat['file']?>" download="<?=$file_name?>">
                                            <div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;">
                                                <i class="fas fa-file m-2" style="font-size: 25px;"></i>
                                                <span class="me-2 align-self-center text-truncate"><?=$file_name?></span>
                                            </div>
                                        </a>
                                        <small class="d-block text-end">
                                            <i><?=date('d/m/Y h:i a', strtotime($chat['sent_at']))?></i>
                                            <?php if ($chat['sender_id'] == $_SESSION['login_rest_id']) { ?>
                                            &nbsp;
                                            <i class="fas fa-eye<?=$chat['seen'] == '0' ? ' d-none' : '' ?>" id="chat-<?=$chat['chat_id']?>-read"></i>
                                            <i class="fas fa-check<?=$chat['seen'] == '1' ? ' d-none' : '' ?>" id="chat-<?=$chat['chat_id']?>-sent"></i>
                                            <?php } ?>
                                        </small>
                                    </div>
                                <?php } else { ?>
                                    <p class="chat-msgs <?=$chat['sender_id'] == $_SESSION['login_rest_id'] ? 'align-self-end' : 'align-self-start color-2' ?> border border-2 rounded p-2 mb-1"<?=$chat['sender_id'] == $_SESSION['login_rest_id'] ? ' style="background-color: #f0f0f0;"' : ' style="border-color: #edd8ed !important;"' ?> id="chat-<?=$chat['chat_id']?>">
                                        <?=$chat['message']?>
                                        <small class="d-block text-end">
                                            <i><?=date('d/m/Y h:i a', strtotime($chat['sent_at']))?></i>
                                            <?php if ($chat['sender_id'] == $_SESSION['login_rest_id']) { ?>
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
                            <button type="button" id="btn-plus" class="text-white px-3 mx-2" style="padding-top: 0.6rem; padding-bottom: 0.65rem;" onclick="showHideChatRoomUploadBtns();"<?=(isset($guest_online_status) && $guest_online_status['online'] == 1 || !isset($guest_online_status)) ? '' : ' disabled' ?>><i class="fas fa-plus"></i></button>
                        </div>
                        <input type="file" id="upload-imgs" class="d-none" accept="image/*" capture="" multiple>
                        <input type="file" id="upload-files" class="d-none" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" multiple>
                        <textarea class="form-control my-3 border border-0" id="chat-message" style="border-radius: 10px; line-height: 30px;" placeholder="Write a message..."<?=(isset($guest_online_status) && $guest_online_status['online'] == 1 || !isset($guest_online_status)) ? '' : ' disabled' ?>></textarea>
                        <div class="align-self-center">
                            <button type="button" id="send-msg-btn" class="text-white px-3 mx-2" style="padding-top: 0.6rem; padding-bottom: 0.65rem;"<?=(isset($guest_online_status) && $guest_online_status['online'] == 1 || !isset($guest_online_status)) ? '' : ' disabled' ?>><i class="bi bi-send-fill"></i></button>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        <?php } else { ?>
            <div class="text-center mt-3 w-100 p-4">
                <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
            </div>
        <?php } ?>
    </div>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <?php if ((int)$has_permission['has_permission']){ ?>
        <script src="../../../../store/javascript/chatroom.js"></script>
        
        <script>
            function close_message() {
                $('.form-message').remove();
            }

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
                        cus_guest_id: '<?=$_GET['cus_id']?>',
                        update_online_status: true
                    },
                    method: 'post',
                    success: function(output) {
                        $('#chat-room-online-status').html(output);

                        if ('<?=$_GET['cus_id']?>'.substring(0, 1) != 'C' && $('#chat-room-online-status small').is(':hidden')){
                            $('#chat-message, #btn-plus, #send-msg-btn').prop('disabled', true);
                        }
                    }
                });
            }

            function chatReadStatusUpdate(){
                $.ajax({
                    url: '../../helpers/message.php',
                    data: {
                        cus_guest_id: '<?=$_GET['cus_id']?>',
                        update_read_status: true
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
                        cus_guest_id: '<?=$_GET['cus_id']?>',
                        update_new_chats: true
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

                                        $('#chat-content').append('<div class="chat-msgs align-self-start color-2 border border-2 rounded p-2 mb-1" style="border-color: #edd8ed !important;" id="chat-' + value['chat_id'] + '"><a href="../../../../store/uploads/chat_files/' + value['file'] + '" download="' + file_name + '"><div class="border border-2 rounded m-1 p-1 d-flex" style="background-color: white;"><i class="fas fa-file m-2" style="font-size: 25px;"></i><span class="me-2 align-self-center text-truncate">' + file_name + '</span></div></a><small class="d-block text-end"><i>' + value['sent_at'] + '</i></small></div>');
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
                        cus_guest_id: '<?=$_GET['cus_id']?>',
                        seen_message: true
                    },
                    method: 'post'
                });
            }

            $(document).ready(function() {
                $('html, body').animate({ scrollTop: $('body').height() }, 0);

                chatRoomScrollDown();

                $('#chat-message').focus();
                    
                seenMessage();

                $('#upload-files').change(function() {
                    $return_msg = previewFiles($('#upload-files'));
                    if ($return_msg != ''){
                        alert($return_msg);
                    }
                });

                $('#upload-imgs').change(function() {
                    $return_msg = previewImages($('#upload-imgs'));
                    if ($return_msg != ''){
                        alert($return_msg);
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

                        data.append('cus_guest_id', '<?=$_GET['cus_id']?>');
                        data.append('send_file', true);

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
                                cus_guest_id: '<?=$_GET['cus_id']?>',
                                send_msg: true
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
    <?php } ?>
</body>
</html>

<?php

    } else {
        header("Location: ../signin_signup/sign_in.php");
        exit;
    }

?>