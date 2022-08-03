<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../../../admin/php/helpers/reservation.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/time_slot.php';
    include_once '../../../../admin/php/helpers/table.php';
    include_once '../../../../admin/php/helpers/settings.php';

    if (isset($_SESSION['login_cus_id'])) {

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

      $reservations = getCustomerReservations($connection, $_SESSION['login_cus_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
    
    <style>
        body{
            height: 100%;
        }
        
        .col{
            max-height: 500px;
            min-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
        }

        .col table{
          width: 95%;
        }

        #title-image, #title-image img{
            min-width: 180px;
            max-width: 180px;
        }

        @media (min-width: 768px){
          .col{
            flex: 1 0 0%;
          }

          #title-name{
            margin-left: 1.5rem;
          }

          #title-name h2{
              margin-left: 0.5rem;
          }
        }

        @media (max-width: 768px){
          .row>*{
            flex-shrink: unset;
          }

          #title-name, #title-image{
              width: 100% !important;
          }

          #title-name h1, #title-name h2, #title-image img{
            align-self: center!important;
          }

          #title-image{
            max-width: 100% !important;
            min-width: 100% !important;
            flex-direction: column;
          }
        }

        #res_modal, #orderModal table{
            border: 1px solid black !important;
        }

        #res_modal tr, #res_modal th, #orderModal tr, #orderModal th{
            border-color: #dee2e6 !important;
        }
        
        #res_modal thead, #res_modal tbody, #orderModal thead, #orderModal tbody{
            border-width: 2px;
        }
    </style>

</head>
<body>
    <?php include_once '../../common/navigation_bar.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
      <div class="m-5">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 mx-3">
            <div class="col-sm-12 col-md-2 d-flex" style="min-width: 180px;" id="title-image">
                <img width='180' height='180' class='rounded-circle align-self-center' style="border: 3px solid #d0efff;" src="../../../uploads/profile_pic/<?=$profile_img?>">
            </div>
            <div class="col-sm-12 col-md-10 align-self-center d-flex flex-column" id="title-name" style="width: fit-content;">
                <h1 style="cursor: default; font-size: 3rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Reservations</h1>
                <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 m-auto mb-3 mt-5" style="margin-bottom: 100px !important;">
          <div class="box-style col p-0 m-4 mt-0 color-2">
            <div class="color-2 px-3 pt-2 w-100">
              <h5 class="fw-bold m-2">Previous Reservations</h5>
              <table class="m-3">
              <?php $counter = 1; ?>
                <tbody>
                  <?php foreach ($reservations as $reservation) { ?>
                    <?php date_default_timezone_set("Asia/Kuala_Lumpur"); ?>
                    <?php if (strtotime($reservation['date']) < strtotime(date('Y-m-d')) || $reservation['status'] != '1') { ?>
                    <?php
                      $this_reservation_info = getReservation($connection, $reservation['reservation_id']);
                      if (!empty($this_reservation_info['startTime']) && !empty($this_reservation_info['endTime']))
                        $time = date('h:i a', strtotime($this_reservation_info['startTime'])) . ' to ' . date('h:i a', strtotime($this_reservation_info['endTime']));
                      else{
                        $deleted_data = json_decode($this_reservation_info['deleted_data'], true);
                        $time = date('h:i a', strtotime($deleted_data['startTime'])) . ' to ' . date('h:i a', strtotime($deleted_data['endTime']));
                      }  
                    ?>
                    <tr style="height: 127px;">
                      <td class="align-top pt-3" width="7%"><?=$counter?>.</td>
                      <td class="align-top pt-3 pe-3">
                        <?=getRestaurant($connection, $reservation['rest_id'])['rest_name']?><br>
                        <?=date('d/m/Y', strtotime($reservation['date']))?><br>
                        <?=$time?><br>
                        <?php if ($reservation['status'] == '0') { ?>
                          <span class="badge bg-danger">Cancelled</span>
                        <?php } elseif ($reservation['status'] == '1') { ?>
                          <span class="badge bg-success">Reserved</span>
                        <?php } elseif ($reservation['status'] == '2') { ?>
                          <span class="badge bg-secondary">Disabled</span>
                        <?php } elseif ($reservation['status'] == '3') { ?>
                          <span class="badge bg-primary">Completed</span>
                        <?php } ?>
                      </td>
                      <td>
                        <button type="button" style="float: right;" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$reservation['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                      </td>
                    </tr>
                    <?php $counter++; } ?>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="box-style col p-0 m-4 mt-0 color-2">
            <div class="color-2 px-3 pt-2 w-100">
              <h5 class="fw-bold m-2">Upcoming Reservations</h5>
              <table class="m-3">
              <?php $counter = 1; ?>
                <tbody>
                  <?php foreach ($reservations as $reservation) { ?>
                    <?php date_default_timezone_set("Asia/Kuala_Lumpur"); ?>
                    <?php if (strtotime($reservation['date']) >= strtotime(date('Y-m-d')) && $reservation['status'] == '1') { ?>
                    <?php
                      $this_reservation_info = getReservation($connection, $reservation['reservation_id']);
                      if (!empty($this_reservation_info['startTime']) && !empty($this_reservation_info['endTime']))
                        $time = date('h:i a', strtotime($this_reservation_info['startTime'])) . ' to ' . date('h:i a', strtotime($this_reservation_info['endTime']));
                      else{
                        $deleted_data = json_decode($this_reservation_info['deleted_data'], true);
                        $time = date('h:i a', strtotime($deleted_data['startTime'])) . ' to ' . date('h:i a', strtotime($deleted_data['endTime']));
                      }  
                    ?>
                    <tr style="height: 127px;">
                      <td class="align-top pt-3" width="7%"><?=$counter?>.</td>
                      <td class="align-top pt-3 pe-3">
                        <?=getRestaurant($connection, $reservation['rest_id'])['rest_name']?><br>
                        <?=date('d/m/Y', strtotime($reservation['date']))?><br>
                        <?=$time?><br>
                        <?php if ($reservation['status'] == '1' && $reservation['remarks'] == 'Ongoing') { ?>
                          <span class="badge bg-success">Ongoing</span>
                        <?php } elseif ($reservation['status'] == '1') { ?>
                          <span class="badge bg-success">Reserved</span>
                        <?php } ?>
                      </td>
                      <?php $time_slot_setting = getSetting($connection, $reservation['rest_id'], 'time_slot');?>
                      <td>
                        <button type="button" style="float: right; width: 48.5px;" class="btn bg-primary bg-opacity-75 ms-2" onclick="showEditReservationModal('<?=$reservation['reservation_id']?>', '<?=$reservation['rest_id']?>', '<?=$reservation['table_id']?>', '<?=$time_slot_setting['maximum-time-slot']?>');" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                        <?php if ($reservation['remarks'] == 'Ongoing') { ?>
                          <a href="../order/food_list.php?visit_rest_id=<?=$reservation['rest_id']?>"><button type="button" style="float: right; width: 48.5px;" class="btn bg-secondary bg-opacity-75 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
                        <?php } ?>
                        <button type="button" style="float: right;" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$reservation['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                      </td>
                    </tr>
                    <?php $counter++; } ?>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

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

    <div class="modal fade p-0" id="editReservationModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Reservation</h5>
                    <button type="button" class="btn-close close-edit-reservation-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../helpers/reservation.php" method="POST" id="edit-reservation-form">
                        <div class="mb-3" id="date-container">
                            <input type="text" style="display: none;" name="submit_form_store_edit_reservation">
                            <input type="text" style="display: none;" name="reservation_id" id="reservation_id">
                            <label for="date" class="form-label fw-bold">Date <span class="required-star">*</span></label>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-3" id="time-container">
                            <label for="time-start" class="form-label fw-bold">Time <span class="required-star">*</span></label>
                            <select class="form-select" onchange="updateTableDropdown();" multiple aria-label="multiple" id="time" name="time[]">
                            </select>
                            <p class="d-block text-danger time-error"></p>
                        </div>
                        <div class="mb-3" id="table-container">
                          <label for="table-num-name" class="form-label fw-bold">Table Number (Name) <span class="required-star">*</span></label>
                          <select class="form-select" id="table-num-name" name="table-num-name">
                          </select>
                          <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-3" id="request-container">
                            <label for="special-request" class="form-label fw-bold">Special Request</label>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                        </div>
                        <div class="mb-3">
                          <button type="button" class="btn btn-outline-danger" data-bs-target="#editReservationModal2" data-bs-toggle="modal">Cancel Reservation</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" form="edit-reservation-form"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" role="status" aria-hidden="true"></span>Save changes</button>
                    <button type="button" class="btn btn-secondary close-edit-reservation-modal" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade p-0" id="editReservationModal2" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Reservation</h5>
                    <button type="button" class="btn-close close-edit-reservation-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="font-size-18 fw-bold text-center">Are you sure you want to cancel the reservation?</p>
                    <div class="mb-3" id="remarks-container">
                        <label for="remarks" class="form-label">Please tell us the reason</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="6"></textarea>
                        <small><div class="char-counter" style="color: grey;">1000 characters</div></small>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="yes-btn" onclick="cancelReservation();"><span class="spinner-border spinner-border-sm me-3 spinner_2 d-none" role="status" aria-hidden="true"></span>Yes</button>
                  <button class="btn btn-secondary" id="no-btn" data-bs-target="#editReservationModal" data-bs-toggle="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <script>
      function close_message() {
        $('.form-message').remove();
      }

      function viewDetails(id) {
        $.ajax({ 
            url: '../../../../admin/php/helpers/reservation.php',
            data: { id: id },
            type: 'post',
            success: function(output){
                var json = $.parseJSON(output);

                var html = "";

                html += "<table class='table w-100 table-striped mt-1 align-middle' id='res_modal'>";

                    for (i in json){
                        html += "<tr>";
                        html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                        html += "<td width='60%' class='px-3 py-1'>" + json[i] + "</td>";
                        html += "</tr>";
                    }

                html += "</table>";
            
                $("#detailsModal .modal-body").html(html);
                
                $('.transition').removeClass('transition');
                $('#detailsModal').modal('show');
                $("nav, main, footer").addClass('blur');

                //preorder
                $.ajax({
                  url: '../../../../admin/php/helpers/reservation.php',
                  data: {
                      reservation_id: id,
                      check_preorder: true
                  },
                  type: 'post',
                  success: function(output){
                      var json2 = $.parseJSON(output);

                      $('.pre-order-btn').remove();

                      if (json2['preorder'].length > 0){
                          $('#detailsModal .modal-footer').prepend('<button type="button" class="btn btn-primary pre-order-btn" data-bs-target="#orderModal" data-bs-toggle="modal">' + (json2['reservation_status'] == '1' ? (json2['reservation_remarks'] != 'Ongoing' ? 'View Pre-Order' : 'View Order') : 'View Order') + '</button>');
                      
                          $.ajax({ 
                              url: '../../../../admin/php/helpers/order.php',
                              data: { modal_order_id: json2['preorder'][0]['order_id'] },
                              type: 'post',
                              success: function(output){
                                  var json = $.parseJSON(output);

                                  var html = '';

                                  html += "<table class='table w-100 mt-1 align-middle' style='white-space: nowrap;'>";
                                  html += "<tr class='table-secondary'>";
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

                                                  html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + "'" + (j == 'Name' ? ' style="white-space: normal;"' : '') + ">" + (j == 'Name' ? '<a href="../order/food_profile.php?food_item_id=' + json['Order'][i]['food_id'] + '" target="_blank" style="color: blue;">' : '') + json['Order'][i][j] + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '&nbsp;<span class=text-danger>+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
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

                                  html += "<table class='table text-nowrap w-100 table-striped mt-1 align-middle' style='min-width: 407px'>";

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
                              }
                          });
                      }

                      $('.transition').removeClass('transition');
                      $('#detailsModal').modal('show');
                  }
                }); 
            }
        });
      }

      function showEditReservationModal(reser_id, rest_id, a_table_id, max_time_slot){
        $.ajax({
          url: '../../helpers/reservation.php',
          data: {
            reservation_id: reser_id,
            restaurant_id: rest_id,
            table_id: a_table_id,
            get_data_for_edit: true
          },
          method: 'post',
          success: function(output){
            var json = $.parseJSON(output);

            var reservation_infor = json['reservation_info'];
            var time_slots = json['time_slots'];
            var table = json['table'];

            //date
            $('#date-container #date').remove();
            $('<input type="date" class="form-control" value="' + reservation_infor['date'] + '" id="date" name="date">').insertBefore('#date-container p');
            $('#date-container #date').attr('onchange', 'updateTableDropdown(' + max_time_slot + ', \'' + reservation_infor['rest_id'] + '\', ' + reservation_infor['reservation_id'] + ');');

            //time
            $('#time-container select').children().remove();
            var selected_time_slots = $.parseJSON(reservation_infor['time_slot_id']);
            var selected_time_slots_status = [];
            for (i in time_slots){
              if (selected_time_slots.includes(time_slots[i]['time_slot_id'])){
                selected_time_slots_status.push(time_slots[i]['status']);

                $('#time-container select').append('<option value="' + time_slots[i]['time_slot_id'] + '" selected="selected"' + (time_slots[i]['status'] == '0' ? ' disabled' : '') + '>' + time_slots[i]['start_time'] + ' to ' + time_slots[i]['end_time'] + '</option>');
              } else if (time_slots[i]['status'] == '0'){
                $('#time-container select').append('<option value="' + time_slots[i]['time_slot_id'] + '" disabled>' + time_slots[i]['start_time'] + ' to ' + time_slots[i]['end_time'] + '</option>');
              } else {
                $('#time-container select').append('<option value="' + time_slots[i]['time_slot_id'] + '">' + time_slots[i]['start_time'] + ' to ' + time_slots[i]['end_time'] + '</option>');
              }
            }   

            $('#time-container select').attr('onchange', 'updateTableDropdown(' + max_time_slot + ', \'' + reservation_infor['rest_id'] + '\', ' + reservation_infor['reservation_id'] + ');');

            //table
            if (table !== null){
              $('#table-container select').children().remove();
              $('#table-container select').append('<option value="' + table['table_id'] + '" selected="selected">' + table['table_num_name'] + '</option>');
            }
            
            //trigger to update table dropdown
            //if one of the selected time slot is disabled, need to empty time slot and table for reselect
            if (selected_time_slots_status.length > 0 && selected_time_slots_status.includes('0')) {
                $('#time-container select').val('');
                $('#table-container select').children().remove();
            }
            //if one of the selected time slot is deleted OR if all time slots are reset
            if ($('#time-container select').val().length < selected_time_slots.length){
                $('#time-container select').val('');
                $('#table-container select').children().remove();
            }
            $('#time-container select').trigger('change');
            
            //special request
            $('#request-container textarea').remove();
            $('<textarea class="form-control" id="special-request" name="special-request" rows="6">' + reservation_infor['additional_notes'] + '</textarea>').insertBefore('#request-container small');

            var limit = 1000;
            var current_char = $('#request-container textarea').val().length;
            $('#request-container textarea').parent().find('.char-counter').text((limit - current_char).toString() + ' characters');

            //assign reservation id
            $('#date-container #reservation_id').val(reservation_infor['reservation_id']);
          }
        });

        $('.transition').removeClass('transition');
        $('#editReservationModal').modal('show');
        $("nav, main, footer").addClass('blur');
      }

      function updateTableDropdown(max_time_slots, rest_id, reservation_id){
          $('#time').removeClass('red-box-shadow is-invalid');
          $('#time').parent().find('p').text('');

          if ($('#time').val().length > max_time_slots){
              $('#time').addClass('red-box-shadow is-invalid');
              $('#time').parent().find('p').text('You can only select maximum ' + max_time_slots.toString() + ' time slots')
              $('#table-num-name').children().remove();
          } else {
              var selected_time_slots = $('#time').val();
              for (var i = 1; i <= selected_time_slots.length-1; i++){
                  if (parseInt(selected_time_slots[i]) - parseInt(selected_time_slots[i-1]) > 1){
                      //error
                      $('#time').parent().find('p').text('You can only select consecutive time');
                      $('#time').addClass('red-box-shadow is-invalid');
                      $('#table-num-name').children().remove();
                      return;
                  }
              }

              if ($('#date').val() != '' && $('#time').val().length !== 0){
                  $.ajax({
                      url: '../../helpers/reservation.php',
                      data: {
                          date: $('#date').val(),
                          time: $('#time').val(),
                          the_rest_id: rest_id,
                          edit_reservation_id: reservation_id,
                          get_available_tables_store: true
                      },
                      method: 'post',
                      success: function(output) {
                          var json = $.parseJSON(output);

                          var selected_table = $('#table-num-name').val();
                          $('#table-num-name').children().remove();
                          $('#table-num-name').append('<option value="0">-- Please Select --</option>');

                          for (i in json){
                              if (selected_table == json[i]['table_id']){
                                  $('#table-num-name').append('<option value="' + json[i]['table_id'] + '" selected>' + json[i]['table_num_name'] + ' (table size: ' + json[i]['capacity'] + ')</option>');
                              } else {
                                  $('#table-num-name').append('<option value="' + json[i]['table_id'] + '">' + json[i]['table_num_name'] + ' (table size: ' + json[i]['capacity'] + ')</option>');
                              }
                          }
                      }
                  });
              }
          }
      }

      function cancelReservation(){
        $.ajax({
          url: '../../helpers/reservation.php',
          data: {
            reservation_id: $('#reservation_id').val(),
            remarks: $('#remarks').val(),
            cancel_reservation: true
          },
          method: 'post',
          beforeSend: function(){
            $('.spinner_2').removeClass('d-none');
            $('.spinner_2').parent().prop('disabled', true);
          },
          success: function(){
            window.location.reload();
          }
        });
      }


      $(document).ready(function() {
        $('.close-details-modal').click(function() {
          $("nav, main, footer").removeClass('blur');
        });

        $('.close-edit-reservation-modal').on('click', function() {
            $("nav, main, footer").removeClass('blur');
            $('.form-message').remove();
            $('.text-danger').text('');
            $('.form-control, .form-select').each(function() {
                $(this).removeClass('red-box-shadow is-invalid');
                $(this).val('');
            });
        });

        $(document).on('input', 'textarea', function(){
            var current_char = $(this).val().length;
            var limit = 1000;
            if (current_char > limit) {
                $(this).val($(this).val().substring(0, limit));
            }
            var char_left = limit - current_char;
            if (char_left < 0)
                char_left = 0;
            $(this).parent().find('.char-counter').text(char_left.toString() + ' characters');
            
        });

        $("#edit-reservation-form").submit(function(event){
          event.preventDefault();

          $('.form-message').remove();
          $('input, select').removeClass('red-box-shadow is-invalid');
          $('.text-danger').text('');

          $.ajax({
              url: '../../helpers/reservation.php',
              data: {
                  reservation_id: $('#reservation_id').val(),
                  date: $('#date').val(),
                  time: $('#time').val().length == 0 ? '[]' : $('#time').val(),
                  table_id: $('#table-num-name').val(),
                  submit_check_store: $("button[type=submit]").val()
              },
              method: 'post',
              success: function(output) {
                  var json = $.parseJSON(output);
                  
                  if (json['error']){
                      $('<div class="alert form-message alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#edit-reservation-form');
                      $.each(json['error'], function(key, value) {
                          $('#' + key).addClass('red-box-shadow is-invalid');
                          $('#' + key).parent().find('p').text(value);
                      });
                      $('#editReservationModal .modal-body').animate({ scrollTop: 0 }, 0);
                  } else if (json['hours-error']){
                    $('<div class="alert form-message alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + json['hours-error'] + '</div>').insertBefore('#edit-reservation-form');
                    $('#editReservationModal .modal-body').animate({ scrollTop: 0 }, 0);
                  } else {
                      $('#spinner').removeClass('d-none');
                      $('#spinner').parent().prop('disabled', true);
                      $('#edit-reservation-form').unbind().submit();
                  }
              }
          });
        });
      });
    </script>

  </body>
</html>

<?php

  } else {
    header("Location: ../homepage/index.php");
    exit;
  }

?>