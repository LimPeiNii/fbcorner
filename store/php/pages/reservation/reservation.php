<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/customer.php';
    include_once '../../helpers/voucher.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/time_slot.php';
    include_once '../../../../admin/php/helpers/table.php';
    include_once '../../../../admin/php/helpers/settings.php';
    include_once '../../../../admin/php/helpers/food_menu.php';
    include_once '../../../../admin/php/helpers/category.php';

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

      $visit_restaurant = getRestaurant($connection, $_GET['visit_rest_id']);
      $time_slots = getTimeSlots($connection, $_GET['visit_rest_id']);
      $customer = getCustomer($connection, $_SESSION['login_cus_id']);
      $food_menu_items_results = getFoodMenuItems($connection, $_GET['visit_rest_id'], ['order_by' => 'item_code', 'asc_desc' => 'ASC']);
      $food_menu_items = [];
      foreach ($food_menu_items_results as $result){
        $food_menu_items[$result['category_id']][] = $result;
      }
      
      $tables = getTables($connection, $_GET['visit_rest_id']);
      $table_sizes = array_column($tables, 'capacity');
      if ($table_sizes)
        $max_table_size = max($table_sizes);
      else
        $max_table_size = 0;
      $settings = getSettings($connection, $_GET['visit_rest_id']);
      $settings = json_decode($settings['time_slot'], true);
      $vouchers = getVouchers($connection, $_SESSION['login_cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_in_use' => true]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation &VerticalLine; <?=$visit_restaurant['rest_name']?></title>

    <?php include_once '../../../../link.php'; ?>

    <style>
      .timeslots-wrapper{
        min-height: 100vh;
        border: 1px solid black;
      }

      @media (max-width: 550px){
        .timeslots label{
          margin-left: 10px !important;
          margin-right: 10px;
        }
      }

      .hover-opacity:hover{
        background-color: #27a86cbb !important;
      }

      .timeslots-wrapper{
        position: relative;
      }

      .continue-btn-wrapper{
        position: absolute;
        bottom: 0;
        right: 0;
      } 

      #voucher{
        border: 1px solid black; 
        max-height: 250px;
        overflow-y: auto;
      }

      #date-size-wrapper input{
        max-width: max(204.4px,15vw);
      }

      @media (max-width: 1199.36px){
        #pre-order-food-wrapper{
          text-align: start !important; 
        }
      }

      .enabled:hover{
        background-color: rgb(245, 232, 253);
      }

      .enabled, .disabled{
        box-shadow: 1px 1px 8px 2px rgba(0, 0, 0, 0.2);
      }

      body{
        height: 100%;
      }
    </style>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>

    <?php include_once '../../common/signin_signup.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
        <div>
            <div id="time-slot">
                <div class="m-4">
                    <div>
                    <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 d-inline-block font-bernard font-size-35">
                        <?=$visit_restaurant['rest_name']?>
                    </h3>
                    </div>
                    <div class="timeslots-wrapper mt-4">
                    <div class="m-4 my-0 me-0">
                      <div class="container p-0 ms-0 me-2 w-100">
                        <div class="row" id="date-size-wrapper">
                          <div class="col-3 mt-4 pe-0" style="min-width: max-content; max-width: max-content">
                            <input type="date" name="" id="date" class="custom-form-control">
                          </div>
                          <div class="col-3 mt-4 p-0" style="min-width: max-content; padding-left: 12px !important; margin-right: 2rem;">
                            <input type="number" name="" id="table-size" class="custom-form-control" min="1" placeholder="Table Size">
                          </div>
                          <div class="col-3 mt-4" style="min-width: max-content;">
                            <button type="button" id="btn-find-table" class="btn btn-outline-primary" style="white-space: nowrap; margin-right: 2rem;">Find a Table</button>
                          </div>
                          <?php if ($food_menu_items) { ?>
                          <div class="col-3 mt-4 text-end" id="pre-order-food-wrapper" style="min-width: max-content; line-height: 35px; margin-right: 2rem;">
                            <input type="checkbox" id="checkbox-pre-order-food" class="form-check-input align-middle mt-0 checkbox-default me-2">
                            <label class="form-check-label" for="checkbox-pre-order-food">Pre-order Food</label>
                          </div>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                    <div class="timeslots m-4 row row-cols-1 row-cols-sm-2 row-cols-md-3 mt-4" style="padding-bottom: 4rem !important">
                        <?php foreach ($time_slots as $time_slot){ ?>
                        <div class="btn-group col p-4" role="group" aria-label="Basic checkbox toggle button group">
                          <input type="checkbox" class="btn-check d-none" id="time-slot-<?=$time_slot['time_slot_id']?>" value="<?=$time_slot['time_slot_id']?>" autocomplete="off">
                          <label class="btn btn-outline-success hover-opacity d-none" id="label-time-slot-<?=$time_slot['time_slot_id']?>" for="time-slot-<?=$time_slot['time_slot_id']?>"><?=date('h:i a', strtotime($time_slot['start_time']))?><br>to<br><?=date('h:i a', strtotime($time_slot['end_time']))?></label>
                        </div>
                        <?php } ?>          
                    </div>
                    <div class="m-3 continue-btn-wrapper">    
                      <button type="button" class="btn btn-primary float-end font-size-20 text-white time-slot-continue-btn">Continue</button>
                    </div>
                    </div>
                </div>
            </div>
            <div id="pre-order-food">
              <div class="m-4">
                <div class="pre-order-food-wrapper">
                  <div class="m-1 mt-2 px-4 mb-4">
                  <?php foreach ($food_menu_items as $key => $categories) { ?>
                      <?php $category_name = getCategory($connection, $key)['category_name']; ?>
                      <div class="pt-4">
                          <span style="font-size: 1.5rem;"><?=ucwords($category_name)?></span>
                          <hr class="my-2">
                      </div>
                      
                      <div class="mt-0 px-4 pb-5">
                          <?php foreach ($categories as $food_menu_item) { ?>
                              <?php if ($food_menu_item['status'] == '1') { ?>
                              <div class="enabled pt-3 ps-4 mt-4">
                              <?php } else { ?>
                              <div class="disabled pt-3 ps-4 mt-4" style="opacity: 0.5; pointer-event: none; cursor: default;">
                              <?php } ?>
                                  <div class="container">
                                    <div class="d-flex flex-wrap w-100">
                                      <div class="me-3 mb-3" style="max-width: 100px;">
                                        <img height="80" width="80" src="../../../../admin/uploads/food_menu_photo/<?=$food_menu_item['image']?>">
                                      </div>
                                      <div class="me-3 mb-3 flex-grow-1">
                                        <?=$food_menu_item['item_name']?><br>
                                        <span <?=($food_menu_item['status'] == '1' ? 'style="cursor: pointer;"' : '')?> onclick="viewDetails(<?=$food_menu_item['item_id']?>); "><small style="color: grey;">Details</small></span>
                                      </div>
                                      <div class="me-5 mb-3 text-end" style="white-space: nowrap;">
                                        RM <?=number_format($food_menu_item['price'], 2, '.', '')?>
                                      </div>
                                      <div class="me-3 mb-3 text-end">
                                        <div class="d-none item_id"><?=$food_menu_item['item_id']?></div>
                                        <?php if ($food_menu_item['status'] == '1') { ?>
                                          <input type="number" min="0" class="form-control qty d-inline-block ps-2 pe-1" style="width: 55px; border-radius: 0px; border: 0px !important; background-color: #dee2e6" value="0">
                                        <?php } else { ?>
                                          <input type="number" min="0" class="form-control qty d-inline-block ps-2 pe-1" style="width: 55px; border-radius: 0px; border: 0px !important; background-color: #dee2e6" value="0" disabled>
                                        <?php } ?>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                          <?php } ?>
                      </div>
                  <?php } ?>
                  <div class="my-5 mb-4">
                    <button type="button" class="btn btn-secondary font-size-20" onclick="$('#time-slot').show(); $('#pre-order-food').hide(); $('html, body').animate({ scrollTop: 0 }, 0);">Back</button>
                    <button type="button" class="btn btn-primary float-end text-white pre-order-food-continue-btn font-size-20">Continue</button>
                  </div>
                  </div>
                  
                </div>
              </div>
            </div>
            <div id="confirmation">
                <div>
                <h3 style="cursor: default; line-height: 45px;" class="m-5 text-center font-century font-size-35">
                    Please Confirm Your Reservation
                </h3>
                </div>
                <div class="row m-5 mb-3" style="margin-top: 100px !important">
                    <div class="col p-0 px-2 mb-4 mt-0">
                    <div class="px-3 pt-2 w-100" style="border: 3px solid rgb(204, 204, 204)">
                        <h5 class="fw-bold m-2">Customer Information</h5>
                        <table class="my-4 mx-2">
                            <tbody>
                                <tr class="align-top">
                                    <td>Name:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$customer['firstname']?> <?=$customer['lastname']?></td>
                                </tr>
                                <tr class="align-top">
                                    <td>Email:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$customer['email']?></td>
                                </tr>
                                <tr class="align-top">
                                    <td>Contact Number:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$customer['contact_num']?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 pt-2 mt-4 w-100" style="border: 3px solid rgb(204, 204, 204)">
                        <h5 class="fw-bold m-2">Restaurant Information</h5>
                        <table class="my-4 mx-2">
                            <tbody>
                                <tr class="align-top">
                                    <td>Name:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$visit_restaurant['rest_name']?></td>
                                </tr>
                                <tr class="align-top">
                                    <td>Email:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$visit_restaurant['email']?></td>
                                </tr>
                                <tr class="align-top">
                                    <td>Address:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$visit_restaurant['address']?></td>
                                </tr>
                                <tr class="align-top">
                                    <td>City:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break"><?=$visit_restaurant['city']?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    </div>
                    <div class="col mx-2 mb-4 mt-0 pb-4 d-flex flex-column" style="border: 3px solid rgb(204, 204, 204)">
                        <h5 class="fw-bold m-2 pt-2">Reservation Details</h5>
                        <table class="my-4 mx-2">
                            <tbody>
                                <tr class="align-top">
                                    <td>Date:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break" id="confirmation-date"></td>
                                </tr>
                                <tr class="align-top">
                                    <td>Time:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break" id="confirmation-time"></td>
                                </tr>
                                <tr class="align-top">
                                    <td>Table Size:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td class="color-3-text text-break" id="confirmation-table-size"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="p-0 mx-2 flex-grow-1 d-flex flex-column">
                        <label>Special Request</label>
                        <textarea name="additional-request" placeholder="special request" class="form-control d-block mt-2 width-100p flex-grow-1" style="border-radius: 0px; height: 90%"></textarea>
                        </div>
                    </div>
                </div>
                <div class="m-5 mt-0 mb-4">
                <div class="mb-4 mt-0 px-3 mx-2" id="confirmation-pre-order" style="border: 3px solid rgb(204, 204, 204)">
                    <div class="m-2 pt-2">
                    <label class="form-check-label"><h5 class="fw-bold pt-1">Pre-order Food</h5></label>
                    </div>
                </div>
                <div class="form-switch form-check mb-4 mx-2 mt-0 px-3" id="voucher-parent" style="border: 3px solid rgb(204, 204, 204)">
                    <div class="m-2 pt-2">
                    <label class="form-check-label" for="voucher-switch"><h5 class="fw-bold pt-1 d-inline-block">Vouchers</h5>&nbsp;&nbsp;<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<p class='text-start'>-&nbsp;&nbsp;All vouchers can be used in any restaurant<br>-&nbsp;&nbsp;Only one voucher can be used at a time<br></p>"><i class="fas fa-question-circle"></i></span></label>
                    <input class="form-check-input float-end m-1" style="height: 20px; width: 40px; margin-top: 0.35rem !important;" id="voucher-switch" type="checkbox" role="switch">
                    </div>
                    <div class="bg-light p-3 mb-3 d-none" id="voucher-wrapper" style="border: 1px solid black">
                        <?php if (count($vouchers) == 0) { ?>
                            <div style="margin: 0.8rem; border-radius: 0.25rem; height: 296px;" id="voucher">
                                <table class="m-0 w-100">
                                <tr class="w-100">
                                    <td class="text-center align-middle" style="height: 230px;">No available voucher</td>  
                                </tr>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="list-group" style="max-height: 269.4px; min-height: 269.4px; margin: 0.8rem; <?=count($vouchers) <= 3 ? 'overflow:hidden; ' : 'overflow: auto; '?> border: 1px solid black; background-color: white;">
                            <?php foreach ($vouchers as $voucher_key => $voucher) { ?>
                                <div style="<?=$voucher_key == count($vouchers)-1 && count($vouchers) >= 3 ? 'border-bottom: none; ' : 'border-bottom: 1px solid grey; border-bottom-left-radius: unset; border-bottom-right-radius: unset; '?> cursor: pointer;" class="list-group-item list-group-item-action" onclick="applyRemoveVoucher(<?=$voucher['equal_price']?>, <?=$voucher['the_voucher_id']?>, $(this))">
                                    <div class="d-flex flex-wrap w-100 p-1">
                                        <i class="bi bi-ticket-perforated-fill voucher-icon mx-1 me-4 align-self-center text-success" style="font-size: 30px;"></i>
                                        <div class="flex-grow-1 pt-1">
                                        <input type="number" class="d-none" value="<?=$voucher['the_voucher_id']?>">
                                        <h4 class="mb-1 voucher-name"><strong><?=$voucher['name']?></strong></h4>
                                        <small>Valid Till <?=date('d/m/Y', strtotime($voucher['expired_date']))?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="my-5 mx-2 mb-4 d-flex justify-content-between">
                    <button type="button" class="mb-2 me-5 btn btn-secondary font-size-20 align-self-start" onclick="$('#confirmation').hide(); $('#checkbox-pre-order-food').prop('checked') ? $('#pre-order-food').show() : $('#time-slot').show(); $('html, body').animate({ scrollTop: 0 }, 0);">Back</button>
                    <div class="d-flex flex-wrap justify-content-end">
                        <button type="button" class="mb-2 btn btn-secondary font-size-20 cancel-btn" style="width: 106.6px;">Cancel</button>
                        <button type="button" class="mb-2 ms-3 btn btn-primary font-size-20 confirm-btn"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span>Confirm</button>
                    </div>
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
                    <h5 class="modal-title">Food Item Details</h5>
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

    <?php include_once '../../common/footer.php'; ?>
    </div>
    
    <?php include_once '../../../../script.php'; ?>

    <?php include_once '../../../script.php'; ?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>

    <script>
      function viewDetails(id) {
        $.ajax({ 
            url: '../../helpers/reservation.php',
            data: { 
              preorder_item_id: id,
              preorder_item_details: true
            },
            type: 'post',
            success: function(output){
                var json = $.parseJSON(output);

                var html = "";

                html += "<table class='table w-100 table-striped mt-1 align-middle' style='border: solid 2px black;'>";

                html += "<tr style='border-color: #dee2e6;'>";
                html += "<td width='40%' class='px-3 fw-bold'>Image</td>";
                html += "<td width='60%' class='p-3'><img style='min-width: 100px; max-width: 120px;' height='100' src='../../../../admin/uploads/food_menu_photo/" + json['Image'] + "'></td>";
                html += "</tr>";

                for (i in json){
                    if (i != 'Image' && i != 'Ingredient'){
                        html += "<tr style='border-color: #dee2e6;'>";
                        html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                        html += "<td width='60%' class='px-3 py-2'>" + json[i] + "</td>";
                        html += "</tr>";
                    }
                }

                if (!jQuery.isEmptyObject(json["Ingredient"])){
                  html += "<tr style='border-color: #dee2e6;'>";
                  html += "<td width='40%' class='px-3 fw-bold'>Ingredients</td>";
                  html += "<td width='60%' class='px-3 py-2'>"
                  html += "<ol class='m-0'>"
                  for (i in json['Ingredient']){
                    html += "<li>" + json['Ingredient'][i] + "</li>";
                  }
                  html += "</ol>"
                  html += "</td>"
                  html += "</tr>";
                }

                html += "</table>";
                
                $("#detailsModal .modal-body").html(html);
                
                $('#detailsModal').modal('show');
                $("nav, main, footer").addClass('blur');
            }
        });
      }

      function preOrderFoodOrderList(food_qty){
        var html = '';

        $.ajax({
          url: '../../helpers/reservation.php',
          data: {
            food_qty: food_qty,
            get_preorder_list_details: true
          },
          method: 'post',
          async: false,
          success: function (output){
            var json = $.parseJSON(output);
            var total = 0.0;

            html += '<div class="m-2 my-2 table-responsive" id="food_table">';
            html +=   '<table class="w-100 table mt-2" style="white-space: nowrap;" id="order-food-table">';
            html +=     '<thead>';               
            html +=       '<tr style="height: 50px; border: solid 2px black;">';
            html +=         '<th class="ps-4" colspan="2">Item</th>';
            html +=         '<th>Price</th>';
            html +=         '<th>Quantity</th>';
            html +=         '<th>Subtotal</th>';
            html +=       '</tr>';
            html +=     '</thead>';
            html +=     '<tbody style="border: solid 2px black; border-top: 0px; " class="align-middle">';
          
          for (i in json){
            html +=       '<tr class="my-2" style="border-color: #dee2e6;">';
            html +=         '<td class="ps-3 pe-2 py-2 text-center" style="width: 90px;">';
            html +=           '<img class="m-0" height="80" width="80" style="max-height: 80px; max-width: 80px;" src="../../../../admin/uploads/food_menu_photo/' + json[i]['image'] + '">';
            html +=         '</td>';
            html +=         '<td style="white-space: normal;">' + json[i]['item_name'] + '</td>';
            html +=         '<td class="price">RM ' + json[i]['price'] + '</td>';
            html +=         '<td>&nbsp;' + json[i]['qty'] + '</td>';
            html +=         '<td class="subtotal">RM ' + json[i]['total'] + '</td>';
            html +=       '</tr>';

            total += parseFloat(json[i]['total']);
          }

            html +=       '<tr>';
            html +=         '<td colspan="4" class="text-end fw-bold" style="border:0px;">Total</td>';
            html +=         '<td class="total" style="border:0px;">RM ' + total.toFixed(2) + '</td>';
            html +=       '</tr>';

          if ($('.voucher_value').length != 0){
            var grand_total_value = total - parseFloat($('.voucher_value').text().substring(5));
            if (grand_total_value < 0)
              grand_total_value = 0;

            html +=       $('.voucher_value').parent().html();
            html +=       '<tr style="border-color: #dee2e6;">';
            html +=         '<td colspan="4" class="fw-bold text-end">Grand Total</td>';
            html +=         '<td class="grand_total">RM ' + grand_total_value.toFixed(2) + '</td>';
            html +=        '</tr>';
          }

            html +=     '</tbody>';
            html +=   '</table>';
            html += '</div>';
          }
        });

        return html;
      }

      function resetVoucherList(){
        $('.voucher-icon').each(function() {
          $(this).removeClass('fas fa-check-circle');
          $(this).addClass('bi bi-ticket-perforated-fill');
          $(this).parent().parent().removeClass('bg-primary bg-opacity-25');
        });
      }

      function applyRemoveVoucher(price, voucher_id, this_element){
        if (this_element.hasClass('bg-primary bg-opacity-25')){
          $('#order-food-table tbody .voucher_value').parent().remove();
          $('#order-food-table tbody .grand_total').parent().remove();
          resetVoucherList();
        } else {
          var html = '';

          $('#order-food-table tbody .voucher_value').parent().remove();
          $('#order-food-table tbody .grand_total').parent().remove();

          html += '<tr style="border-color: #dee2e6;">';
          html +=     '<td colspan="4" class="fw-bold text-end" style="color: RGB(0, 196, 49); border:0px;">Voucher</td>';
          html +=     '<td class="voucher_id d-none"><input type="number" name="voucher_id" value="' + voucher_id + '"></td>';
          html +=     '<td class="voucher_value" style="border:0px;">- RM ' + price.toFixed(2) + '</td>';
          html += '</tr>';

          var grand_total = parseFloat($('.total').text().substring(3)) - price;
          if (grand_total < 0)
            grand_total = 0.0;
          
          html += '<tr style="border-color: #dee2e6;">';
          html +=     '<td colspan="4" class="fw-bold text-end">Grand Total</td>';
          html +=     '<td class="grand_total">RM ' + grand_total.toFixed(2) + '</td>';
          html += '</tr>';

          $('#order-food-table tbody').append(html);
          
          resetVoucherList();

          $('.voucher-icon').each(function() {
            if ($(this).next().find('input').val() == voucher_id){
              $(this).removeClass('bi bi-ticket-perforated-fill');
              $(this).addClass('fas fa-check-circle');
              $(this).parent().parent().addClass('bg-primary bg-opacity-25');
            }
          });
        }
      }


      $(document).ready(function() {
        $('#pre-order-food').hide();
        $('#confirmation').hide();
        $('#confirmation-pre-order').hide();
        $('#voucher-parent').hide();

        var food_qty = {};
        var number_of_food = 0;

        $('#date, #table-size').change(function() {
          if (!$('.timeslots label').hasClass('d-none')){
            if ($('#date').val() == '' || $('#table-size').val() == ''){
              $('.timeslots label').addClass('d-none');
              $('.timeslots input').addClass('d-none');
            } else
              $('#btn-find-table').trigger('click');
          }
        });
        
        $('#btn-find-table').click(function() {
          $('#date').removeClass('red-box-shadow is-invalid');
          $('#table-size').removeClass('red-box-shadow is-invalid');
          var error = false;

          if ($('#date').val() == ''){ 
            //error msg
            slideInMsg('error', 'Please select a date!');
            $('#date').addClass('red-box-shadow is-invalid');
            error = true;
          } else {
            var selected_date = new Date(new Date($('#date').val()).getTime()).setHours(0, 0, 0, 0);
            var current_date = new Date(new Date().getTime()).setHours(0, 0, 0, 0);
            if (selected_date < current_date){
              slideInMsg('error', 'Selected date must not be in the past!');
              $('#date').addClass('red-box-shadow is-invalid');
              error = true;
            }
          }
          
          if ($('#table-size').val() == ''){
            //error msg
            slideInMsg('error', 'Please enter the table size!');
            $('#table-size').addClass('red-box-shadow is-invalid');
            error = true;          
          } else if ($('#table-size').val() <= 0){
            slideInMsg('error', 'Table size must be greater than or equal to one!');
            $('#table-size').addClass('red-box-shadow is-invalid');
            error = true;
          } else if ($('#table-size').val() > <?=$max_table_size?>){
            if (<?=$max_table_size?> == 0)
              slideInMsg('error', 'There are currently no available tables in this restaurant. Please contact the restaurant staff to get more information.');
            else
              slideInMsg('error', 'The maximum table size for this restaurant is <?=$max_table_size?>!');
            $('#table-size').addClass('red-box-shadow is-invalid');
          }

          if (!error){
            $('.btn-danger').addClass('btn-outline-success hover-opacity');
            $('.btn-danger').removeClass('btn-danger');
            $('#time-slot .btn-secondary').addClass('btn-outline-success hover-opacity');
            $('#time-slot .btn-secondary').removeClass('btn-secondary');
            $('.btn-check').prop('disabled', false);
            $('.btn-check').prop('checked', false);

            $.ajax({
              url: '../../helpers/reservation.php',
              data: {
                  date: $('#date').val(),
                  table_size: $('#table-size').val(),
                  visit_rest_id: '<?php echo $_GET['visit_rest_id']?>'
              },
              type: 'post',
              success: function(output){
                var json = $.parseJSON(output);  

                for (i in json['full']){
                  $('#' + json['full'][i]).prop('disabled', true);
                  $('#label-' + json['full'][i]).addClass('btn-danger');
                  $('#label-' + json['full'][i]).removeClass('btn-outline-success hover-opacity');
                }

                for (i in json['unavailable']){
                  $('#' + json['unavailable'][i]).prop('disabled', true);
                  $('#label-' + json['unavailable'][i]).addClass('btn-secondary');
                  $('#label-' + json['unavailable'][i]).removeClass('btn-outline-success hover-opacity');
                }

                $('.timeslots label').removeClass('d-none');
                $('.timeslots input').removeClass('d-none');
              }
            });
          } else {
            if (!$('.timeslots label').hasClass('d-none')){
              $('.timeslots label').addClass('d-none');
              $('.timeslots input').addClass('d-none');
            }
          }
        });

        $('.timeslots input[type=checkbox]').change(function() {
          var current_element = $(this);
          var total_selected_time_slot = $('.timeslots input[type=checkbox]:checked').length
          if (total_selected_time_slot > <?=$settings['maximum-time-slot']?>){
            if (<?=$settings['maximum-time-slot']?> == 1)
              slideInMsg('error', 'You can only select maximum ' + '<?=$settings['maximum-time-slot']?>' + ' time slot!');
            else
              slideInMsg('error', 'You can only select maximum ' + '<?=$settings['maximum-time-slot']?>' + ' consecutive time slots!');
            $(this).prop('checked', false);
          } else if (total_selected_time_slot >= 2){
            var selected_time_slots = [];
            $('.timeslots input[type=checkbox]:checked').each(function() {
              selected_time_slots.push($(this).val());
            });
            for (var i = 1; i <= selected_time_slots.length-1; i++){
              if (parseInt(selected_time_slots[i]) - parseInt(selected_time_slots[i-1]) > 1){
                //error
                slideInMsg('error', 'You can only select consecutive time!');
                if ($(this).prop('checked'))
                  $(this).prop('checked', false);
                else
                  $(this).prop('checked', true);
              }
            }
          } 
          
          if ($(this).prop('checked')){
            $.ajax({
              url: '../../helpers/reservation.php',
              data: {
                date: $('#date').val(),
                time_slot: $(this).val(),
                check_past_time_slot: true
              },
              method: 'post',
              success: function(output){
                var json = $.parseJSON(output);

                if (json['hours-error']){
                  slideInMsg('error', json['hours-error']);
                  current_element.prop('checked', false);
                }
              }
            });
          }

          if (total_selected_time_slot > 0){
            //unset all timeslot first
            $('.timeslots input[type=checkbox]').prop('disabled', false);
            $('.timeslots input[type=checkbox]').parent().find('label').removeClass('btn-danger');
            $('.timeslots input[type=checkbox]').parent().find('label').addClass('btn-outline-success hover-opacity');

            var selected_time_slots = [];
            $('.timeslots input[type=checkbox]:checked').each(function() {
              selected_time_slots.push($(this).val());
            });

            $.ajax({
              url: '../../helpers/reservation.php',
              data: {
                date: $('#date').val(),
                time_slot_ids: selected_time_slots,
                restaurant: '<?php echo $_GET['visit_rest_id']?>',
                table_size: $('#table-size').val(),
                update_time_slots: true
              },
              method: 'post',
              success: function(output){
                var json = $.parseJSON(output);

                for (i in json['full']){
                  $('#' + json['full'][i]).prop('disabled', true);
                  $('#label-' + json['full'][i]).addClass('btn-danger');
                  $('#label-' + json['full'][i]).removeClass('btn-outline-success hover-opacity');
                }

                for (i in json['unavailable']){
                  $('#' + json['unavailable'][i]).prop('disabled', true);
                  $('#label-' + json['unavailable'][i]).addClass('btn-secondary');
                  $('#label-' + json['unavailable'][i]).removeClass('btn-outline-success hover-opacity');
                }
              }
            });
          } else {
            $('#btn-find-table').trigger('click');
          }
        });

        $('.time-slot-continue-btn').click(function () {
          if ($('.timeslots input[type=checkbox]:checked').length == 0 || $('.timeslots label').hasClass('d-none'))
            slideInMsg('error', 'Please select at least one time slot!');
          else{
            $('#time-slot').hide();

            if ($('#checkbox-pre-order-food').prop('checked'))
              $('#pre-order-food').show();
            else{
              $('.qty').val(0);
              $('.pre-order-food-continue-btn').trigger('click');
            }

            $('html, body').animate({ scrollTop: 0 }, 0);
          }     
        });

        $('.pre-order-food-continue-btn').click(function () {
            var date = new Date($('#date').val());
            var d = date.getDate();
            var m =  date.getMonth();
            m += 1;  // JavaScript months are 0-11
            var y = date.getFullYear();
            $('#confirmation-date').text(d + "/" + m + "/" + y);

            $('#confirmation-table-size').text($('#table-size').val());

            var selected_time_slots = [];
            $('.timeslots input[type=checkbox]:checked').each(function() {
              selected_time_slots.push($(this).val());
            });

            $.ajax({
                url: '../../helpers/reservation.php',
                data: {confirm_time_slot: JSON.stringify(Object.assign({}, selected_time_slots))},
                type: 'post',
                success: function(output){
                    var json_output = $.parseJSON(output);

                    $('#confirmation-time').text(json_output[0] + ' to ' + json_output[1]);
                }
            })

            food_qty = {};
            number_of_food = 0;
            $('#confirmation-pre-order').hide();
            $('#voucher-parent').hide();

            $('.qty').each(function () {
              if ($(this).val() > 0){
                food_qty[$(this).parent().find('.item_id').text()] = $(this).val();
                number_of_food++;
              }
            });

            if (number_of_food > 0){
              $('#confirmation-pre-order').show();
              $('#voucher-parent').show();
              var html = preOrderFoodOrderList(food_qty);
              $('#food_table').remove();
              $('#confirmation-pre-order').append(html);
            }

            $('#pre-order-food').hide();
            $('#confirmation').show();

            $('html, body').animate({ scrollTop: 0 }, 0);
        });

        $('.qty').change(function () {
          if ($(this).val() <= 0)
            $(this).val(0);
          else
            $(this).val(Math.floor($(this).val()));
        });

        $('#voucher-switch').change(function() {
          $('#voucher-wrapper').toggleClass('d-none');
        });

        $('.close-details-modal').click(function() {
          $("nav, main, footer").removeClass('blur');
        });

        $('.cancel-btn').click(function() {
          location = '../restaurant/profile.php?visit_rest_id=<?=$_GET['visit_rest_id']?>';
        });

        $(window).on('beforeunload', function() {
          return '';
        });

        $('.confirm-btn').click(function() {
          var selected_datetime_closed;
          var selected_time_slots = [];
          $('.timeslots input[type=checkbox]:checked').each(function() {
            selected_time_slots.push($(this).val());
          });

          //check if restaurant will be closed at this day & time
          $.ajax({
            url: '../../helpers/reservation.php',
            data: {
              rest_id: '<?=$_GET['visit_rest_id']?>',
              time_slot_ids: selected_time_slots,
              date: $('#date').val(),
              check_restaurant_closed: true
            },
            method: 'post',
            async: false,
            success: function(output) {
              selected_datetime_closed = output;
            }
          });

          if (selected_datetime_closed == 'true'){
            slideInMsg('error', 'This restaurant will be closed at the selected date and time. Please select another date and time.');
            return;
          }

          var data = {
            restaurant: '<?=$_GET['visit_rest_id']?>',
            time_slot_ids: selected_time_slots,
            table_size: $('#table-size').val(),
            date: $('#date').val(),
            additional_request: $('#confirmation textarea').val(),
            reservation_submit: $('.confirm-btn').val()
          };

          if (!jQuery.isEmptyObject(food_qty)){
            data['preorder_food_qty'] = food_qty;
          }

          if ($('input[name=voucher_id]').length != 0)
            data['voucher_id'] = $('input[name=voucher_id]').val();
          
          $.ajax({
            url: '../../helpers/reservation.php',
            data: data,
            method: 'post',
            beforeSend: function(){
              $('#spinner').removeClass('d-none');
              $('#spinner').parent().prop('disabled', true);
            },
            success: function(){
              $(window).off('beforeunload');
              location = '../restaurant/profile.php?visit_rest_id=<?=$_GET['visit_rest_id']?>';
            }
          });
        });
      });

    </script>

  </body>
</html>


<?php 

  } else { 
    $visit_restaurant = getRestaurant($connection, $_GET['visit_rest_id']);
    $_SESSION['doc_title'] = "<title>Reservation &VerticalLine; " . $visit_restaurant['rest_name'] . "</title>";
    $_SESSION['redirect'] = '../reservation/' . basename($_SERVER['REQUEST_URI']);
    header('Location: ../warning/login_first.php');
  }

?>