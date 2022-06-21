<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/reservation.php';
        include_once '../../helpers/time_slot.php';
        include_once '../../helpers/table.php';
        include_once '../../helpers/user.php';
        include_once '../../../../store/php/helpers/customer.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/reservation', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/reservation', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            $reservations = getReservations($connection, $_SESSION['login_rest_id'], array('order_by' => "(CASE WHEN r.status = 1 THEN 1 ELSE 2 END) ASC, date DESC, (CASE WHEN startTime IS NULL THEN second_startTime ELSE startTime END)", 'asc_desc' => 'DESC'));
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation &VerticalLine; F&amp;B Corner</title>

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

            .reservation-list{
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
                <a href="reservation_form.php"><button type="button" class="btn btn-secondary float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Reservations
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive reservation-list">
                    <?php if (count($reservations) == 0 || count($reservations) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr class="text-center align-middle">
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4">ID</td>
                                <th width="20%" class="px-3">Name</td>
                                <th width="10%" class="px-3">Date</td>
                                <th width="20%" class="px-3">Time</td>
                                <th width="10%" class="px-3">Table No. (Name)</td>
                                <th width="10%" class="px-3">Special Request</td>
                                <th width="10%" class="px-3">Status</td>
                                <th width="13%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                foreach ($reservations as $reservation) { 
                                    $counter++;
                                    $customer = getCustomer($connection, $reservation['cus_id']);
                                    $time_slot = json_decode($reservation['time_slot_id'], true);
                                    $table_info = getTable($connection, $reservation['table_id']);
                                    if (!empty($table_info))
                                        $table_num_name = $table_info['table_num_name'];
                                    else{
                                        $deleted_data = json_decode($reservation['deleted_data'], true);
                                        $table_num_name = $deleted_data['table_num_name'];
                                    }
                                        
                                    if (!empty($reservation['startTime']) && !empty($reservation['endTime']))
                                        $time = date('h:i a', strtotime($reservation['startTime'])) . ' to ' . date('h:i a', strtotime($reservation['endTime']));
                                    else{
                                        $deleted_data = json_decode($reservation['deleted_data'], true);
                                        $time = date('h:i a', strtotime($deleted_data['startTime'])) . ' to ' . date('h:i a', strtotime($deleted_data['endTime']));
                                    }
                                ?>
                                <tr class="text-center align-middle">
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$reservation['reservation_id']?>"></td>
                                    <td width="5%" class="px-3"><?=$reservation['reservation_id']?></td>
                                    <td width="20%" class="px-3"><span style="color: blue; cursor: pointer;" class="fw-bold" onclick="viewCustomerDetails('<?=$reservation['cus_id']?>');"><?=$customer['firstname']?> <?=$customer['lastname']?></span></td>
                                    <td width="10%" class="px-3"><?=date('d/m/Y', strtotime($reservation['date']))?></td>
                                    <td width="20%" class="px-3"><?=$time?></td>
                                    <td width="10%" class="px-3"><?=$table_num_name?></td>
                                    <?php if (strlen($reservation['additional_notes']) > 0) { ?>
                                    <td width="10%" class="px-3"><i class="fas fa-check"></i></td>
                                    <?php } else { ?>
                                    <td width="10%" class="px-3"></td>
                                    <?php } ?>
                                    <?php if ($reservation['reservation_status'] == '0') { ?>
                                    <td width="10%" class="px-3"><span class="badge bg-danger font-size-14">&nbsp;Cancelled&nbsp;</span></td>
                                    <?php } else if ($reservation['reservation_status'] == '1') { ?>
                                    <td width="10%" class="px-3"><span class="badge bg-success font-size-14">&nbsp;&nbsp;Reserved&nbsp;&nbsp;</span></td>
                                    <?php } else if ($reservation['reservation_status'] == '2') { ?>
                                    <td width="10%" class="px-3"><span class="badge bg-secondary font-size-14">&nbsp;&nbsp;Disabled&nbsp;&nbsp;</span></td>
                                    <?php } else if ($reservation['reservation_status'] == '3') { ?>
                                    <td width="10%" class="px-3"><span class="badge bg-primary font-size-14">Completed</span></td>
                                    <?php } ?>
                                    <td width="13%">
                                        <?php if ($reservation['reservation_status'] != '1') { ?>
                                            <button type="button" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$reservation['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                            <button type="button" class="btn bg-primary bg-opacity-50" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" disabled><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                        <?php } else { ?>
                                            <button type="button" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$reservation['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                            <a href="reservation_form.php?reservation_id=<?=$reservation['reservation_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
                                        <?php } ?>
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
                        <h5 class="modal-title">Reservation Details</h5>
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

        <div class="modal fade p-0" id="orderModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Details</h5>
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
                        <button type="button" class="btn btn-primary" data-bs-target="#orderModal" data-bs-toggle="modal">Back</button>
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
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
                        if (confirm("Are you sure you want to delete the reservation(s)?\n* Relevant pre-orders will also be deleted")){
                            $.ajax({ 
                                url: '../../helpers/reservation.php',
                                data: {selected_group: selected_data},
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to delete the reservation(s).</div>').insertBefore('.reservation-list');
                    <?php } ?>
                }
            }

            function viewDetails(id) {
                $.ajax({ 
                    url: '../../helpers/reservation.php',
                    data: { id: id },
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
                        $("#detailsModal .modal-title").text("Reservation Details (#" + id + ")");

                        //preorder
                        $.ajax({
                            url: '../../helpers/reservation.php',
                            data: {
                                reservation_id: id,
                                check_preorder: true
                            },
                            type: 'post',
                            success: function(output){
                                var json2 = $.parseJSON(output);

                                $('.pre-order-btn').remove();

                                if (json2['reservation_status'] == '1'){
                                    //can add or view preorder
                                    if (json2['preorder'].length > 0){
                                        $('#detailsModal .modal-footer').prepend('<button type="button" class="btn btn-primary pre-order-btn" data-bs-target="#orderModal" data-bs-toggle="modal">' + (json2['reservation_remarks'] != 'Ongoing' ? 'View Pre-Order' : 'View Order') + '</button>');
                                    } else {
                                        $('#detailsModal .modal-footer').prepend('<a href="../order/preorder_form.php?reservation_id=' + id + '" class="pre-order-btn"><button type="button" class="btn btn-primary">' + (json2['reservation_remarks'] != 'Ongoing' ? 'Add Pre-Order' : 'Add Order') + '</button></a>');
                                    }
                                } else {
                                    if (json2['preorder'].length > 0){
                                        $('#detailsModal .modal-footer').prepend('<button type="button" class="btn btn-primary pre-order-btn" data-bs-target="#orderModal" data-bs-toggle="modal">View Order</button>');
                                    }
                                }

                                if (json2['preorder'].length > 0){
                                    $.ajax({ 
                                        url: '../../helpers/order.php',
                                        data: { modal_order_id: json2['preorder'][0]['order_id'] },
                                        type: 'post',
                                        success: function(output){
                                            var json = $.parseJSON(output);

                                            var html = '';

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
                                                } else if (i == 'promo_code') {
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
                                            $("#orderModal .modal-title").text("Order Details (#" + json2['preorder'][0]['order_id'] + ")");
                                        }
                                    });
                                }

                                $('.transition').removeClass('transition');
                                $('#detailsModal').modal('show');
                                $("nav, .side-bar, .content").addClass('blur');
                            }
                        });                    
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
                    }
                });
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
                        html += "<td width='60%' class='p-3'><img width='80' height='80' class='me-2 rounded-circle' style='border: 3px solid #d0efff;' src='../../../../store/uploads/profile_pic/" + json['pp'] + "'></td>";
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

                var dataTable = $('.reservation-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($reservations) == 0) { ?>
                    $('.dataTables_empty').addClass('text-center');
                    $('.dataTables_empty').text('No results !');
                <?php } ?>

                <?php if (isset($_GET['search_id'])) { ?>
                    dataTable.search('<?=$_GET['search_id']?>').draw();
                <?php } ?>

                $('input[type=search]').on('search',function(){
                    window.location = window.location.pathname;
                });

                setInterval(function() {
                    $.ajax({
                        url: '../../helpers/reservation.php',
                        data: {
                            total_count: dataTable.rows().count(),
                            update_reservation_list: true
                        },
                        method: 'post',
                        success: function(output){
                            if (output){
                                window.location.reload();
                            }
                        }
                    });
                }, 5000);
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