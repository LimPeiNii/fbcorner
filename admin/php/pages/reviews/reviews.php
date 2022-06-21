<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/rating_review.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reviews/reviews', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reviews/reviews', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            $ratings_reviews = getRatingsReviews($connection, $_SESSION['login_rest_id'], ['order_by' => 'rr.review_modified_date', 'asc_desc' => 'DESC']);
            $rating_review_setting = getRatingReviewSetting($connection, $_SESSION['login_rest_id']);
            if ($rating_review_setting)
                $review_reply_options = json_decode($rating_review_setting[0]['value'], true)['reply_options'];
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews &VerticalLine; F&amp;B Corner</title>

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

            tr, th{
                border-color: #dee2e6 !important;
            }
            
            thead, tbody{
                border-width: 2px;
            }

            th{
                background-color: rgb(235, 235, 235) !important;
            }

            .offcanvas-backdrop{
                z-index: 1060;
            }

            .offcanvas{
                z-index: 1065;
                height: 50%;
            }

            .review-list{
                margin-top: 15px;
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
                <button type="button" id="btn-settings" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings"><i class="fas fa-cog font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <button type="button" onclick="autoReply();" class="btn bg-warning ms-2 float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Auto Reply" data-bs-original-title="Auto Reply" aria-label="Auto Reply" style="padding: 4.5px; padding-top: 0px; padding-bottom: 5px;"><i class="bi bi-reply-fill font-size-20 text-dark text-opacity-75 m-0 p-0" style="line-height: 30px; font-size: 35px;"></i></button>

                <button type="button" id="mark-as-unread" onclick="markReviewsAsUnread();" class="btn bg-info ms-2 float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark As Unread" data-bs-original-title="Mark As Unread" aria-label="Mark As Unread" style="padding-right: 3px; padding-left: 1.5px; padding-top: 7px; padding-bottom: 0px;">
                    <span class="fa-stack position-relative" style=" margin-bottom: 3px;">
                        <i class="bi bi-chat-left-fill fa-stack-2x text-white" style="line-height: 30px; font-size: 23px;"></i>
                        <i class="position-absolute bi bi-circle-fill fa-stack-1x fa-inverse text-dark text-opacity-75" style="font-size: 11px; margin-top: 1.5px; margin-left: 0.5px; width: fit-content; top: -14px; left: 25px;"></i>
                    </span>
                </button>

                <button type="button" id="mark-as-read" onclick="markReviewsAsRead();" class="btn bg-info float-end d-none" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark As Read" data-bs-original-title="Mark As Read" aria-label="Mark As Read" style="padding: 4.5px; padding-top: 2.5px; padding-bottom: 2.5px;"><i class="bi bi-check-all font-size-20 text-white m-0 p-0" style="line-height: 30px; font-size: 35px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Reviews
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive review-list">
                    <?php if (count($ratings_reviews) == 0 || count($ratings_reviews) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr class="align-middle">
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></th>
                                <th width="5%" class="text-center px-4">No.</th>
                                <th width="25%" class="px-3">Name</th>
                                <th width="25%" class="px-3">Rating</th>
                                <th width="30%" class="px-3">Last Modified Date & Time</th>
                                <th width="15%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                foreach ($ratings_reviews as $rating_review) { 
                                    $counter++;
                            ?>
                                <tr class="align-middle"<?=!empty($rating_review['read_date']) ? ' style="background-color: rgba(242,245,245,0.8);"' : ' style="font-weight: bolder;"'?>>
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="text" class="read_or_unread d-none" value="<?=!empty($rating_review['read_date']) ? 'read' : 'unread'?>"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$rating_review['order_id']?>"></td>
                                    <td width="5%" class="text-center"><?=$counter?></td>
                                    <td width="25%" class="px-3"><span style="color: blue; cursor: pointer;" onclick="viewCustomerDetails('<?=$rating_review['cus_id']?>');"><?=$rating_review['cus_name']?></span></td>
                                    <td width="25%" class="px-3">
                                        <?php for ($i=0; $i<$rating_review['rating']; $i++) { ?>
                                            <i class="fas fa-star text-danger"></i>
                                        <?php } ?>
                                        <?php for ($i=0; $i<(5-$rating_review['rating']); $i++) { ?>
                                            <i class="fas fa-star text-secondary"></i>
                                        <?php } ?>
                                    </td>
                                    <td class="px-3"><?=date('d/m/Y h:i a',strtotime($rating_review['review_modified_date']))?></td>
                                    <td width="15%" class="text-center">
                                    <?php if ($rating_review['reservation_id'] == '0') { ?>
                                        <button type="button" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$rating_review['order_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View Order"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                    <?php } else { ?>
                                        <button type="button" class="btn bg-success bg-opacity-75" onclick="viewPreOrderDetails(<?=$rating_review['order_id']?>, <?=$rating_review['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View Order"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                    <?php } ?>
                                        <button type="button" class="btn bg-primary bg-opacity-75" onclick="showReplyReviewModal(<?=$rating_review['order_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="Reply"><i class="fas fa-reply font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center mt-3">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>    
    </div>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <div class="modal fade p-0" id="settingsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Rating and Review Settings</h5>
                        <button type="button" class="btn-close close-settings" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="mb-4">
                                <div class="d-flex">
                                    <textarea id="reply-template-settings-item" placeholder="Write a reply..." class="form-control" rows="3"></textarea>
                                    <button type="button" class="btn btn-primary ms-2" style="width: 70px;" data-bs-toggle="tooltip" data-bs-placement="top" title="Add" data-bs-original-title="Add" aria-label="Add" onclick="addReplyToTemplate($('#reply-template-settings-item').val()); $('#reply-template-settings-item').val('')"><i class="fas fa-plus-circle font-size-28" style="line-height: 30px;"></i></button>
                                </div>
                                <small><div class="char-counter" style="color: grey;">2000 characters</div></small>
                            </div>
                            <div class="table-responsive">
                                <table id="reply-template-setting-table" class="w-100 mt-0 table table-striped" style="border-color: #ccc;">
                                    <thead style="border-color: #ccc;">
                                        <tr class="align-middle">
                                            <th style="width: 20px; background-color: white !important;" class="text-center">Auto Reply</td>
                                            <th class="px-3" style="background-color: white !important;">Reply Options</td>
                                            <th style="background-color: white !important;"></td>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle" style="border-color: #ccc;">
                                        <tr id="empty-table-row"><td colspan="3" height="77.5"></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-settings" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="btn-save-rating_review_setting">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade p-0" id="customerDetailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Customer Details</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade p-0" id="replyReviewModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reply to Review</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../helpers/rating_review.php" method="post" id="reply-to-review-form">
                            <input type="text" style="display: none;" name="submit_reply_review">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Rating</label>
                                <div id="rating_star_container">

                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="form-label fw-bold">Review</label>
                                <p id="review_container"></p>
                            </div>
                            <div>
                                <input type="number" style="display: none;" name="order_id">
                                <div class="mb-3 d-flex">
                                    <label for="earliest-time" class="form-label fw-bold d-inline-block flex-grow-1 align-self-end mb-0">Reply <span class="required-star">*</span></label>
                                    <?php if (isset($review_reply_options)) { ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-block" data-bs-toggle="offcanvas" data-bs-target="#offcanvasReplyTemplate" aria-controls="offcanvasBottom">Reply Template</button>
                                    <?php } ?>
                                </div>
                                <textarea class="form-control rounded-0" id="reply_review" name="reply_review" rows="6"></textarea>
                                <p class="d-block text-danger"></p>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" form="reply-to-review-form">Save Changes</button>
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div> 

        <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasReplyTemplate" aria-labelledby="offcanvasBottomLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Reply Templates</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body small p-0">
                <div class="list-group" id="reply-template-list">
                    <?php if (isset($review_reply_options)) { ?>
                        <?php foreach ($review_reply_options as $review_reply_option) { ?>
                        <div class="list-group-item list-group-item-action" style="cursor: pointer;" onclick="$('#replyReviewModal #reply_review').val('<?=$review_reply_option?>'); $('#offcanvasReplyTemplate').offcanvas('hide');">
                            <p class="mb-1"><?=$review_reply_option?></p>
                        </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="modal fade p-0" id="detailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Details</h5>
                        <button type="button" class="btn-close close-details-modal order-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-target="#orderModal" data-bs-toggle="modal">View Order</button>
                        <button type="button" class="btn btn-secondary close-details-modal order-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade p-0" id="orderModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Details</h5>
                        <button type="button" class="btn-close close-details-modal order-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-target="#detailsModal" data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-secondary close-details-modal order-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade p-0" id="foodModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Food Details</h5>
                        <button type="button" class="btn-close close-details-modal order-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-target="#orderModal" data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-secondary close-details-modal order-details-modal" data-bs-dismiss="modal">Close</button>
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
        <script>
            function close_message() {
                $('.form-message').remove();
            }

            function viewCustomerDetails(id) {
                $.ajax({ 
                    url: '../../../../store/php/helpers/customer.php',
                    data: { id: id },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        html += "<tr>";
                        html += "<td width='40%' class='px-3 fw-bold'>Profile Picture</td>";
                        html += "<td width='60%' class='p-3'><img width='80' height='80' class='me-2 rounded-circle' src='../../../../store/uploads/profile_pic/" + json['pp'] + "'></td>";
                        html += "</tr>";

                        for (i in json){
                            if (i != 'pp'){
                                html += "<tr>";
                                html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='60%' class='px-3 py-2'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</table>";
                    
                        $("#customerDetailsModal .modal-body").html(html);
                        $("#customerDetailsModal .modal-title").text("Customer Details (#" + id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#customerDetailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });
            }

            function markReviewsAsUnread(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (confirm("Are you sure you want to mark the review(s) as unread?")){
                            $.ajax({ 
                                url: '../../helpers/rating_review.php',
                                data: {
                                    selected_group: selected_data,
                                    mark_reviews_unread: true
                                },
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to modify the status of review(s).</div>').insertBefore('.review-list');
                    <?php } ?>
                }
            }

            function markReviewsAsRead(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (confirm("Are you sure you want to mark the review(s) as read?")){
                            $.ajax({ 
                                url: '../../helpers/rating_review.php',
                                data: {
                                    selected_group: selected_data,
                                    mark_reviews_read: true
                                },
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to modify the status of review(s).</div>').insertBefore('.review-list');
                    <?php } ?>
                }
            }

            function updateReadUnreadAllButtons(){
                var has_unread = false;
                $('.data-checkbox:checked').each(function() {
                    if ($(this).parent().find('.read_or_unread').val() == 'unread'){
                        has_unread = true;
                        return false;
                    }
                });
                if (!has_unread){
                    $('#mark-as-unread').removeClass('d-none');
                    $('#mark-as-read').addClass('d-none');
                } else {
                    $('#mark-as-read').removeClass('d-none');
                    $('#mark-as-unread').addClass('d-none');
                }
            }

            function addReplyToTemplate(reply_to_add){
                if (reply_to_add != ''){
                    if ($('#settingsModal #reply-template-setting-table tbody').find('#empty-table-row').length > 0){
                        $('#empty-table-row').remove();
                    }

                    var html = '';

                    html += '<tr>';
                    html +=     '<td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default auto-reply-checkbox"></td>';
                    html +=     '<td class="px-3"><span class="reply_option_added">' + reply_to_add + '</span></td>';
                    html +=     '<td width="60px"><button type="button" class="btn btn-danger m-2 remove-reply-option-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove" data-bs-original-title="Remove" aria-label="Remove"><i class="fas fa-minus-circle font-size-20" style="line-height: 30px;"></i></button></td>';
                    html += '</tr>';

                    $('#settingsModal #reply-template-setting-table tbody').append(html);
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    $('[data-bs-toggle="tooltip"]').on('click', function () {
                        $(this).tooltip('hide');
                    });
                }
            }

            function autoReply(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        $.ajax({
                            url: '../../helpers/rating_review.php',
                            data: {check_auto_reply_exist: true},
                            type: 'post',
                            success: function(output){
                                if (output != ''){
                                    if (confirm("Are you sure you want to auto reply the review(s) using the preset message?")){
                                        $.ajax({ 
                                            url: '../../helpers/rating_review.php',
                                            data: {
                                                selected_group: selected_data,
                                                auto_reply_message: output,
                                                auto_reply_reviews: true
                                            },
                                            type: 'post',
                                            success: function(){
                                                window.location.reload();
                                            }
                                        });
                                    }
                                } else {
                                    $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Please set the auto reply message in settings before using this feature!<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('.content .table-responsive');
                                }
                            }    
                        });
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to reply to the review(s).</div>').insertBefore('.review-list');
                    <?php } ?>
                }
            }
            
            function showReplyReviewModal(order_id){
                <?php if ((int)$has_modify_permission['has_permission']){ ?>                
                    $('#replyReviewModal .form-message').remove();
                    $('#replyReviewModal .text-danger').text('');
                    $('#replyReviewModal #reply_review').removeClass('red-box-shadow is-invalid');

                    $.ajax({
                        url: '../../helpers/rating_review.php',
                        data: {
                            order_id: order_id,
                            get_review_data: true
                        },
                        type: 'post',
                        success: function(output){
                            var json = $.parseJSON(output);

                            var html = '';

                            for (let i = 0; i < json['rating']; i++) {
                                html += '<i class="fas fa-star text-danger"></i>';
                            }
                            for (let i = 0; i < (5 - json['rating']); i++) {
                                html += '<i class="fas fa-star text-secondary"></i>';
                            }

                            $('#replyReviewModal #rating_star_container').html(html);

                            if (json['review']){
                                $('#replyReviewModal #review_container').text(json['review']);
                            } else {
                                $('#replyReviewModal #review_container').html('<span class="text-muted">No Review</span>');
                            }

                            if (json['reply']){
                                $('#replyReviewModal #reply_review').val(json['reply']);
                            } else {
                                $('#replyReviewModal #reply_review').val('');
                            }

                            $('#replyReviewModal input[name="order_id"]').val(order_id);

                            $("nav, .side-bar, .content").addClass('blur');
                            $('#replyReviewModal').modal('show');
                        }
                    });
                <?php } else { ?>
                    close_message();
                    $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to reply to the review(s).</div>').insertBefore('.review-list');
                <?php } ?>
            }

            function viewDetails(id) {
                $.ajax({ 
                    url: '../../helpers/order.php',
                    data: { modal_order_id: id },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        for (i in json){
                            if (i != 'Order'){
                                html += "<tr>";
                                html += "<td width='35%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='65%' class='px-3 py-2'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</table>";
                    
                        $("#detailsModal .modal-body").html(html);
                        $("#detailsModal .modal-title").text("Order Details (#" + id + ")");

                        html = '';

                        html += "<table class='table w-100 mt-1 align-middle' style='white-space: nowrap;'>";
                        html += "<tr>";
                        html += "<th width='30%' class='px-3 fw-bold'>Name</td>";
                        html += "<th width='20%' class='px-3 fw-bold py-2'>Price</td>";
                        html += "<th width='20%' class='px-3 fw-bold py-2'>Quantity</td>";
                        html += "<th width='30%' class='px-3 fw-bold py-2'>Subtotal</td>";
                        html += "</tr>";

                        var red_total = false;
                        for (i in json['Order']){
                            if (i != 'Total' && i != 'promo_code' && i != 'voucher'){
                                html += "<tr>";
                                for (j in json['Order'][i]){
                                    if (j != 'food_id' && j != 'Promotions'){
                                        if (typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined")
                                            red_total = true;

                                        html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + (j == 'Name' ? ' white-space: normal;' : '') + "'>" + (j == 'Name' ? '<a href="" style="color: blue;" data-bs-target="#foodModal" data-bs-toggle="modal" onclick="viewFoodItemDetails($(this)); ">' : '') + json['Order'][i][j] + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '&nbsp;<span class=text-danger>+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
                                    } else if (j == 'food_id')
                                        html += "<td class='d-none item_id'>" + json['Order'][i][j] + "</td>";
                                }
                                html += "</tr>";
                            } else if (i == 'Total') {
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end' style='border:0px;'>Total</td>";
                                html += "<td class='px-3" + (red_total ? ' text-danger' : '') + "' style='border:0px;'>" + json['Order'][i] + "</td>";
                                html += "</tr>";
                            } else if (i == 'promo_code'){
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end' style='color: RGB(0, 196, 49); border:0px;'>Promo Code (" + json['Order'][i]['name'] + ")</td>";
                                html += "<td class='px-3' style='border:0px;'>" + json['Order'][i]['discount'] + "</td>";
                                html += "</tr>";
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                                html += "<td class='px-3'>RM " + ((parseFloat(json['Order']['Total'].substring(3)) - parseFloat(json['Order'][i]['discount'].substring(5))).toFixed(2)) + "</td>";
                                html += "</tr>";
                            } else {
                                var grand_total_value = parseFloat(json['Order']['Total'].substring(3)) - parseFloat(json['Order'][i]['price'].substring(5));
                                if (grand_total_value < 0)
                                    grand_total_value = 0;

                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end' style='color: RGB(0, 196, 49); border:0px;'>Voucher</td>";
                                html += "<td class='px-3' style='border:0px;'>" + json['Order'][i]['price'] + "</td>";
                                html += "</tr>";
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                                html += "<td class='px-3'>RM " + grand_total_value.toFixed(2) + "</td>";
                                html += "</tr>";
                            }
                        }
                        
                        html += "</table>";

                        $("#orderModal .modal-body").html(html);
                        $("#orderModal .modal-title").text("Order Details (#" + id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#detailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });

                //update read date
                $.ajax({
                    url: '../../helpers/rating_review.php',
                    data: {
                        order_id: id,
                        mark_rating_review_read: true
                    },
                    method: 'post'
                });
            }

            function viewPreOrderDetails(id, reservation_id) {
                $.ajax({
                    url: '../../helpers/reservation.php',
                    data: { id: reservation_id },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        for (i in json){
                            html += "<tr>";
                            html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                            html += "<td width='60%' class='px-3 py-2'>" + json[i] + "</td>";
                            html += "</tr>";
                        }

                        html += "</table>";
                    
                        $("#detailsModal .modal-body").html(html);
                        $("#detailsModal .modal-title").text("Reservation Details (#" + reservation_id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#detailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });

                $.ajax({ 
                    url: '../../helpers/order.php',
                    data: { modal_order_id: id },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = '';

                        html += "<table class='table w-100 mt-1 align-middle' style='white-space: nowrap'>";
                        html += "<tr>";
                        html += "<th width='30%' class='px-3 fw-bold'>Name</td>";
                        html += "<th width='20%' class='px-3 fw-bold py-2'>Price</td>";
                        html += "<th width='20%' class='px-3 fw-bold py-2'>Quantity</td>";
                        html += "<th width='30%' class='px-3 fw-bold py-2'>Subtotal</td>";
                        html += "</tr>";

                        var red_total = false;
                        for (i in json['Order']){
                            if (i != 'Total' && i != 'promo_code' && i != 'voucher'){
                                html += "<tr>";
                                for (j in json['Order'][i]){
                                    if (j != 'food_id' && j != 'Promotions'){
                                        if (typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined")
                                            red_total = true;

                                        html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + (j == 'Name' ? ' white-space: normal;' : '') + "'>" + (j == 'Name' ? '<a href="" style="color: blue;" data-bs-target="#foodModal" data-bs-toggle="modal" onclick="viewFoodItemDetails($(this)); ">' : '') + json['Order'][i][j] + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '&nbsp;<span class=text-danger>+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
                                    } else if (j == 'food_id')
                                        html += "<td class='d-none item_id'>" + json['Order'][i][j] + "</td>";
                                }
                                html += "</tr>";
                            } else if (i == 'Total') {
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end' style='border:0px;'>Total</td>";
                                html += "<td class='px-3" + (red_total ? ' text-danger' : '') + "' style='border:0px;'>" + json['Order'][i] + "</td>";
                                html += "</tr>";
                            } else if (i == 'promo_code'){
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end' style='color: RGB(0, 196, 49); border:0px;'>Promo Code (" + json['Order'][i]['name'] + ")</td>";
                                html += "<td class='px-3' style='border:0px;'>" + json['Order'][i]['discount'] + "</td>";
                                html += "</tr>";
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                                html += "<td class='px-3'>RM " + ((parseFloat(json['Order']['Total'].substring(3)) - parseFloat(json['Order'][i]['discount'].substring(5))).toFixed(2)) + "</td>";
                                html += "</tr>";
                            } else {
                                var grand_total_value = parseFloat(json['Order']['Total'].substring(3)) - parseFloat(json['Order'][i]['price'].substring(5));
                                if (grand_total_value < 0)
                                    grand_total_value = 0;

                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end' style='color: RGB(0, 196, 49); border:0px;'>Voucher</td>";
                                html += "<td class='px-3' style='border:0px;'>" + json['Order'][i]['price'] + "</td>";
                                html += "</tr>";
                                html += "<tr>";
                                html += "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                                html += "<td class='px-3'>RM " + grand_total_value.toFixed(2) + "</td>";
                                html += "</tr>";
                            }
                        }
                                
                        html += "</table>";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        for (i in json){
                            if (jQuery.inArray(i, ['Remarks', 'Special Request', 'Created Date', 'Modified Date', 'Rating', 'Review', 'Reply', 'Status']) !== -1){
                                html += "<tr>";
                                html += "<td width='30%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='70%' class='px-3 py-2'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</table>";
                        

                        $("#orderModal .modal-body").html(html);
                        $("#orderModal .modal-title").text("Order Details (#" + id + ")");
                    }
                });

                //update read date
                $.ajax({
                    url: '../../helpers/rating_review.php',
                    data: {
                        order_id: id,
                        mark_rating_review_read: true
                    },
                    method: 'post'
                });
            }

            function viewFoodItemDetails(element){
                $.ajax({ 
                    url: '../../helpers/food_menu.php',
                    data: { id: element.parent().parent().find('.item_id').text() },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        html += "<tr>";
                        html += "<td width='30%' class='px-3 fw-bold'>Image</td>";
                        html += "<td width='70%' class='p-3'><img style='min-width: 100px; max-width: 120px;' height='100' src='../../../uploads/food_menu_photo/" + json['Image'] + "'></td>";
                        html += "</tr>";

                        for (i in json){
                            if (i != 'Image' && i != 'Ingredient' && i != 'Promotions'){
                                html += "<tr>";
                                html += "<td width='30%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='70%' class='px-3 py-2'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "<tr>";
                        html += "<td width='30%' class='px-3 fw-bold'>Promotions</td>";
                        html += "<td width='70%' class='px-3 py-2'>"
                        html += "<table style='border: 0px solid black !important;'>"
                        html += "<tbody style='border: 0px solid black !important;'>"

                        if (json['Promotions']){
                            for (j in json['Promotions']){
                                html += "<tr class='align-top'>";
                                html += "<td width='3%' class='px-1 py-1'>" + (parseInt(j)+1).toString() + ".</td>";
                                html += "<td width='97%' class='px-2 py-1'>" + json['Promotions'][j] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</tbody>";
                        html += "</table>";
                        html += "</td>";
                        html += "</tr>";

                        html += "</table>";

                        if (!jQuery.isEmptyObject(json["Ingredient"])){
                            html += "<br><table class='table w-100 mt-1 align-middle'>";

                            html += "<tr class='table-secondary'>";
                            html += "<th width='30%' class='px-3 fw-bold'>Ingredients</td>";
                            html += "<th width='30%' class='px-3 fw-bold py-2'>Quantity</td>";
                            html += "<th width='40%' class='px-3 fw-bold py-2'>Unit</td>";
                            html += "</tr>";

                            for (i in json['Ingredient']){
                                html += "<tr>";
                                html += "<td width='30%' class='px-3'>" + i + "</td>";
                                html += "<td width='30%' class='px-3 py-2'>" + json['Ingredient'][i]['qty'] + "</td>";
                                html += "<td width='40%' class='px-3 py-2'>" + json['Ingredient'][i]['unit'] + "</td>";
                                html += "</tr>";
                            }
                            html += "</table>";
                        }
                        
                        $("#foodModal .modal-body").html(html);
                        $("#foodModal .modal-title").text("Food Item Details (#" + element.parent().parent().find('.item_id').text() + ")");
                        
                    }
                });
            }

            $(document).ready(function() {
                $('#select-unselect-all').on('click', function () {
                    if ($(this).is(':checked')){
                        $('.content').find('.data-checkbox').prop('checked', true);
                    } else {
                        $('.content').find('.data-checkbox').prop('checked', false);
                    }
                    updateReadUnreadAllButtons();
                });

                $('.data-checkbox').click(function() {
                    if ($('#select-unselect-all').is(':checked')){
                        $('#select-unselect-all').prop('checked', false);
                    }
                    updateReadUnreadAllButtons();
                });

                $('#btn-settings').on('click', function() {
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        $.ajax({
                            url: '../../helpers/rating_review.php',
                            data: {
                                rest_id: '<?=$_SESSION['login_rest_id']?>',
                                get_rating_review_setting: true
                            },
                            method: 'post',
                            success: function(output) {
                                var json = $.parseJSON(output);

                                if (!jQuery.isEmptyObject(json)){
                                    $('#settingsModal #reply-template-setting-table tbody tr').remove();
                                    for (i in json['reply_options']){
                                        addReplyToTemplate(json['reply_options'][i]);
                                    }

                                    if (json['auto_reply_index'] != '-1'){
                                        $('#settingsModal #reply-template-setting-table tbody tr:nth-child(' + (parseInt(json['auto_reply_index'])+1) + ')').find('.auto-reply-checkbox').prop('checked', true);
                                    }
                                }
                                
                                $('.transition').removeClass('transition');
                                $('#settingsModal').modal('show');
                                $("nav, .side-bar, .content").addClass('blur');
                            }
                        });
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to modify the review settings.</div>').insertBefore('.review-list');
                    <?php } ?>
                });

                $('.close-settings').on('click', function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                    $('#settingsModal textarea').val('');
                    $('#settingsModal #reply-template-setting-table tbody tr').remove();
                    $('#settingsModal #reply-template-setting-table tbody').append('<tr id="empty-table-row"><td colspan="3" height="77.5"></td></tr>');
                });

                $(document).on('click', '.remove-reply-option-btn', function () {
                    $(this).parentsUntil('tr').parent().remove();

                    if ($('#settingsModal #reply-template-setting-table tbody').children().length == 0){
                        $('#settingsModal #reply-template-setting-table tbody').append('<tr id="empty-table-row"><td colspan="3" height="77.5"></td></tr>');
                    }
                });

                $(document).on('click', '.auto-reply-checkbox', function() {
                    $('.auto-reply-checkbox').not(this).prop('checked', false);
                });

                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');

                    if ($(this).hasClass('order-details-modal')){
                        window.location.reload();
                    }
                });

                $('#btn-save-rating_review_setting').click(function() {
                    var replies = [];
                    $('#settingsModal .reply_option_added').each(function() {
                        replies.push($(this).text());
                    });

                    var auto_reply_index = $('.auto-reply-checkbox').index($('.auto-reply-checkbox:checked'));

                    $.ajax({
                        url: '../../helpers/rating_review.php',
                        data: {
                            reply_options: replies,
                            auto_reply_index: auto_reply_index,
                            save_rating_review_setting: true
                        },
                        method: 'post',
                        success: function() {
                            window.location.reload();
                        }
                    });
                });

                $(document).on('input', 'textarea', function(){
                    var current_char = $(this).val().length;
                    var limit = 2000;

                    if (current_char > limit) {
                        $(this).val($(this).val().substring(0, limit));
                    }
                    var char_left = limit - current_char;
                    if (char_left < 0)
                        char_left = 0;
                    $(this).parent().parent().find('.char-counter').text(char_left.toString() + ' characters');
                });

                $("#reply-to-review-form").submit(function(event){
                    event.preventDefault();

                    $('#replyReviewModal .form-message').remove();
                    $('#replyReviewModal .text-danger').text('');
                    $('#replyReviewModal #reply_review').removeClass('red-box-shadow is-invalid');

                    if (!$('#replyReviewModal #reply_review').val()){
                        $('<div class="alert form-message mt-0 mb-2 alert-danger" role="alert"><button type="button" onclick=$("#replyReviewModal .form-message").remove(); class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#reply-to-review-form');
                        $('#replyReviewModal #reply_review').addClass('red-box-shadow is-invalid');
                        $('#replyReviewModal #reply_review').next().text('Please write something to reply your customer!');
                        $('#replyReviewModal .modal-body').animate({ scrollTop: 0 }, 0);
                    } else {
                        $('#reply-to-review-form').unbind().submit();
                    }
                });

                $('.review-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($ratings_reviews) == 0) { ?>
                    $('.dataTables_empty').addClass('text-center');
                    $('.dataTables_empty').text('No results !');
                <?php } ?>
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