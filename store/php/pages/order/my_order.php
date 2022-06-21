<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../../../admin/php/helpers/order.php';
    include_once '../../../../admin/php/helpers/reservation.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/table.php';
    include_once '../../../../admin/php/helpers/rating_review.php';
    
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

      $pre_orders = getCustomerOrders($connection, $_SESSION['login_cus_id'], ['preorder' => true, 'status' => 'Upcoming', 'asc_desc' => 'DESC']);
      $previous_orders = getCustomerOrders($connection, $_SESSION['login_cus_id'], ['statuses' => ['Completed', 'Cancelled', 'Disabled'], 'asc_desc' => 'DESC']);
      $dine_in_orders = getCustomerOrders($connection, $_SESSION['login_cus_id'], ['not_statuses' => ['Completed', 'Cancelled', 'Disabled', 'Upcoming'], 'asc_desc' => 'DESC']);
      date_default_timezone_set("Asia/Kuala_Lumpur");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
    
    <style>
        .column{
            max-height: 572px;
            min-height: 572px;
            overflow-y: auto;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
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

        .box-style{
          overflow-x: auto;
          max-width: 100%;
        }

        #upcoming-dine-in-order-wrapper .box-style{
            max-height: 274px;
            min-height: 274px;
            overflow-y: auto;
        }

        .col table{
          width: 95%;
        }

        #voucher{
            border: 1px solid black; 
            max-height: 250px;
            overflow-y: auto;
            background-color: white;
        }

        #orderModal table, #detailsModal table, #order-food-table{
            border: 1px solid black !important;
        }

        #orderModal tr, #orderModal th, #detailsModal tr, #detailsModal th, #order-food-table tr, #order-food-table th{
            border-color: #dee2e6 !important;
        }
        
        #orderModal thead, #orderModal tbody, #detailsModal thead, #detailsModal tbody, #order-food-table thead, #order-food-table tbody{
            border-width: 2px;
        }

        #editOrderModal4 .enabled:hover{
            background-color: rgb(245, 232, 253);
        }

        #editOrderModal4 .enabled, #editOrderModal4 .disabled{
          box-shadow: 1px 1px 8px 2px rgba(0, 0, 0, 0.2);
        }

        @media (min-width: 992px){
          #editOrderModal4 .wrapper{
            padding-left: 1.5rem;
            padding-right: 1.5rem;
          }
        }

        .nav-tabs .nav-link.active{
            border-color: black black white;
        }

        .nav-tabs{
            border-bottom: 1px solid black;
        }

        @media (max-width: 576px) {
            .nav-tabs .nav-link{
                border: 1px solid black !important;
                border-bottom: none !important;
            }

            .nav-tabs .nav-link.active{
                background-color: black;
                color: white;
            }
        }

        #nav-tabContent{
            border-left: 1px solid black;
            border-right: 1px solid black;
            border-bottom: 1px solid black;
        }

        body{
            height: 100%;
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
                <h1 style="cursor: default; font-size: 3rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Orders</h1>
                <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
            </div>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 m-auto mb-3 mt-5" style="margin-bottom: 100px !important;">
          <div class="col p-0 px-4 mb-4 mt-0">
            <div class="box-style color-2 column">
              <div class="color-2 px-3 pt-2 w-100">
                <h5 class="fw-bold m-2">Previous Orders</h5>
                <table class="m-3 text-nowrap">
                <?php $counter = 1; ?>
                  <tbody>
                    <?php foreach ($previous_orders as $previous_order) { ?>
                      <tr style="height: 127px;">
                        <td class="align-top pt-3" width="7%"><?=$counter?>.</td>
                        <td class="align-top pt-3 pe-3">
                          <?=getRestaurant($connection, $previous_order['rest_id'])['rest_name']?><br>
                          <?=date('d/m/Y', strtotime($previous_order['datetime']))?><br>
                          <?php if ($previous_order['table_id'] != '0') { ?>
                            <?php $this_table_info = getTable($connection, $previous_order['table_id']);?>
                            table (<?=empty($this_table_info) ? json_decode($previous_order['deleted_data'], true)['table_num_name'] : $this_table_info['table_num_name']?>)<br>
                          <?php } else { ?>
                            <span class="text-danger">* </span>Self Pickup<br>
                          <?php } ?>
                          <?php if ($previous_order['status'] == 'Cancelled') { ?>
                            <span class="badge bg-danger">Cancelled</span>
                          <?php } elseif ($previous_order['status'] == 'Disabled') { ?>
                            <span class="badge bg-secondary">Disabled</span>
                          <?php } else { ?>
                            <span class="badge bg-primary">Completed</span>
                          <?php } ?>
                        </td>
                        <td>
                          <?php if ($previous_order['reservation_id'] == '0') { ?>
                          <button type="button" style="float: right;" class="btn bg-success bg-opacity-75 ms-2" onclick="viewDetails(<?=$previous_order['order_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                          <?php } else { ?>
                          <button type="button" style="float: right;" class="btn bg-success bg-opacity-75 ms-2" onclick="viewPreOrderDetails(<?=$previous_order['order_id']?>,<?=$previous_order['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                          <?php } ?>


                          <?php $date_diff = (array)date_diff(date_create_from_format('Y-m-d', date('Y-m-d')), date_create_from_format('Y-m-d', date('Y-m-d', strtotime($previous_order['datetime'])))); ?>
                          <?php if (empty($previous_order['rating']) && $previous_order['status'] == 'Completed' && $date_diff['days'] <= 3 && empty($previous_order['reply'])) { ?>
                          <button type="button" style="float: right;" class="btn bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Rate" onclick="showRatingModal(<?=$previous_order['order_id']?>);"><i class="fas fa-star font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                          <?php } elseif ($previous_order['status'] == 'Completed' && $date_diff['days'] <= 3 && empty($previous_order['reply'])) { ?>
                          <button type="button" style="float: right; width: 48.5px; height: 40.84px;" class="btn bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Review" onclick="showRatingModal(<?=$previous_order['order_id']?>);">
                            <span class="fa-stack" style="line-height: 25px; height: 25px; width: 22.5px; margin-bottom: 2px;">
                              <i class="fas fa-star fa-stack-2x text-white font-size-20" style="line-height: 25px;"></i>
                              <i class="fas fa-check fa-stack-1x fa-inverse text-dark" style="font-size: 11px; margin-top: 1.5px; margin-left: 0.5px;"></i>
                            </span>
                          </button>
                          <?php } ?>
                        </td>
                      </tr>
                      <?php $counter++; ?>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>  
          <div class="col p-0 px-4 mb-4 mt-0" id="upcoming-dine-in-order-wrapper">
            <div class="box-style p-0 mt-0 color-2">
                <div class="color-2 px-3 pt-2 w-100">
                <h5 class="fw-bold m-2">Pre-Orders</h5>
                <table class="m-3 text-nowrap">
                <?php $counter = 1; ?>
                    <tbody>
                    <?php foreach ($pre_orders as $pre_order) { ?>
                        <tr style="height: 127px;">
                        <td width="7%" class="align-top pt-3"><?=$counter?>.</td>
                        <?php
                            $reservation = getReservation($connection, $pre_order['reservation_id']);
                            if (!empty($reservation['startTime']) && !empty($reservation['endTime']))
                              $time = date('h:i a', strtotime($reservation['startTime'])) . ' to ' . date('h:i a', strtotime($reservation['endTime']));
                            else{
                              $deleted_data = json_decode($reservation['deleted_data'], true);
                              $time = date('h:i a', strtotime($deleted_data['startTime'])) . ' to ' . date('h:i a', strtotime($deleted_data['endTime']));
                            }
                        ?>
                        <td class="align-top pt-3 pe-3">
                            <?=getRestaurant($connection, $pre_order['rest_id'])['rest_name']?><br>
                            <?=date('d/m/Y', strtotime($reservation['date']))?><br> 
                            <?=$time?><br>
                            <?php $this_table_info = getTable($connection, $reservation['table_id']);?>
                            table (<?=empty($this_table_info) ? json_decode($pre_order['deleted_data'], true)['table_num_name'] : $this_table_info['table_num_name']?>)<br>
                            <?php if ($pre_order['status'] == 'Upcoming') { ?>
                            <span class="badge bg-success">Upcoming</span>
                            <?php } ?>
                        </td>
                        <td>
                            <button type="button" style="float: right; min-width: 48.5px;" class="btn bg-primary bg-opacity-75 ms-2" onclick="showEditOrderModal('<?=$pre_order['order_id']?>');" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                            <button type="button" style="float: right;" class="btn bg-success bg-opacity-75" onclick="viewPreOrderDetails(<?=$pre_order['order_id']?>,<?=$pre_order['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                        </td>
                        </tr>
                        <?php $counter++; ?>
                    <?php } ?>
                    </tbody>
                </table>
                </div>
            </div>
            <div class="box-style p-0 mt-4 mt-0 color-2">
                <div class="color-2 px-3 pt-2 w-100">
                <h5 class="fw-bold m-2">Dine-in Orders &amp; Self Pickup Orders</h5>
                <table class="m-3 text-nowrap">
                <?php $counter = 1; ?>
                    <tbody>
                    <?php foreach ($dine_in_orders as $dine_in_order) { ?>
                        <tr style="height: 127px;">
                        <td class="align-top pt-3" width="7%"><?=$counter?>.</td>
                        <td class="align-top pt-3 pe-3">
                            <?=getRestaurant($connection, $dine_in_order['rest_id'])['rest_name']?><br>
                            <?=date('d/m/Y', strtotime($dine_in_order['datetime']))?><br>
                            <?php if ($dine_in_order['table_id'] != '0') { ?>
                              <?php $this_table_info = getTable($connection, $dine_in_order['table_id']);?>
                              table (<?=empty($this_table_info) ? json_decode($dine_in_order['deleted_data'], true)['table_num_name'] : $this_table_info['table_num_name']?>)<br>
                            <?php } else { ?>
                              <span class="text-danger">* </span>Self Pickup<br>
                            <?php } ?>
                            <?php if ($dine_in_order['status'] == 'Pending') { ?>
                            <span class="badge bg-warning bg-opacity-75" style="color: #664d03;">Pending</span>
                            <?php } elseif ($dine_in_order['status'] == 'Processing') { ?>
                            <span class="badge bg-info bg-opacity-50" style="color: #055160;">Processing</span>
                            <?php } elseif ($dine_in_order['status'] == 'Served') { ?>
                            <span class="badge bg-success">Served</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($dine_in_order['reservation_id'] == '0') { ?>
                            <button type="button" style="float: right; min-width: 48.5px;" class="btn bg-primary bg-opacity-75 ms-2" onclick="showEditOrderModal('<?=$dine_in_order['order_id']?>');" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                            <button type="button" style="float: right;" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$dine_in_order['order_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                            <?php } else { ?>
                            <button type="button" style="float: right; min-width: 48.5px;" class="btn bg-primary bg-opacity-75 ms-2" onclick="showEditOrderModal('<?=$dine_in_order['order_id']?>');" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                            <button type="button" style="float: right;" class="btn bg-success bg-opacity-75" onclick="viewPreOrderDetails(<?=$dine_in_order['order_id']?>,<?=$dine_in_order['reservation_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                            <?php } ?>
                        </td>
                        </tr>
                        <?php $counter++; ?>
                    <?php } ?>
                    </tbody>
                </table>
                </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <div class="modal fade p-0" id="detailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-target="#orderModal" data-bs-toggle="modal">View Order</button>
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

    <div class="modal fade p-0" id="ratingModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Write A Review</h5>
                    <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" form="rating-review-form">Submit</button>
                    <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade p-0" id="editOrderModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order</h5>
                    <button type="button" class="btn-close close-edit-order-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../helpers/order.php" method="POST" id="edit-order-form">
                        <div class="mb-3" id="order-type-container">
                          <input type="text" style="display: none;" name="submit_check_order_store">
                          <input type="text" style="display: none;" name="order_id" id="order_id">
                          <input type="text" style="display: none;" name="hidden_food_qty" id="hidden_food_qty">
                          <label class="form-label fw-bold d-block">Order Type</label>
                            <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="order_type" id="dine-in" value="dine_in">
                              <label class="form-check-label" for="dine-in">Dine-In</label>
                            </div>
                            <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="order_type" id="self-pickup" value="self_pickup">
                              <label class="form-check-label" for="self-pickup">Self Pickup</label>
                            </div>
                        </div>
                        <div class="mb-3 d-none" id="table-container">
                          <label for="table-num-name" class="form-label fw-bold">Table Number (Name) <span class="required-star">*</span></label>
                          <select class="form-select" id="table-num-name" name="table-num-name">
                          </select>
                          <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-3 d-none" id="pickup-time-container">
                          <label for="pickup_time" class="form-label fw-bold">Pickup Time <span class="required-star">*</span></label>
                          <input type="time" class="form-control" name="pickup_time" id="pickup_time">
                          </select>
                          <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-3" id="order-container">
                          <label class="form-label fw-bold d-inline-block">Food Items <span class="required-star">*</span>&nbsp;&nbsp;&nbsp;</label>
                          <button type="button" class="btn btn-sm btn-outline-primary" data-bs-target="#editOrderModal4" onclick="updateFoodList($('#order_id').val());" data-bs-toggle="modal"><small>Edit</small></button>
                        </div>
                        <div class="mb-3" id="request-container">
                            <label for="special-request" class="form-label fw-bold">Special Request</label>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                        </div>
                        <div class="mb-4" id="offer-voucher-container">
                            
                        </div>
                        <div class="mb-3">
                          <button type="button" class="btn btn-outline-danger" onclick="$('#editOrderModal2').find('.char-counter').text('1000 characters');" data-bs-target="#editOrderModal2" data-bs-toggle="modal">Cancel Order</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" form="edit-order-form"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" role="status" aria-hidden="true"></span>Save changes</button>
                    <button type="button" class="btn btn-secondary close-edit-order-modal" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade p-0" id="editOrderModal2" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order</h5>
                    <button type="button" class="btn-close close-edit-order-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="font-size-18 fw-bold text-center">Are you sure you want to cancel the order?</p>
                    <div class="mb-3" id="remarks-container">
                        <label for="remarks" class="form-label">Please tell us the reason</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="6"></textarea>
                        <small><div class="char-counter" style="color: grey;"></div></small>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="yes-btn" onclick="cancelOrder($('#order_id').val()); "><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner_2" role="status" aria-hidden="true"></span>Yes</button>
                  <button class="btn btn-secondary" id="no-btn" data-bs-target="#editOrderModal" data-bs-toggle="modal">No</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade p-0" id="editOrderModal4" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Order</h5>
                    <button type="button" class="btn-close close-edit-order-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="continue-btn">Continue</button>
                  <button class="btn btn-secondary" id="no-btn-2" data-bs-target="#editOrderModal" data-bs-toggle="modal">Back</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade p-0" id="offerModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Offer Details</h5>
                    <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary apply-remove-btn">Apply</button>
                    <button type="button" class="btn btn-secondary" data-bs-target="#editOrderModal" data-bs-toggle="modal">Cancel</button>
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
        $('.modal .form-message').remove();
      }

      function rateOrder(this_element){
        //reset stars
        $('.rating-star i').addClass('text-secondary');
        $('.rating-star i').removeClass('text-danger');

        //update stars
        var star_index = $('.rating-star').index(this_element);
        for (let i = 1; i <= star_index+1; i++) {
          $('.rating-star:nth-child(' + i + ') i').toggleClass('text-secondary text-danger');
        }
      }

      function showRatingModal(order_id){
          $.ajax({
            url: '../../helpers/order.php',
            data: {
              order_id: order_id,
              get_order_rating_review: true
            },
            method: 'post',
            success: function(output) {
              var json = $.parseJSON(output);
              
              var html = '';

                html += '<form action="../../helpers/order.php" method="post" id="rating-review-form">';
                html +=   '<input type="number" style="display: none;" name="order_id" value="' + order_id + '">';
                html +=   '<div class="d-flex justify-content-center">';
                html +=     '<div class="font-size-35" style="cursor: default">';
              
              for (let i = 0; i <= 4; i++) {
                html +=       '<button type="button" onclick="rateOrder($(this));" class="p-1 rating-star"><i class="fas fa-star text-secondary text-opacity-75"></i></button>';
              }
                
                html +=     '</div>';
                html +=   '</div>';
                html +=   '<div class="mt-3">';
                html +=     '<textarea class="form-control" placeholder="Write a review..." id="review" name="review" rows="6">' + (json['review'] ? json['review'] : '' ) + '</textarea>';
                html +=     '<small><div class="review-char-counter char-counter" style="color: grey;">2000 characters</div></small>'
                html +=   '</div>';
                html += '</form>';

                $("#ratingModal .modal-body").html(html);

                if (json['rating']){
                  rateOrder($('.rating-star:nth-child(' + json['rating'] + ')'));
                }

                $('#ratingModal').modal('show'); 
                $('nav, footer, main').addClass('blur');
            }
          });

          
      }

      function viewDetails(id) {
        $.ajax({ 
            url: '../../../../admin/php/helpers/order.php',
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
                $("#detailsModal .modal-title").text("Order Details");

                html = '';

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

                                html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + (j == 'Name' ? ' white-space: normal;' : '') + "'>" + (j == 'Name' ? '<a href="food_profile.php?food_item_id=' + json['Order'][i]['food_id'] + '" target="_blank" style="color: blue;">' : '') + json['Order'][i][j] + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '&nbsp;<span class=text-danger>+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
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

                $("#orderModal .modal-body").html(html);
                
                $('#detailsModal').modal('show');
                $('nav, footer, main').addClass('blur');
            }
        });
      }

      function viewPreOrderDetails(id, reservation_id) {
        $.ajax({
            url: '../../../../admin/php/helpers/reservation.php',
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
                $("#detailsModal .modal-title").text("Reservation Details");
                
                $('#detailsModal').modal('show');
                $('nav, footer, main').addClass('blur');
            }
        });

        $.ajax({ 
            url: '../../../../admin/php/helpers/order.php',
            data: { modal_order_id: id },
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

                                html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + (j == 'Name' ? ' white-space: normal;' : '') + "'>" + (j == 'Name' ? '<a href="food_profile.php?food_item_id=' + json['Order'][i]['food_id'] + '" target="_blank" style="color: blue;">' : '') + json['Order'][i][j] + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '&nbsp;<span class=text-danger>+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
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

                html += "<table class='table w-100 text-nowrap table-striped mt-1 align-middle' style='min-width: 407px;'>";

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

      function showEditOrderModal(order_id){
        $.ajax({
          url: '../../helpers/order.php',
          data: {
            order_id: order_id,
            get_data_for_edit: true
          },
          method: 'post',
          success: function(output){
            var json = $.parseJSON(output);

            var order_info = json['order_info'];
            var tables_info = json['tables_info'];

            if (order_info['reservation_id'] != '0'){
              $('#editOrderModal #order-type-container').hide();
              $('#editOrderModal #table-container').hide();
            } else {
              $('#editOrderModal #order-type-container').show();
              $('#editOrderModal #table-container').show();
            }

            //check if the order is processing
            if (order_info['status'] == 'Processing'){
              slideInMsg('error', 'This order is being proceesed, you cannot edit this order.');
            } else if (order_info['status'] == 'Served'){
              slideInMsg('error', 'This order has already served, you cannot edit this order.');
            } else {
              //order type
              if (order_info['table_id'] != '0'){
                $('#order-type-container #dine-in').prop('checked', true);
                $('#order-type-container #dine-in').trigger('change');

                //table
                $('#table-container select').children().remove();
                $('#table-container select').append('<option value="*">-- Please Select --</option>');

                for (i in tables_info){
                  if (order_info['table_id'] == tables_info[i]['table_id']){
                    $('#table-container select').append('<option value="' + order_info['table_id'] + '" selected="selected">' + order_info['table_num_name'] + '</option>');
                  } else {
                    $('#table-container select').append('<option value="' + tables_info[i]['table_id'] + '">' + tables_info[i]['table_num_name'] + '</option>');
                  }
                }
              } else {
                $('#order-type-container #self-pickup').prop('checked', true);
                $('#order-type-container #self-pickup').trigger('change');
                $('#pickup-time-container #pickup_time').val(json['pickup_time']);
                $('#table-container select').children().remove();
                $('#table-container select').append('<option value="*">-- Please Select --</option>');

                for (i in tables_info){
                  $('#table-container select').append('<option value="' + tables_info[i]['table_id'] + '">' + tables_info[i]['table_num_name'] + '</option>');
                }
              }

              //food items
              $('#order-container table').parent().remove();
              var html = '';

              html += "<div class='table-responsive'>";
              html +=   "<table class='table w-100 mt-1 mb-0 align-middle' id='order-food-table' style='white-space: nowrap;'>";
              html +=     "<tr class='table-secondary'>";
              html +=       "<th width='30%' class='px-3 fw-bold'>Name</td>";
              html +=       "<th width='20%' class='px-3 fw-bold py-2'>Price</td>";
              html +=       "<th width='20%' class='px-3 fw-bold py-2'>Quantity</td>";
              html +=       "<th width='30%' class='px-3 fw-bold py-2'>Subtotal</td>";
              html +=     "</tr>";

              var red_total = false;
              for (i in json['Order']){
                if (i != 'Total' && i != 'promo_code' && i != 'voucher'){
                    html += "<tr>";
                    for (j in json['Order'][i]){
                      if (j != 'food_id' && j != 'Promotions'){
                          if (typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined")
                              red_total = true;

                          html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + (j == 'Name' ? ' white-space: normal;' : '') + "'>" + (j == 'Name' ? '<a href="food_profile.php?food_item_id=' + json['Order'][i]['food_id'] + '" target="_blank" style="color: blue;">' : '') + (j == 'Quantity' ? '<span class=qty>' : '') + json['Order'][i][j] + (j == 'Quantity' ? '</span>' : '') + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '<span class=text-danger>&nbsp;+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
                      } else if (j == 'food_id')
                          html += "<td class='d-none item_id'>" + json['Order'][i][j] + "</td>";                      
                    }
                     html += "</tr>";
                } else if (i == 'Total') {
                    html += "<tr>";
                    html +=   "<td colspan='3' class='px-3 fw-bold text-end' style='border:0px;'>Total</td>";
                    html +=   "<td class='px-3 total" + (red_total ? ' text-danger' : '') + "' style='border:0px;'>" + json['Order'][i] + "</td>";
                    html += "</tr>";
                } else if (i == 'promo_code') {
                    html += "<tr>";
                    html +=   "<td class='d-none'><input type='number' id='promo_code_id' name='promo_code_id' value='" + json['Order'][i]['id'] + "'></td>"
                    html +=   "<td colspan='3' class='px-3 fw-bold text-end promo_code_name' style='color: RGB(0, 196, 49); border:0px;'>Promo Code (" + json['Order'][i]['name'] + ")</td>";
                    html +=   "<td class='px-3 promo_code_value' style='border:0px;'>" + json['Order'][i]['discount'] + "</td>";
                    html += "</tr>";
                    html += "<tr>";
                    html +=   "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                    html +=   "<td class='px-3 grand_total'>RM " + ((parseFloat(json['Order']['Total'].substring(3)) - parseFloat(json['Order'][i]['discount'].substring(5))).toFixed(2)) + "</td>";
                    html += "</tr>";
                } else {
                    var grand_total_value = parseFloat(json['Order']['Total'].substring(3)) - parseFloat(json['Order'][i]['price'].substring(5));
                        if (grand_total_value < 0)
                            grand_total_value = 0;

                    html += "<tr>";
                    html +=   "<td class='d-none'><input type='number' id='voucher_id' name='voucher_id' value='" + json['Order'][i]['id'] + "'></td>"
                    html +=   "<td colspan='3' class='px-3 fw-bold text-end' style='color: RGB(0, 196, 49); border:0px;'>Voucher</td>";
                    html +=   "<td class='px-3 voucher_value' style='border:0px;'>" + json['Order'][i]['price'] + "</td>";
                    html += "</tr>";
                    html += "<tr>";
                    html +=   "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                    html +=   "<td class='px-3 grand_total'>RM " + grand_total_value.toFixed(2) + "</td>";
                    html += "</tr>";
                }
              }
                      
              html += "</table>";
              html += "<p class='d-block text-danger'></p>";
              html += "</div>";

              $('#order-container').append(html);

              //special request
              $('#request-container textarea').remove();
              $('<textarea class="form-control" id="special-request" name="special-request" rows="6">' + order_info['additional_notes'] + '</textarea>').insertBefore('#request-container small');

              var limit = 1000;
              var current_char = $('#request-container textarea').val().length;
              $('#request-container textarea').parent().find('.char-counter').text((limit - current_char).toString() + ' characters');

              //assign order id
              $('#order-type-container #order_id').val(order_info['order_id']);

              //offer voucher section
              var data = {
                status: order_info['status'],
                rest_id: order_info['rest_id'],
                total: parseFloat(json['Order']['Total'].substring(3))
              };

              if ($('#order-food-table #promo_code_id').length != 0){
                data['promo_id'] = $('#order-food-table #promo_code_id').val();
              }

              if ($('#order-food-table #voucher_id').length != 0){
                data['voucher_id'] = $('#order-food-table #voucher_id').val();
              }

              $.ajax({
                url: 'my_order_offer_voucher.php',
                data: data,
                method: 'post',
                success: function(html) {
                  $('#offer-voucher-container').html(html);
                  $('#editOrderModal').modal('show');
                  $("nav, main, footer").not('#offer-voucher-container nav').addClass('blur');
                }
              });
            }
          }
        });
      }

      function updateFoodList(order_id){
        var food_qty_json = {};

        $('#editOrderModal .item_id').each(function () {
          food_qty_json[$(this).text()] = $(this).parent().find('.qty').text();
        });

        $.ajax({
          url: '../../helpers/order.php',
          data: {
            the_order: order_id,
            update_food_list: true            
          },
          method: 'post',
          success: function(output) {
            var json = $.parseJSON(output);

            var html = '';

            $.each(json, function(category_name, food_menu_items) {
              if (category_name == 'order_status'){
                return false;
              }
                  html += '<div>';
                  html +=   '<span style="font-size: 18px;">' + category_name + '</span>';
                  html +=   '<hr class="my-2">';
                  html += '</div>';
                  html += '<div class="mt-0 pb-5 wrapper">';

              $.each(food_menu_items, function(key, food_menu_item) {
                if (food_menu_item['status'] == '1') {
                  html +=   '<div class="enabled pt-3 mt-4">';
                } else {
                  html +=   '<div class="disabled pt-3 mt-4" style="opacity: 0.5; pointer-event: none; cursor: default;">';
                }

                  html +=     '<div class="container">';
                  html +=       '<div class="row row-cols-2 g-2 g-lg-3 w-100">';
                  html +=         '<div class="col-7 mb-3 d-flex">';
                  html +=           '<div class="d-inline-block">';                  
                  html +=           '<img height="80" width="80" src="../../../../admin/uploads/food_menu_photo/' + food_menu_item['image'] + '" style="vertical-align: unset;">';
                  html +=           '</div>';
                  html +=           '<div class="d-inline-block ms-3" style="vertical-align: top;">';
                  html +=           food_menu_item['item_name'] + '<br>';

                if (food_menu_item['buy_free'] && json['order_status'] != 'Upcoming'){
                  html +=           '<span class="text-danger"><small>' + food_menu_item['buy_free'] + '</small></span>' + '<br>';
                }

                  html +=           '<span><small><a href="food_profile.php?food_item_id=' + food_menu_item['item_id'] + '" target="_blank" style="color: grey;">Details</a></small></span>';
                  html +=           '</div>';
                  html +=         '</div>';
                  html +=         '<div class="col-3 text-end" style="white-space: nowrap;">';

                if (food_menu_item['discount_price'] && json['order_status'] != 'Upcoming'){
                  html +=             '<span class="text-decoration-line-through">RM ' + parseFloat(food_menu_item['price']).toFixed(2) + '</span>';
                  html +=             '<br><span class="text-danger"><small>RM ' + parseFloat(food_menu_item['discount_price']).toFixed(2) + '&nbsp;</small></span>';
                } else {
                  html +=           'RM ' + parseFloat(food_menu_item['price']).toFixed(2);
                }

                  html +=         '</div>';
                  html +=         '<div class="col-2 text-end">';
                  html +=           '<input type="text" class="d-none item_id" value="' + food_menu_item['item_id'] + '">';

                var current_food_id = food_menu_item['item_id'];

                if (current_food_id in food_qty_json){
                  var value = "value='" + food_qty_json[current_food_id] + "'";
                } else {
                  var value = "value='0'";
                }

                if (food_menu_item['status'] == '1') {
                  html +=             "<input type='number' min='0' class='form-control qty d-inline-block ps-2 pe-1' style='width: 55px; border-radius: 0px; border: 0px !important; background-color: #dee2e6' " + value + ">";
                } else {
                  html +=            "<input type='number' min='0' class='form-control qty d-inline-block ps-2 pe-1' style='width: 55px; border-radius: 0px; border: 0px !important; background-color: #dee2e6' " + value + " disabled>";
                }

                  html +=           '</div>';
                  html +=         '</div>';
                  html +=       '</div>';
                  html +=     '</div>';
              });
                  html += '</div>';
            });

            $('#editOrderModal4 .modal-body').html(html);
          }
        });
      }

      function showOfferDescription(promo_id, this_element){
        $.ajax({
            url: '../../../../admin/php/helpers/order.php',
            data: {
                promo_id: promo_id,
                get_offer_description: true
            },
            method: 'post',
            success: function(output) {
                $("#offerModal .modal-body").html(output);
                if (!this_element.find('.offer-icon').hasClass('fa-check-circle')){
                    $("#offerModal .apply-remove-btn").attr('onclick', 'applyOffer(\'' + promo_id + '\')');
                    $("#offerModal .apply-remove-btn").text('Apply');
                    $("#offerModal .apply-remove-btn").addClass('btn-primary');
                    $("#offerModal .apply-remove-btn").removeClass('btn-danger');
                } else {
                    $("#offerModal .apply-remove-btn").attr('onclick', 'removeOffer()');
                    $("#offerModal .apply-remove-btn").text('Remove');
                    $("#offerModal .apply-remove-btn").removeClass('btn-primary');
                    $("#offerModal .apply-remove-btn").addClass('btn-danger');
                }
                
                $('#offerModal').modal('show');
            }
        });
      }

      function resetOfferList(){
        $('.offer-icon').each(function() {
            $(this).removeClass('fa-check-circle');

            if (parseFloat($('.total').text().substring(3)) < parseFloat($(this).parent().find('input').val())){
                $(this).removeClass('fa-lock-open text-success');
                $(this).addClass('fa-lock text-danger');
            } else {
                $(this).addClass('fa-lock-open text-success');
                $(this).removeClass('fa-lock text-danger');
            }

            $(this).parent().parent().removeClass('bg-primary bg-opacity-25');
        });
      }

      function resetVoucherList(){
        $('.voucher-icon').each(function() {
          $(this).removeClass('fas fa-check-circle');
          $(this).addClass('bi bi-ticket-perforated-fill');
          $(this).parent().parent().removeClass('bg-primary bg-opacity-25');
        });
      }

      function applyOffer(promo_id){
        $.ajax({
            url: '../../../../admin/php/helpers/order.php',
            data: {
                promo_id: promo_id,
                cus_id: "<?=$_SESSION['login_cus_id']?>",
                current_total: parseFloat($('.total').text().substring(3)),
                apply_offer_checking_store: true
            },
            method: 'post',
            success: function(output) {
                var json = $.parseJSON(output);

                if (json['error']){
                    slideInMsg('error', json['error']);
                } else if (json['success']) {
                    $('#offerModal').modal('hide');
                    $('#editOrderModal').modal('show');

                    var html = '';

                    $('#order-food-table tbody .promo_code_value').parent().remove();
                    $('#order-food-table tbody .voucher_value').parent().remove();
                    $('#order-food-table tbody .grand_total').parent().remove();

                    html += '<tr style="border-color: #dee2e6;">';
                    html +=     '<td colspan="3" class="fw-bold text-end promo_code_name" style="color: RGB(0, 196, 49); border:0px;">Promo Code (' + json['success']['name'] + ')</td>';
                    html +=     '<td class="promo_code_id d-none"><input type="number" name="promo_code_id" id="promo_code_id" value="' + promo_id + '"></td>';
                    html +=     '<td class="px-3 promo_code_value" style="border:0px;">- RM ' + json['success']['value'].toFixed(2) + '</td>';
                    html += '</tr>';

                    html += '<tr style="border-color: #dee2e6;">';
                    html +=     '<td colspan="3" class="px-3 fw-bold text-end">Grand Total</td>';
                    html +=     '<td class="px-3 grand_total">RM ' + (parseFloat($('.total').text().substring(3)) - json['success']['value']).toFixed(2) + '</td>';
                    html += '</tr>';

                    $('#order-food-table tbody').append(html);
                    
                    resetOfferList();
                    resetVoucherList();

                    $('.offer-name').each(function() {
                        if ($(this).find('strong').text() == json['success']['name']){
                            $(this).parent().parent().find('i').removeClass('fa-lock-open');
                            $(this).parent().parent().find('i').addClass('fa-check-circle');
                            $(this).parent().parent().parent().addClass('bg-primary bg-opacity-25');
                        }
                    });
                }
            }
        });
      }

      function removeOffer(){
        resetOfferList();

        //remove promotion from food table
        $('#order-food-table .promo_code_value').parent().remove();
        $('#order-food-table .grand_total').parent().remove();

        $('#offerModal').modal('hide');
        $('#editOrderModal').modal('show');
      }

      function applyRemoveVoucher(price, voucher_id, this_element){
        if (this_element.hasClass('bg-primary bg-opacity-25')){
          $('#order-food-table tbody .voucher_value').parent().remove();
          $('#order-food-table tbody .grand_total').parent().remove();
          resetVoucherList();
        } else {
          var html = '';

          $('#order-food-table tbody .promo_code_value').parent().remove();
          $('#order-food-table tbody .voucher_value').parent().remove();
          $('#order-food-table tbody .grand_total').parent().remove();

          html += '<tr style="border-color: #dee2e6;">';
          html +=     '<td class="d-none"><input type="number" name="voucher_id" id="voucher_id" value="' + voucher_id + '"></td>';
          html +=     '<td colspan="3" class="px-3 fw-bold text-end" style="color: RGB(0, 196, 49); border:0px;">Voucher</td>';
          html +=     '<td class="voucher_value px-3" style="border:0px;">- RM ' + price.toFixed(2) + '</td>';
          html += '</tr>';

          var grand_total = parseFloat($('.total').text().substring(3)) - price;
          if (grand_total < 0)
              grand_total = 0.0;
          
          html += '<tr style="border-color: #dee2e6;">';
          html +=     '<td colspan="3" class="px-3 fw-bold text-end">Grand Total</td>';
          html +=     '<td class="px-3 grand_total">RM ' + grand_total.toFixed(2) + '</td>';
          html += '</tr>';

          $('#order-food-table tbody').append(html);
          
          resetOfferList();
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

      function cancelOrder(order_id){
        $.ajax({
          url: '../../helpers/order.php',
          data: {
            order_id: order_id,
            remarks: $('#remarks').val(),
            cancel_order: true
          },
          method: 'post',
          beforeSend: function() {
            $('#spinner_2').removeClass('d-none');
            $('#spinner_2').parent().prop('disabled', true);
          },
          success: function(){
            location.reload();
          }
        });
      }


      $(document).ready(function() {
        $('.close-edit-order-modal').on('click', function() {
            $("nav, main, footer").removeClass('blur');
            $('.modal .form-message').remove();
            $('.modal .text-danger').text('');
            $('.modal .form-control, .modal .form-select').each(function() {
              $(this).removeClass('red-box-shadow is-invalid');
              $(this).val('');
            });
        });

        $('.close-details-modal').click(function() {
          $("nav, main, footer").removeClass('blur');
        });

        $(document).on('input', '#search-offers', function() {
          var input_val = $(this).val().toLowerCase();
          var shown_div = 0;
          $('.offer-name').each(function () {
              if ($(this).text().toLowerCase().indexOf(input_val) >= 0){
                  $(this).parent().parent().parent().show();
                  shown_div++;
              } else {
                  $(this).parent().parent().parent().hide();
              }

              if (shown_div < 3){
                  $(this).parent().parent().parent().css({
                      'border-bottom': '1px solid grey',
                      'border-bottom-left-radius': 'unset',
                      'border-bottom-right-radius': 'unset'
                  });
              } else if ($(this).parent().parent().parent().is(".list-group-item:visible:last")) {
                  $(this).parent().parent().parent().css({
                      'border-bottom': 'none',
                      'border-bottom-left-radius': '',
                      'border-bottom-right-radius': ''
                  });
              }
          });
        });

        $('#continue-btn').click(function() {
          var new_food_qty = {};
          $("#editOrderModal4 .qty").each(function () {
            if ($(this).val() > 0){
              new_food_qty[$(this).parent().find('.item_id').val()] = $(this).val();
            }
          });

          $.ajax({
            url: '../../helpers/order.php',
            data: {
              new_food_qty: new_food_qty,
              the_order_id: $('#order_id').val(),
              get_new_food_qty_info: true
            },
            method: 'post',
            success: function(output){
              var json = $.parseJSON(output);

              //food items
              var html = '';

              html += "<div class='table-responsive'>";
              html += "<table class='table w-100 mt-1 mb-0 align-middle' id='order-food-table' style='white-space: nowrap;'>";
              html += "<tr class='table-secondary'>";
              html += "<th width='30%' class='px-3 fw-bold'>Name</td>";
              html += "<th width='20%' class='px-3 fw-bold py-2'>Price</td>";
              html += "<th width='20%' class='px-3 fw-bold py-2'>Quantity</td>";
              html += "<th width='30%' class='px-3 fw-bold py-2'>Subtotal</td>";
              html += "</tr>";

              for (i in json['Order']){
                if (i != 'Total'){
                    html += "<tr>";
                    for (j in json['Order'][i]){
                      if (j != 'food_id' && j != 'Promotions')
                        html += "<td class='px-3 py-2" + (j == 'Subtotal' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-danger' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? ' text-decoration-line-through' : '') + (j == 'Name' ? ' white-space: normal;' : '') + "'>" + (j == 'Name' ? '<a href="food_profile.php?food_item_id=' + json['Order'][i]['food_id'] + '" target="_blank" style="color: blue;">' : '') + (j == 'Quantity' ? '<span class=qty>' : '') + json['Order'][i][j] + (j == 'Quantity' ? '</span>' : '') + (j == 'Name' ? '</a>' : '') + (j == 'Name' && typeof(json['Order'][i]['Promotions']['buy_free']) != "undefined" ? '<br><span class=text-danger>' + json['Order'][i]['Promotions']['buy_free'] + '</span>' : '') + (j == 'Quantity' && typeof(json['Order'][i]['Promotions']['buy_free_value']) != "undefined" && json['Order'][i]['Promotions']['buy_free_value'] != 0 ? '<span class=text-danger>&nbsp;+ ' + json['Order'][i]['Promotions']['buy_free_value'].toString() + ' Free</span>' : '') + (j == 'Price' && typeof(json['Order'][i]['Promotions']['discount_price']) != "undefined" ? '<br><span class=text-danger style=text-decoration:none!important;display:inline-block;>RM ' + parseFloat(json['Order'][i]['Promotions']['discount_price']).toFixed(2) + '</span>' : '') + "</td>";
                      else if (j == 'food_id')
                        html += "<td class='d-none item_id'>" + json['Order'][i][j] + "</td>";
                    }
                    html += "</tr>";
                } else {
                    html += "<tr>";
                    html += "<td colspan='3' class='px-3 fw-bold text-end' style='border:0px;'>Total</td>";
                    html += "<td class='px-3 total' style='border:0px;'>" + json['Order'][i] + "</td>";
                    html += "</tr>";
                }
              }

              if (!json['Order']){
                html += "<tr>";
                html += "<td colspan='4' style='height: 41.5px;'></td>";
                html += "</tr>";

                if ($('.voucher_value').length != 0){
                  resetVoucherList();
                  slideInMsg('info', 'The use of voucher has been cancelled as no food item is selected.')
                }

                $("#nav-offer-tab").trigger('click');
              } else if ($('.promo_code_value').length != 0 && parseFloat(json['Order']['Total'].substring(3)) >= parseFloat($('.offer-icon.fa-check-circle').parent().find('input').val())){
                html += "<tr>";
                html += $('.promo_code_value').parent().html();
                html += "</tr>";
                html += "<tr>";
                html += "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";
                html += "<td class='px-3 grand_total'>RM " + ((parseFloat(json['Order']['Total'].substring(3)) - parseFloat($('.promo_code_value').text().substring(5))).toFixed(2)) + "</td>";
                html += "</tr>";
              } else if ($('.voucher_value').length != 0){
                html += "<tr>";
                html += $('.voucher_value').parent().html();
                html += "</tr>";
                html += "<tr>";
                html += "<td colspan='3' class='px-3 fw-bold text-end'>Grand Total</td>";

                var grand_total = parseFloat(json['Order']['Total'].substring(3)) - parseFloat($('.voucher_value').text().substring(5));
                if (grand_total < 0)
                  grand_total = 0.0

                html += "<td class='px-3 grand_total'>RM " + (grand_total.toFixed(2)) + "</td>";
                html += "</tr>";
              }

              if (typeof(json['Order']) === 'undefined'){
                var total = 'RM 0.0';
              } else {
                var total = json['Order']['Total'];
              }

              if ($('.total').length == 0)
                $('#order-container table tbody').append("<td class='total'>RM 0.0</td>");

              $('.total').text(total);

              //if total < min spend
              if (parseFloat(total.substring(3)) < parseFloat($('.offer-icon.fa-check-circle').parent().find('input').val())){
                  removeOffer();
                  slideInMsg('info', 'The use of offer has been cancelled as the order total is lower than the minimum spend.')
              } else {
                  resetOfferList();

                  if ($('.promo_code_name').length != 0){
                    var promo_code_name = $('.promo_code_name').text().substring($('.promo_code_name').text().indexOf('(')+1, $('.promo_code_name').text().length-1);
                    $('.offer-name').each(function() {
                        if ($(this).find('strong').text() == promo_code_name){
                            $(this).parent().parent().find('i').removeClass('fa-lock-open');
                            $(this).parent().parent().find('i').addClass('fa-check-circle');
                            $(this).parent().parent().parent().addClass('bg-primary bg-opacity-25');
                        }
                    });
                  }
              }
                      
              html += "</table>";
              html += "<p class='d-block text-danger'></p>";
              html += "</div>";

              $('#order-container table').parent().remove();
              $('#order-container').append(html);

              $('#editOrderModal4').modal('hide');
              $('#editOrderModal').modal('show');
            }
          });
        });

        $('#dine-in').change(function() {
            $('#table-container').removeClass('d-none');
            $('#pickup-time-container').addClass('d-none');
            $('#table-num-name').prop('disabled', false);
            $('#pickup_time').prop('disabled', true);
        });

        $('#self-pickup').change(function() {
            $('#table-container').addClass('d-none');
            $('#pickup-time-container').removeClass('d-none');
            $('#table-num-name').prop('disabled', true);
            $('#pickup_time').prop('disabled', false);
        });

        $(document).on('change', '.qty', function() {
          if ($(this).val() < 0){
            $(this).val(0);
          } else {
            $(this).val(Math.floor($(this).val()));
          }
        })

        $(document).on('input', 'textarea', function(){
            var current_char = $(this).val().length;
            var limit = 1000;

            if ($(this).next().find('div').hasClass('review-char-counter')){
              limit = 2000;
            }

            if (current_char > limit) {
                $(this).val($(this).val().substring(0, limit));
            }
            var char_left = limit - current_char;
            if (char_left < 0)
                char_left = 0;
            $(this).parent().find('.char-counter').text(char_left.toString() + ' characters');
            
        });

        $("#edit-order-form").submit(function(event){
          event.preventDefault();

          var food_qty_json = {};

          $('#editOrderModal .item_id').each(function () {
            food_qty_json[$(this).text()] = $(this).parent().find('.qty').text();
          });

          $('#hidden_food_qty').val(JSON.stringify(food_qty_json));

          $('#editOrderModal .form-message').remove();
          $('#editOrderModal input, #editOrderModal select').removeClass('is-invalid red-box-shadow');
          $('#editOrderModal .text-danger').not('#order-food-table .text-danger').text('');

          $.ajax({
            url: '../../helpers/order.php',
            data: $('#edit-order-form select, #edit-order-form input:not([type=radio]), #edit-order-form textarea, #edit-order-form input[type=radio]:checked'),
            method: 'post',
            success: function(output) {
                var json = $.parseJSON(output);
                
                if (json['error']){
                    $('<div class="alert form-message alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#edit-order-form');
                    $.each(json['error'], function(key, value) {
                        if (key != 'order-food-table')
                          $('#' + key).addClass('red-box-shadow is-invalid');
                        $('#' + key).parent().find('p').text(value);
                    });
                    $('#editOrderModal .modal-body').animate({ scrollTop: 0 }, 0);
                } else {
                    $('#spinner').removeClass('d-none');
                    $('#spinner').parent().prop('disabled', true);
                    $('input[name="submit_check_order_store"]').attr('name', "submit_form_order_store");
                    $('#edit-order-form').unbind().submit();
                }
            }
          });
        });

        $(document).on('submit', "#rating-review-form", function(event){
          event.preventDefault();

          var rating = $('#rating-review-form .fas.fa-star.text-danger').length;
          if (rating == 0){
            slideInMsg('error', 'Please select at least one star to rate your experience.');
          } else {
            $.ajax({
              url: '../../helpers/order.php',
              data: {
                order_id: $('#rating-review-form').find('input[name=order_id]').val(),
                rating: rating,
                review: $('#review').val(),
                submit_rating_review: true
              },
              method: 'post',
              success: function() {
                  window.location.reload();
              }
            });
          }
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