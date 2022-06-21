<?php 

    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/message.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'message/message', 'access_permission');
    
        if ((int)$has_permission['has_permission']){
            $conversations = getConversationsByRestID($connection, $_SESSION['login_rest_id']);
            foreach ($conversations as $key => $conversation){
                $total_result = getConversationChatsTotal($connection, $conversation['conversation_id'], ['seen' => 0, 'receiver_id' => $_SESSION['login_rest_id']]);
                $conversations[$key]['unseen_total'] = $total_result['COUNT(*)'];
            }
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
            input[type=checkbox]{
                transform: scale(1.2);
            }

            table{
                border: 1px solid black !important;
            }

            tr{
                border-color: #dee2e6 !important;
            }
            
            tbody{
                border-width: 2px;
            }

            .text-truncate{
                width: 67vw !important;
            }

            @media(max-width: 1300px){
                .text-truncate{
                    width: 60vw !important;
                }
            }

            @media(max-width: 1000px){
                .text-truncate{
                    width: 55vw !important;
                }
            }

            @media(max-width: 970px){
                .text-truncate{
                    width: 50vw !important;
                }
            }

            @media(max-width: 900px){
                .text-truncate{
                    width: 45vw !important;
                }
            }

            @media(max-width: 780px){
                .text-truncate{
                    width: 40vw !important;
                }
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
                <button type="button" class="btn btn-secondary float-end ms-2" onclick="showCustomerListModal()" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Message
                </h3>

                <?php if (count($conversations) > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="white-space: nowrap;" id="conversation-table">  
                    <tbody>
                            <?php foreach ($conversations as $conversation) { ?>
                                <tr id="con-<?=$conversation['conversation_id']?>">
                                    <td style="cursor: pointer;">
                                        <a href="chatroom.php?cus_id=<?=$conversation['cus_guest_id']?>">
                                        <div class="d-flex">
                                            <img src="../../../../store/uploads/profile_pic/<?=empty($conversation['profile_pic']) ? 'default_pp.png' : $conversation['profile_pic']?>" height="80" width="80" class="m-3 rounded-circle" style="border: 3px solid #d0efff;">
                                            <div class="flex-grow-1 d-flex">
                                                <?php 
                                                    if (!empty($conversation['firstname'])){
                                                        $name = $conversation['firstname'] . ' ' . $conversation['lastname'];
                                                    } else {
                                                        $name = 'Guest ' . substr($conversation['cus_guest_id'], strlen($_SESSION['login_rest_id']) + 1);
                                                    }
                                                ?>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex">
                                                        <span class="fw-bold font-size-28 d-inline-block m-3 mb-2"><?=$name?></span>
                                                    </div>
                                                    <span class="d-flex m-3 mt-0 text-muted last-msg-wrapper"><?=empty($conversation['message']) ? '<i class="fas fa-file m-2 ms-0" style="font-size: 20px;"></i><span class="d-inline-block text-truncate align-self-center"' . ($conversation['unseen_total'] == 0 ? '' : ' style="color: #48cb68; font-weight: bolder;"') . ' id="last-msg-' . $conversation['chat_id'] . '">' . substr($conversation['file'], strpos($conversation['file'], '-') + 1) . '</span>' : '<span class="d-inline-block text-truncate"' . ($conversation['unseen_total'] == 0 ? '' : ' style="color: #48cb68; font-weight: bolder;"') . ' id="last-msg-' . $conversation['chat_id'] . '">' . $conversation['message'] . '</span>'?></span>
                                                </div>
                                            </div>
                                            <div class="d-flex time-wrapper flex-column">
                                                <span class="align-self-end d-inline-block mb-0 mt-2 me-3 date-or-time">
                                                <?php $date = date('d/m/Y', strtotime($conversation['sent_at'])); ?>
                                                <?php if ($date == date('d/m/Y')) { ?>
                                                    <?=date('h:i a', strtotime($conversation['sent_at']))?>
                                                <?php } else { ?>
                                                    <?=date('d/m/Y', strtotime($conversation['sent_at']))?><br>
                                                <?php } ?>
                                                </span>
                                                <span class="badge m-3 bg-primary rounded-circle align-self-end font-size-20 new-msg-total<?=$conversation['unseen_total'] == 0 ? ' d-none' : '' ?>"><?=$conversation['unseen_total']?></span>
                                            </div>
                                        </div>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                    <div class="w-100 text-center my-5 alert alert-info">
                        <i class="fas fa-comments" style="font-size: 40px;"></i>&nbsp;&nbsp;
                        <span style="line-height: 50px; font-size: 30px;">
                            No message history.
                        </span>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="text-center mt-3">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <div class="modal fade p-0" id="customerListModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Please Select a Customer</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        

                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="add-btn" disabled><a href="#" class="text-white">Add</a></button>
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script src="../../../../store/javascript/message.js"></script>

        <script>
            function showCustomerListModal(){
                //update customer list
                $.ajax({
                    url: '../../helpers/message.php',
                    data: {get_customer_list: true},
                    method: 'post',
                    success: function(output) {
                        var json = $.parseJSON(output);

                        var html = '';
                
                        if (json.length > 0){
                            html += '<select class="form-select" id="customer">';
                            html += '<option value="">-- Please Select a Customer --</option>';
                            $.each(json, function(key, value) {
                                html += '<option value="' + value['cus_id'] + '">' + value['name'] + ' ' + value['email'] + '</option>';
                            });
                            html += '</select>';
                        } else {
                            html = '<div class="text-center">Your restaurant has no existing customer.</div>';
                        }

                        $('#customerListModal .modal-body').html(html);
                        $('#customerListModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });
            }

            $(document).ready(function() {
                $(document).on('change', '#customer', function() {
                    if ($(this).val()){
                        $('#add-btn').prop('disabled', false);
                        $('#add-btn a').attr('href', 'chatroom.php?cus_id=' + $(this).val());
                    } else {
                        $('#add-btn').prop('disabled', true);
                        $('#add-btn a').attr('href', '#');
                    }
                });

                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                });
            })
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