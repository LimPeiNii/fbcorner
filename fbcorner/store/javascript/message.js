$(document).ready(function() {
    //update conversation list
    setInterval(function() {
        var last_chat_ids = {};
        var has_unseen_conversation_ids = {};
        $('#conversation-table tr').each(function() {
            var conversation_id = $(this).attr('id').substring($(this).attr('id').indexOf('-') + 1);
            var chat_id = $(this).find('.last-msg-wrapper span').attr('id').substring($(this).find('.last-msg-wrapper span').attr('id').lastIndexOf('-') + 1);
            last_chat_ids[conversation_id] = chat_id;
        });

        var counter = 0;
        $('#conversation-table tr .new-msg-total').each(function() {
            if ($(this).text() != '0'){
                var conversation_id1 = $(this).parentsUntil('tr').parent().attr('id').substring($(this).parentsUntil('tr').parent().attr('id').indexOf('-') + 1);
                has_unseen_conversation_ids[counter] = conversation_id1;
                counter++;
            }
        });

        $.ajax({
            url: '../../helpers/message.php',
            data: {
                last_chat_ids: JSON.stringify(last_chat_ids),
                has_unseen_conversation_ids: JSON.stringify(has_unseen_conversation_ids),
                update_conversation_list: true
            },
            method: 'post',
            success: function(output){
                var json = $.parseJSON(output);

                if (json['new_conversation'] || json['refresh'])
                    window.location.reload();
                else if (json['new_msgs']){
                    $.each(json['new_msgs'], function(conversation_id, info) {
                        var row = $('#con-' + conversation_id);
                        row.remove();
                        $('#conversation-table tbody').prepend(row);
                        $('#con-' + conversation_id + ' .new-msg-total').text(info['total_new_msgs']);
                        if (info['total_new_msgs'] > 0)
                            $('#con-' + conversation_id + ' .new-msg-total').removeClass('d-none');
                        
                        var parent = $('#con-' + conversation_id + ' .last-msg-wrapper').parent();
                        $('#con-' + conversation_id + ' .last-msg-wrapper').remove();

                        if (info['last_chat_info']['message']){
                            parent.append('<span class="d-flex m-3 mt-0 text-muted last-msg-wrapper"><span class="d-inline-block text-truncate"' + (info['total_new_msgs'] > 0 ? 'style="color: #48cb68; font-weight: bolder;"' : '') + 'id="last-msg-' + info['last_chat_info']['chat_id'] + '">' + info['last_chat_info']['message'] + '</span></span>');                                   
                        } else {
                            parent.append('<span class="d-flex m-3 mt-0 text-muted last-msg-wrapper"><i class="fas fa-file m-2 ms-0" style="font-size: 20px;"></i><span class="d-inline-block text-truncate align-self-center"' + (info['total_new_msgs'] > 0 ? 'style="color: #48cb68; font-weight: bolder;"' : '') + 'id="last-msg-' + info['last_chat_info']['chat_id'] + '">' + info['last_chat_info']['file'].substring((info['last_chat_info']['file']).indexOf('-') + 1) + '</span></span>');
                        }
                        
                        $('#con-' + conversation_id + ' .date-or-time').remove();
                        $('#con-' + conversation_id + ' .time-wrapper').prepend('<span class="align-self-end d-inline-block mb-0 mt-2 me-3 date-or-time">' + datetimeFormatter(new Date(info['last_chat_info']['sent_at']), 'time') + '</span>');
                    });
                }
            }
        });
    }, 1000);
});