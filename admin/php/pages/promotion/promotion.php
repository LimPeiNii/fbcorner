<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/promotion.php';
        include_once '../../helpers/food_menu.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'promotion/promotion', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'promotion/promotion', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            $promotions = getPromotions($connection, $_SESSION['login_rest_id']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion &VerticalLine; F&amp;B Corner</title>

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

            .promotion-list{
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
                <button type="button" onclick="delete_data();" class="btn bg-danger bg-opacity-75 float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><i class="fas fa-trash-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <a href="promotion_form.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <button type="button" class="btn bg-info float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Redemption Chances" id="reset-redemption"><i class="fas fa-sync-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Promotion
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive promotion-list">
                    <?php if (count($promotions) == 0 || count($promotions) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4">No.</td>
                                <th class="px-3">Type</td>
                                <th class="px-3">Details</td>
                                <th class="px-3">Status</td>
                                <th width="15%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                $promo_types = array(
                                    'BOGO'        => 'Buy One Get One',
                                    'multi_buy'   => 'Multi-Buys',
                                    'percent_off' => 'Percent Off',
                                    'dollar_dis'  => 'Dollar Discount',
                                    'promo_code'  => 'Promotional Code',
                                );
                                foreach ($promotions as $promotion) { 
                                    $counter++;
                            ?>
                                <tr>
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$promotion['promotion_id']?>"></td>
                                    <td width="5%" class="text-center align-middle"><?=$counter?></td>
                                    <td class="align-middle px-3"><?=$promo_types[$promotion['type_code']]?></td>
                                    <?php 
                                        if ($promotion['settings_data'])
                                            $details_json = json_decode($promotion['settings_data'], true);
                                        if ($promotion['type_code'] == 'BOGO'){
                                            $details = 'Buy 1 Get 1 Free';
                                        } elseif ($promotion['type_code'] == 'multi_buy') {
                                            $details = 'Buy ' . $details_json['amount_1'] . ' Get ' . $details_json['amount_2'] . ' Free';
                                        } elseif ($promotion['type_code'] == 'percent_off') {
                                            $details = $details_json['percentage'] . ' % Offer';
                                        } elseif ($promotion['type_code'] == 'dollar_dis') {
                                            $food_item_info = getFoodMenuItem($connection, json_decode($promotion['food_items'], true)[0]);
                                            $details = 'Original Price: RM ' . number_format($food_item_info['price'], 2, '.', '') . '<br>Discounted Price: RM ' . number_format($details_json['discounted_price'], 2, '.', '');
                                        } elseif ($promotion['type_code'] == 'promo_code') {
                                            $details = 'Promotion Code Name: ' . $details_json['promo_code_name'] . '<br>Minimum Spend: RM ' . number_format($details_json['min_price'], 2, '.', '') . '<br>Actual Discount: RM ' . number_format($details_json['actual_discount'], 2, '.', ''). '<br>Redemption Limit: ' . $details_json['max_redemption'];
                                        }
                                    ?>
                                    <td class="align-middle px-3"><span style="color: blue; cursor: pointer;" class="d-inline-block" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="<?=$details?>">View</span></td>
                                    <td class="align-middle px-3"><?=$promotion['status'] ? 'Available' : 'Unavailable'?></td>
                                    <td width="15%" class="text-center">
                                        <button type="button" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$promotion['promotion_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                        <a href="promotion_form.php?promotion_id=<?=$promotion['promotion_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
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
        <div class="modal fade p-0" id="detailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Promotion Details</h5>
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

        <div class="modal fade p-0" id="foodModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Food Details</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-target="#detailsModal" data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
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

            function delete_data(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (confirm("Are you sure you want to delete the promotion(s)?\n* Promotion(s) will no longer be applied to uncompleted orders after the deletion")){
                            $.ajax({ 
                                url: '../../helpers/promotion.php',
                                data: {selected_group_promotion: selected_data},
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to delete the promotion(s).</div>').insertBefore('.promotion-list');
                    <?php } ?>
                }
            }

            function viewDetails(id) {
                $.ajax({
                    url: '../../helpers/promotion.php',
                    data: { 
                        promo_id: id,
                        get_promo_food_items: true
                    },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";
                        var counter = 0;

                        if (!json['description']){
                            html += "<table class='table w-100 mt-1 align-middle'>";
                            html += "<tr>";
                            html += "<th width='5%' class='px-3 fw-bold text-center'>No.</td>";
                            html += "<th width='95%' class='px-3 fw-bold'>Applied Food Items</td>";
                            html += "</tr>";

                            for (i in json){
                                counter++;

                                html += "<tr>";
                                html += "<td class='d-none item_id'>" + i + "</td>";
                                html += "<td width='5%' class='px-3 fw-bold text-center'>" + counter.toString() + "</td>";
                                html += "<td width='95%' class='px-3'><a href='' style='color: blue;' data-bs-target='#foodModal' data-bs-toggle='modal' onclick='viewFoodItemDetails($(this)); '>" + json[i] + "</a></td>";
                                html += "</tr>";
                            }

                            html += "</table>";
                        } else {
                            html += '<span class="fw-bold font-size-18">Description:</span><br>';
                            html += '<p class="my-2">' + json['description'] + '</p>';
                        }
                    
                        $("#detailsModal .modal-body").html(html);
                        $("#detailsModal .modal-title").text("Promotion Details (#" + id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#detailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
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

                        $('.transition').removeClass('transition');
                        $("nav, .side-bar, .content").addClass('blur');
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
                });

                $('.data-checkbox').click(function() {
                    if ($('#select-unselect-all').is(':checked')){
                        $('#select-unselect-all').prop('checked', false);
                    }
                });

                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                });

                $('#reset-redemption').click(function () {
                    var promo_ids = [];
                    var error = false;
                    $('.data-checkbox:checked').each(function() {
                        if ($(this).parentsUntil('tr').parent().find('td:nth-child(3)').text() == 'Promotional Code')
                            promo_ids.push($(this).val());
                        else{
                            error = true;
                            return false;
                        }
                    });

                    if (error){
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;This function only applies to (Promotional Code) type promotions!</div>').insertBefore('.promotion-list');
                        return;
                    }

                    if (promo_ids.length > 0){
                        <?php if ((int)$has_modify_permission['has_permission']){ ?>
                            if (confirm("Are you sure you want to reset the redemption chances of this promotional code(s) for all customers?")){
                                $.ajax({ 
                                    url: '../../helpers/promotion.php',
                                    data: {reset_promo_redemption_ids: promo_ids},
                                    type: 'post',
                                    success: function(){
                                        window.location.reload();
                                    }
                                });
                            }
                        <?php } else { ?>
                            close_message();
                            $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to reset the redemption chance for the promo code(s).</div>').insertBefore('.promotion-list');
                        <?php } ?>
                    }
                });

                $('.promotion-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($promotions) == 0) { ?>
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