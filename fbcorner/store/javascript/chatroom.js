var temp_uploaded_files = new Array();

function showHideChatRoomUploadBtns(){
    $('#upload-files-btns button').toggle(); 
    $('#upload-files-btns button').toggleClass('popout-animation');
}

function chatRoomScrollDown(){
    $('#chat-content-wrapper').scrollTop($('#chat-content').height());
}

function previewFiles(element){
    var upload_file = element[0].files;
    var allowed_ext = ["pdf", "doc", "docx", "xls", "xlsx", "txt"]; 
    var error = false;
    for (var i=0; i<upload_file.length; i++){
        var file_name = upload_file[i].name;
        if ($.inArray(file_name.substring(file_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
            error = true;
            $('#upload-files').val('');
            return 'You can only select files with type "pdf", "doc", "docx", "xls", "xlsx", "txt"!';
        }
    }

    if (!error) {
        $('#chat-room-upload-file-preview .owl-carousel').trigger('destroy.owl.carousel');

        for (var i=0; i<upload_file.length; i++){
            var uploaded_file_names = temp_uploaded_files.map(item => item['name']);

            if ($.inArray(upload_file[i].name, uploaded_file_names) == -1){
                temp_uploaded_files.push(upload_file[i]);
                
                var file_name = upload_file[i].name;
                if (file_name.length > 6){
                    file_name = file_name.substring(0, 6) + '...';
                }

                $('#chat-room-upload-file-preview .owl-carousel').append('<span class="item bg-light d-flex flex-column text-center position-relative" style="width: 80px; height: 80px;"><span class="position-absolute overlay-text d-flex start-0 end-0 bottom-0 top-0 bg-dark text-white" style="opacity: 0; font-size: 0.8rem;"><span class="align-self-center text-center w-100">Remove</span></span><i class="fas fa-file m-2" style="font-size: 30px; margin-top: 0.7rem !important;"></i><small class="mx-2">' + file_name + '</small></span>');
            }
        }

        chatRoomPreviewFiles();
        $('#chat-room-upload-file-preview .owl-carousel').parent().removeClass('d-none');
    }

    return '';
}

function previewImages(element){
    var upload_img = element[0].files;
    var allowed_ext = ["jpg", "jpeg", "png"]; 
    var error = false;
    for (var i=0; i<upload_img.length; i++){
        var img_name = upload_img[i].name;
        if ($.inArray(img_name.substring(img_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
            error = true;
            $('#upload-imgs').val('');
            return 'You can only select files with type "jpg", "jpeg", "png"!';
        }
    }

    if (!error) {
        for (var i=0; i<upload_img.length; i++){
            var uploaded_file_names = temp_uploaded_files.map(item => item['name']);

            if ($.inArray(upload_img[i].name, uploaded_file_names) == -1){
                temp_uploaded_files.push(upload_img[i]);
                let reader = new FileReader();
                reader.onload = function(event){
                    $('#chat-room-upload-file-preview .owl-carousel').append('<span class="h-100 d-inline-block position-relative chat-img-span"><img class="item d-inline-block" style="width: 80px; height: 80px;" src="' + event.target.result + '"><span class="position-absolute overlay-text d-flex start-0 end-0 bottom-0 top-0 bg-dark text-white" style="opacity: 0; font-size: 0.8rem;"><span class="align-self-center text-center w-100">Remove</span></span></span>');
                    $('#chat-room-upload-file-preview .owl-carousel').trigger('destroy.owl.carousel');
                    chatRoomPreviewFiles();
                }
                reader.readAsDataURL(upload_img[i]);
            }
        }
        $('#chat-room-upload-file-preview .owl-carousel').parent().removeClass('d-none');
    }

    return '';
}

$(document).ready(function() {
    $('#chat-room').on('click', function(event) {
        if (!$(event.target).closest('#btn-plus, #upload-img-btn, #upload-file-btn, #upload-imgs, #upload-files').length) {
            if ($('#upload-img-btn').css('display') != 'none'){
                showHideChatRoomUploadBtns();
            }
        }
    });

    //remove files from preview
    $(document).on('click', '#chat-room-upload-file-preview .owl-item', function() {
        var index = $('#chat-room-upload-file-preview .owl-item').index($(this));
        temp_uploaded_files.splice(index, 1);
        $(this).remove();
        $('#chat-room-upload-file-preview .owl-carousel').trigger('destroy.owl.carousel');
        chatRoomPreviewFiles();

        if (temp_uploaded_files.length == 0){
            $('#chat-room-upload-file-preview .owl-carousel').parent().addClass('d-none');
        }
    })

    //scroll to bottom
    $('#scroll-to-bottom').click(function() {
        chatRoomScrollDown();
        $(this).hide();
        $('#has-msg-symbol').addClass('d-none');
    })

    //update online status in chat room every 5s
    setInterval(chatOnlineStatusUpdate, 5000);

    //update chat read status every 500ms
    setInterval(chatReadStatusUpdate, 500);

    //update new chats every 500ms
    setInterval(newChatsUpdate, 500);

    //show scroll to bottom button when not at the bottom
    var scrolling = false;
    $('#chat-content-wrapper').on('scroll', function() {
        scrolling = true;
    });
    setInterval(function() {
        var scroll_bottom = $('#chat-content').height() - $('#chat-content-wrapper').height() - $('#chat-content-wrapper').scrollTop();

        if (scrolling && scroll_bottom > 0) {
            $('#scroll-to-bottom').show();
        } else if (scrolling){
            $('#scroll-to-bottom').hide();
            $('#has-msg-symbol').addClass('d-none');
        }

        scrolling = false;
    }, 250);
});