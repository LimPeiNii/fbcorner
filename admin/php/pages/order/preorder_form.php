<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/reservation.php';
        include_once '../../helpers/table.php';
        include_once '../../helpers/order.php';
        include_once '../../helpers/food_menu.php';
        include_once '../../helpers/promotion.php';
        include_once '../../helpers/user.php';
        include_once '../../../../store/php/helpers/customer.php';
        include_once '../../../../store/php/helpers/voucher.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'order/preorder', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['order_id'])) {
                $order = getOrder($connection, $_GET['order_id']);
                $the_customer = getCustomer($connection, $order['cus_id']);
            } elseif (isset($_GET['reservation_id'])) {
                $reservation = getReservation($connection, $_GET['reservation_id']);
                $the_customer = getCustomer($connection, $reservation['cus_id']);
            } else {
                header("Location: preorder.php");
                exit;
            }

            $all_food_items = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['order_by' => 'item_name', 'asc_desc' => 'ASC']);
            $promo_codes = getPromotions($connection, $_SESSION['login_rest_id'], ['type_code' => 'promo_code', 'status' => '1']);

            if (isset($order) && $order['voucher_id'] != '0'){
                $vouchers = getVouchers($connection, $order['cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_other_in_use' => ['this_voucher_id' => $order['voucher_id']]]);
            } else  {
                $vouchers = getVouchers($connection, $the_customer['cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_in_use' => true]);
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            table{
                width: 100%;
            }

            #order-food-table thead, #order-food-table tbody, #order-food-table tfoot{
                border: 2px solid #ccc !important;
            }

            #order-food-table tr, #order-food-table td{
                border-style: unset !important;
            }

            .qty{
                border: 2px solid rgba(217, 233, 254) !important;
            }

            #food_item_name, #food_item_name tbody, #food_item_name tbody tr{
                border-color: transparent !important;
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

            .tab-content{
                border-left: 1px solid black;
                border-right: 1px solid black;
                border-bottom: 1px solid black;
            }

            #voucher{
                border: 1px solid black; 
                max-height: 250px;
                overflow-y: auto;
                background-color: white;
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
                <?php if (isset($order) && $order['status'] == 'Upcoming') { ?>
                <a href="preorder.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <?php } elseif (isset($order) && $order['status'] != 'Upcoming') { ?>
                <a href="order.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <?php } else { ?>
                <a href="../reservation/reservation.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <?php } ?>
                <button type="submit" form="order-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($order) && $order['status'] == 'Upcoming') { ?>
                        Edit Pre-Order
                    <?php } elseif (isset($order) && $order['status'] != 'Upcoming') { ?>
                        Edit Order
                    <?php } elseif (!isset($order) && $reservation['remarks'] != 'Ongoing') { ?>
                        Add Pre-Order
                    <?php } elseif (!isset($order) && $reservation['remarks'] == 'Ongoing') { ?>
                        Add Order
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($order)) { ?>
                        <form action="../../helpers/order.php?order_id=<?=$order['order_id']?>" class="m-5" id="order-form" method="POST">
                    <?php } else { ?>    
                        <form action="../../helpers/order.php?reservation_id=<?=$reservation['reservation_id']?>" class="m-5" id="order-form" method="POST">
                    <?php } ?>
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_check_preorder">
                            <?php if (isset($reservation)) { ?>
                            <input type="text" style="display: none;" name="reservation_id" value="<?=$reservation['reservation_id']?>">
                            <?php } else { ?>
                            <input type="text" style="display: none;" name="order_id" value="<?=$order['order_id']?>">
                            <?php } ?>
                            <input type="text" style="display: none;" name="cus-id" id="cus_id" value="<?=$the_customer['cus_id']?>">
                            <label for="cus-name" class="form-label fw-bold">Customer Name</label>
                            <input class="form-control" name="cus_name" id="cus-name" value="<?=$the_customer['firstname'] . ' ' . $the_customer['lastname']?>" disabled>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="text" class="form-control" id="email" value="<?=$the_customer['email']?>" disabled>
                        </div>
                        <div class="mb-4">
                            <label for="contact-num" class="form-label fw-bold">Contact Number</label>
                            <input type="text" class="form-control" id="contact-num" value="<?=$the_customer['contact_num']?>" disabled>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Food Item <span class="required-star">*</span></label>
                            <div class="table-responsive" id="order-food-wrapper">
                                <table class="table table-striped mt-1 align-middle mb-0" id="order-food-table" style="white-space: nowrap;">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="ps-3">Name</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $food_item_row_counter = 1; 
                                            if (isset($order)) {
                                                $total = 0.0;
                                                $promotions = json_decode($order['promotions'], true);
                                                $food_items = json_decode($order['item_quantity'], true);
                                                foreach ($food_items as $item_id => $qty) {
                                                    $item_info = getFoodMenuItem($connection, $item_id);
                                                    if ($order['status'] != 'Completed' && $order['status'] != 'Disabled' && $order['status'] != 'Cancelled' && $order['status'] != 'Upcoming'){
                                                        $promotion_infos = getPromotions($connection, $_SESSION['login_rest_id'], ['food_item_id' => $item_info['item_id'], 'status' => '1']);
                                                    }

                                                    $buy_free = '';
                                                    $discount_price = '';
                                                    if (!empty($promotion_infos)){
                                                        foreach ($promotion_infos as $promotion_info){
                                                            if (isset($promotion_info['settings_data'])){
                                                                $settings_data = json_decode($promotion_info['settings_data'], true);
                                                            }
                                                            if ($promotion_info['type_code'] == 'BOGO'){
                                                                $buy_free = 'Buy 1 Get 1 Free';
                                                            } elseif ($promotion_info['type_code'] == 'multi_buy'){
                                                                $buy_free = 'Buy ' . $settings_data['amount_1'] . ' Get ' . $settings_data['amount_2'] . ' Free';
                                                            } elseif ($promotion_info['type_code'] == 'percent_off'){
                                                                $discount_price = $item_info['price'] * (1-((int)$settings_data['percentage'] / 100));
                                                            } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                                                                $discount_price = $settings_data['discounted_price'];
                                                            } 
                                                        }
                                                    }
                                        ?>
                                            <tr class="my-2">
                                                <td style="white-space: normal;" class="ps-3">
                                                    <?=$item_info['item_name']?>
                                                    <?php if (isset($buy_free) && !empty($buy_free)) { ?>
                                                        <br><span class="text-danger">(<?=$buy_free?>)</span>
                                                    <?php } ?>
                                                </td>
                                                <td class="price">
                                                    <span class="<?=(isset($discount_price) && !empty($discount_price) ? 'text-decoration-line-through' : 'price_data')?>">RM <?=number_format($item_info['price'], 2, '.', '')?></span>
                                                    <?php if (isset($discount_price) && !empty($discount_price)) { ?>
                                                        <br><span class="text-danger price_data">RM <?=number_format($discount_price, 2, '.', '')?></span>
                                                    <?php } ?>                                        
                                                </td>
                                                <td>
                                                    <input type="text" class="d-none item_id" value="<?=$item_info['item_id']?>">
                                                    &nbsp;&nbsp;<input type="number" min="0" name="food_qty[<?=$item_info['item_id']?>]" class="form-control qty d-inline-block ps-2 pe-1" style="width: 45px; border-radius: 0px; border: 0px !important; background-color: #dee2e6;" value="<?=$qty?>">
                                                </td>
                                                <td class="subtotal">
                                                    <?php if (isset($discount_price) && !empty($discount_price)) { ?>
                                                        <span class="text-danger subtotal_data">RM <?=number_format(round($discount_price * $qty, 2), 2, '.', '')?></span>
                                                    <?php } else { ?>
                                                        <span class="subtotal_data">RM <?=number_format($item_info['price'] * $qty, 2, '.', '')?></span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php 
                                                if (isset($discount_price) && !empty($discount_price)) {
                                                    $total += number_format(round($discount_price * $qty, 2), 2, '.', '');
                                                } else {
                                                    $total += number_format($item_info['price'] * $qty, 2, '.', '');
                                                } 
                                            ?>
                                            <?php $food_item_row_counter++; ?>
                                            <?php } ?>
                                        <?php } else { ?>                       
                                            <td colspan="4" style="height: 42px;" class="empty_td"></td>
                                        <?php } ?>                       
                                    </tbody>
                                    <tfoot <?=(isset($order) ? '' : 'class="d-none"')?>>
                                        <tr>
                                        <?php if (isset($order)) { ?>
                                            <td colspan="3" class="fw-bold text-end">Total</td>
                                            <td class="total">RM <?=number_format($total, 2, '.', '')?></td>
                                        <?php } else { ?>
                                            <td colspan="3" class="fw-bold text-end">Total</td>
                                            <td class="total">RM 0.00</td>
                                        <?php } ?>
                                        </tr>
                                        <?php if (isset($order) && isset($promotions['promo_code'])) { ?>
                                        <tr>
                                            <td colspan="3" class="fw-bold text-end promo_code_name" style="color: RGB(0, 196, 49);">Promo Code (<?=$promotions['promo_code']['promo_code_name']?>)</td>
                                            <td class="promo_code_id d-none"><input type="number" name="promo_code_id" value="<?=$promotions['promo_code']['id']?>"></td>
                                            <td class="promo_code_value">- RM <?=$promotions['promo_code']['actual_discount']?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="fw-bold text-end">Grand Total</td>
                                            <td class="grand_total">RM <?=number_format($total - $promotions['promo_code']['actual_discount'], 2, '.', '');?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php if (isset($order) && $order['voucher_id']) { ?>
                                        <?php $this_voucher_info = getVoucher($connection, $order['voucher_id']); ?>
                                        <tr>
                                            <td class="d-none"><input type="number" name="voucher_id" id="voucher_id" value="<?=$order['voucher_id']?>"></td>
                                            <td colspan="3" class="fw-bold text-end" style="color: RGB(0, 196, 49);">Voucher</td>
                                            <td class="voucher_value">- RM <?=number_format($this_voucher_info['equal_price'], 2, '.', '')?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="fw-bold text-end">Grand Total</td>
                                            <td class="grand_total">RM <?=number_format(max(0, $total - $this_voucher_info['equal_price']), 2, '.', '')?></td>
                                        </tr>
                                        <?php } ?>
                                    </tfoot>
                                </table>
                                <p class="d-block text-danger"></p>
                            </div>
                            <div class="mt-3">
                            <table id="food_item_name">
                                <tbody>
                                <tr>
                                    <td class="align-top">
                                        <input class="form-control" autocomplete="off" style="height: 43px;" list="fooddatalistOptions" placeholder="Food Item Name" id="food-item" onchange="checkFoodStatus(value, $(this));">
                                        <datalist id="fooddatalistOptions">
                                            <?php foreach ($all_food_items as $food_item) { ?>
                                                <?php $promotion_infos = getPromotions($connection, $_SESSION['login_rest_id'], ['food_item_id' => $food_item['item_id'], 'status' => '1']);?>
                                                <option data-value="<?=$food_item['item_id']?>,<?=$food_item['price']?>,<?=$food_item['status']?>,<?=str_replace('"', "'", json_encode($promotion_infos))?>" value="<?=$food_item['item_name']?>">
                                            <?php } ?>
                                        </datalist>
                                        <p class="d-block text-danger"></p>
                                    </td>
                                    <td width="5%" style="max-width: 50px;" class="align-top"><button type="button" id="add-food-btn" class="btn bg-info text-white mx-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus-circle font-size-20" style="line-height: 30px;"></i></button>
                                        <p class="d-block text-danger"></p></td>
                                </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="special-request" class="form-label fw-bold">Special Request</label>
                            <?php if (isset($order)) { ?>
                                <textarea class="form-control" id="special-request" name="special-request" rows="6"><?=$order['additional_notes']?></textarea>
                            <?php } else { ?>
                                <textarea class="form-control" id="special-request" name="special-request" rows="6"></textarea>                    
                            <?php } ?>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                        </div>
                        <div class="mb-4">
                            <?php if (isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming')) { ?>
                            <label for="search-vouchers" class="form-label fw-bold">Vouchers</label>
                            <?php } else { ?>
                            <label for="search-offers" class="form-label fw-bold">Offers and Vouchers</label>
                            <?php } ?>
                            <div id="offer-voucher">
                            <nav>
                            <div class="nav nav-tabs<?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? '' : ' flex-column flex-sm-row'?>" id="nav-tab-offer-voucher" role="tablist">
                                <button class="nav-link flex-sm-fill text-sm-center<?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? ' d-none' : ' active'?>" id="nav-offer-tab" data-bs-toggle="tab" data-bs-target="#nav-offer" type="button" role="tab" aria-controls="nav-offer" <?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? 'aria-selected="false"' : 'aria-selected="true"'?>>Offers</button>
                                <button class="nav-link text-sm-center <?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? ' active' : ' flex-sm-fill'?>" <?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? 'style="background-color: unset; color: unset; min-width: 50%;"' : ''?> id="nav-voucher-tab" data-bs-toggle="tab" data-bs-target="#nav-voucher" type="button" role="tab" aria-controls="nav-voucher" <?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? 'aria-selected="true"' : 'aria-selected="false"'?>>Vouchers</button>
                            </div>
                            </nav>
                            <div class="tab-content bg-light" id="nav-tabContent">
                                <div class="tab-pane fade p-3<?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? ' d-none' : ' active show'?>" id="nav-offer" role="tabpanel" aria-labelledby="nav-offer-tab">
                                    <nav class="navbar navbar-light">
                                        <div class="container-fluid">
                                            <div class="d-flex w-100">
                                            <input class="form-control" name="" id="search-offers" type="search" placeholder="Search" aria-label="Search">
                                            </div>
                                        </div>
                                    </nav>
                                    <?php if ($promo_codes) { ?>
                                        <div class="list-group" style="max-height: 269.4px; min-height: 269.4px; margin: 0.8rem; <?=count($promo_codes) <= 3 ? 'overflow:hidden; ' : 'overflow: auto; '?>; border: 1px solid black; background-color: white;">
                                        <?php foreach ($promo_codes as $promo_key => $promo_code) { ?>
                                            <?php $details = json_decode($promo_code['settings_data'], true); ?>
                                            <div style="<?=$promo_key == count($promo_codes)-1 && count($promo_codes) >= 3 ? 'border-bottom: none; ' : 'border-bottom: 1px solid grey; border-bottom-left-radius: unset; border-bottom-right-radius: unset; '?> cursor: pointer; min-height: 89.8px;" class="list-group-item list-group-item-action <?=isset($order) && isset($promotions['promo_code']) && $promotions['promo_code']['id'] == $promo_code['promotion_id'] ? ' bg-primary bg-opacity-25' : ''?>" data-bs-target="#offerModal" data-bs-toggle="modal"  onclick="showOfferDescription('<?=$promo_code['promotion_id']?>', $(this));" aria-current="true">
                                                <div class="d-flex flex-wrap w-100 p-2">
                                                    <input type="number" class="d-none" value="<?=(float)$details['min_price']?>">
                                                    <?php if (isset($order) && isset($promotions['promo_code']) && $promotions['promo_code']['id'] == $promo_code['promotion_id']) { ?>
                                                    <i class="fas fa-check-circle offer-icon mx-1 me-3 align-self-center text-success" style="font-size: 25px;"></i>
                                                    <?php } elseif ($total < (float)$details['min_price']) { ?>
                                                    <i class="fas fa-lock offer-icon mx-1 me-3 align-self-center text-danger" style="font-size: 25px;"></i>
                                                    <?php } else { ?>
                                                    <i class="fas fa-lock-open offer-icon mx-1 me-3 align-self-center text-success" style="font-size: 25px;"></i>
                                                    <?php } ?>
                                                    <div class="flex-grow-1">
                                                    <h4 class="mb-1 offer-name"><strong><?=$details['promo_code_name']?></strong></h4>
                                                    <small>Minimum spend RM <?=$details['min_price']?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        </div>
                                    <?php } else { ?>
                                        <div wfd-invisible="true" style="height: 296px; border: 1px solid black; margin: 0.8rem; border-radius: 0.25rem; background-color: white;">
                                            <table class="m-0 w-100">
                                                <tbody>
                                                    <tr class="w-100">
                                                        <td class="text-center align-middle" style="height: 294px;">No available offers</td>  
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="tab-pane fade p-3<?=isset($_GET['reservation_id']) || (isset($order) && $order['status'] == 'Upcoming') ? ' active show' : ''?>" id="nav-voucher" role="tabpanel" aria-labelledby="nav-voucher-tab">
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
                                            <div style="<?=$voucher_key == count($vouchers)-1 && count($vouchers) >= 3 ? 'border-bottom: none; ' : 'border-bottom: 1px solid grey; border-bottom-left-radius: unset; border-bottom-right-radius: unset; '?> cursor: pointer; min-height: 89.8px;" class="list-group-item list-group-item-action<?=isset($order) && $order['voucher_id'] == $voucher['the_voucher_id'] ? ' bg-primary bg-opacity-25' : ''?>" onclick="applyRemoveVoucher(<?=$voucher['equal_price']?>, <?=$voucher['the_voucher_id']?>, $(this))">
                                                <div class="d-flex flex-wrap w-100 p-1">
                                                    <?php if (isset($order) && $order['voucher_id'] == $voucher['the_voucher_id']) { ?>
                                                    <i class="fas fa-check-circle voucher-icon mx-1 me-4 align-self-center text-success" style="font-size: 30px;"></i>
                                                    <?php } else { ?>
                                                    <i class="bi bi-ticket-perforated-fill voucher-icon mx-1 me-4 align-self-center text-success" style="font-size: 30px;"></i>
                                                    <?php } ?>
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
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($reservation)) { ?>
                                    <option value="<?=$reservation['remarks'] == 'Ongoing' ? 'Pending' : 'Upcoming' ?>" selected="selected"><?=$reservation['remarks'] == 'Ongoing' ? 'Pending' : 'Upcoming' ?></option>
                                <?php } else { ?>
                                <?php $statuses = ['Upcoming', 'Pending', 'Processing', 'Served', 'Completed', 'Cancelled']; ?>
                                <?php if (isset($order) && $order['status'] == 'Served') { ?>
                                    <option value="Served" selected="selected">Served</option>
                                    <option value="Completed">Completed</option>
                                <?php } else { ?>
                                    <?php foreach ($statuses as $status) { ?>
                                    <?php if (isset($order) && $order['status'] == $status) { ?>
                                        <option value="<?=$status?>" selected="selected"><?=$status?></option>
                                    <?php } else { ?>
                                        <option value="<?=$status?>"><?=$status?></option>
                                    <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="remarks" class="form-label fw-bold">Remarks</label>
                            <?php if (isset($order)) { ?>
                                <textarea class="form-control" name="remarks" id="remarks" rows="4"><?=$order['remarks']?></textarea>
                            <?php } else { ?>
                                <textarea class="form-control" name="remarks" id="remarks" rows="4"></textarea>                    
                            <?php } ?>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="text-center mt-3">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if ((int)$has_permission['has_permission']){ ?>
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
        <script>
            function close_message() {
                $('.form-message').remove();
                $('#order-form').removeClass('mt-4');
            }

            function showOfferDescription(promo_id, this_element){
                $.ajax({
                    url: '../../helpers/order.php',
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
                        
                        $('.transition').removeClass('transition');
                        $('#offerModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
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
                    url: '../../helpers/order.php',
                    data: {
                        promo_id: promo_id,
                        cus_id: $('#cus_id').val(),
                        current_total: parseFloat($('.total').text().substring(3)),
                        apply_offer_checking: true
                    },
                    method: 'post',
                    success: function(output) {
                        var json = $.parseJSON(output);

                        if (json['error']){
                            $('#offerModal .modal-body .form-message').remove();
                            $('#offerModal .modal-body').prepend('<div class="alert form-message mb-2 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i><button type="button" class="btn-close btn float-end" data-bs-dismiss="alert" aria-label="Close"></button>&nbsp;&nbsp;' + json['error'] + '</div>');
                        } else if (json['success']) {
                            $('#offerModal').modal('hide');
                            $('.close-details-modal').trigger('click');

                            var html = '';

                            $('#order-food-table tfoot .promo_code_value').parent().remove();
                            $('#order-food-table tfoot .voucher_value').parent().remove();
                            $('#order-food-table tfoot .grand_total').parent().remove();

                            html += '<tr>';
                            html +=     '<td colspan="3" class="fw-bold text-end promo_code_name" style="color: RGB(0, 196, 49);">Promo Code (' + json['success']['name'] + ')</td>';
                            html +=     '<td class="promo_code_id d-none"><input type="number" name="promo_code_id" value="' + promo_id + '"></td>';
                            html +=     '<td class="promo_code_value">- RM ' + json['success']['value'].toFixed(2) + '</td>';
                            html += '</tr>';

                            html += '<tr>';
                            html +=     '<td colspan="3" class="fw-bold text-end">Grand Total</td>';
                            html +=     '<td class="grand_total">RM ' + (parseFloat($('.total').text().substring(3)) - json['success']['value']).toFixed(2) + '</td>';
                            html += '</tr>';

                            $('#order-food-table tfoot').append(html);
                            
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
                $('#order-food-table tfoot .promo_code_value').parent().remove();
                $('#order-food-table tfoot .grand_total').parent().remove();

                $('#offerModal').modal('hide');
                $('.close-details-modal').trigger('click');
            }

            function applyRemoveVoucher(price, voucher_id, this_element){
                if (this_element.hasClass('bg-primary bg-opacity-25')){
                    $('#order-food-table tfoot .voucher_value').parent().remove();
                    $('#order-food-table tfoot .grand_total').parent().remove();
                    resetVoucherList();
                } else if ($('.total').text() == 'RM 0.00'){
                    $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Please select at least one food item before applying a voucher!<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#order-form');
                    $('#order-form').addClass('mt-4');
                    $('html, body').animate({ scrollTop: 0 }, 0);
                } else {
                    var html = '';

                    $('#order-food-table tfoot .promo_code_value').parent().remove();
                    $('#order-food-table tfoot .voucher_value').parent().remove();
                    $('#order-food-table tfoot .grand_total').parent().remove();

                    html += '<tr>';
                    html +=     '<td class="d-none"><input type="number" name="voucher_id" id="voucher_id" value="' + voucher_id + '"></td>';
                    html +=     '<td colspan="3" class="fw-bold text-end" style="color: RGB(0, 196, 49);">Voucher</td>';
                    html +=     '<td class="voucher_value">- RM ' + price.toFixed(2) + '</td>';
                    html += '</tr>';

                    var grand_total = parseFloat($('.total').text().substring(3)) - price;
                    if (grand_total < 0)
                        grand_total = 0.0;
                    
                    html += '<tr>';
                    html +=     '<td colspan="3" class="fw-bold text-end">Grand Total</td>';
                    html +=     '<td class="grand_total">RM ' + grand_total.toFixed(2) + '</td>';
                    html += '</tr>';

                    $('#order-food-table tfoot').append(html);
                    
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

            function checkFoodStatus(name, this_element){
                this_element.parent().find('p').text('');
                this_element.removeClass('red-box-shadow is-invalid');

                if ($('#fooddatalistOptions [value="' + name + '"]').length != 0){
                    var data = $('#fooddatalistOptions [value="' + name + '"]').data('value');
                    var data_array = data.split(',');
                    if (data_array[2] == 0){
                        this_element.addClass('red-box-shadow is-invalid');
                        this_element.parent().find('p').text('Selected food item is not available!');
                        this_element.val('');
                    }
                }
            }

            $(document).ready(function() {
                <?php if (isset($order) && $order['voucher_id'] != '0') { ?>
                    $('#nav-voucher-tab').trigger('click');
                <?php } ?>

                $(document).on('change', '.qty', function () {
                    var row = $(this).parentsUntil('tr').parent();
                    var subtotal_price = row.find('.subtotal_data').text().substring(3);
                    var total = $(this).parentsUntil('table').parent().find('tfoot .total');
                    var total_1 = (parseFloat(total.text().substring(3)) - parseFloat(subtotal_price)).toFixed(2);
                    var grand_total = $(this).parentsUntil('table').parent().find('tfoot .grand_total');
                    var grand_total_1 = (parseFloat(grand_total.text().substring(3)) - parseFloat(subtotal_price)).toFixed(2);

                    if ($(this).val() <= 0){
                        if (confirm('Are you sure you want to remove this food item?')){
                            total.text('RM ' + total_1);
                            if ($(this).parentsUntil('tbody').parent().children('tr').length == 1){
                                $(this).parentsUntil('table').parent().find('tfoot').addClass('d-none');
                                $(this).parentsUntil('tbody').parent().append('<tr><td colspan="4" style="height: 42px;" class="empty_td"></td></tr>');
                                if ($('.voucher_value').length != 0){
                                    $('.voucher_value').parent().remove();
                                    $('.grand_total').parent().remove();
                                    resetVoucherList();
                                    alert('The use of voucher has been cancelled as no food item is selected.');
                                }
                            }
                            row.remove();

                            var red_text = false;
                            $('.subtotal_data').each(function() {
                                if ($(this).hasClass('text-danger'))
                                    red_text = true;
                            });

                            if (!red_text){
                                $('.total').removeClass('text-danger');
                            }
                        } else {
                            $(this).val(1);
                            var price_per_item = row.find('.price_data').text().substring(3);
                            row.find('.subtotal_data').text('RM ' + price_per_item);
                            var new_total = (parseFloat(total_1) + parseFloat(price_per_item)).toFixed(2);
                            grand_total_1 = (parseFloat(grand_total_1) + parseFloat(price_per_item)).toFixed(2);
                            total.text('RM ' + new_total);
                        }
                    } else {
                        $(this).val(Math.floor($(this).val()));
                        var price_per_item = row.find('.price_data').text().substring(3);
                        var new_subtotal = (parseFloat(price_per_item) * $(this).val()).toFixed(2);
                        row.find('.subtotal_data').text('RM ' + new_subtotal);
                        var new_total = (parseFloat(total_1) + parseFloat(new_subtotal)).toFixed(2);
                        grand_total_1 = (parseFloat(grand_total_1) + parseFloat(new_subtotal)).toFixed(2);
                        total.text('RM ' + new_total);
                    }

                    //remove promocode if lower than min spend
                    if (parseFloat(total.text().substring(3)) < parseFloat($('.offer-icon.fa-check-circle').parent().find('input').val())){
                        removeOffer();
                        alert('The use of offer has been cancelled as the order total is lower than the minimum spend.');
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

                    //update grand total
                    if (grand_total.length != 0){
                        if (grand_total.text() == 'RM 0.00')
                            grand_total_1 = (parseFloat(total.text().substring(3)) - parseFloat($('.voucher_value').text().substring(5))).toFixed(2);

                        if (parseFloat(grand_total_1) < 0)
                            grand_total_1 = 0.0.toFixed(2);

                        grand_total.text('RM ' + grand_total_1);
                    }
                });

                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                });

                $('#add-food-btn').click(function () {
                    var name = $('#food-item').val();
                    if ($('#fooddatalistOptions [value="' + name + '"]').length != 0){
                        var data = $('#fooddatalistOptions [value="' + name + '"]').data('value');
                        var promotion_json = data.substring(data.indexOf(',[')+1);
                        var data_array = data.split(',');
                        var promotions_infos = $.parseJSON(promotion_json.replace(/'/g, '"'));
                        var buy_free, discount_price, settings_data;

                        <?php if (!isset($_GET['reservation_id']) && (isset($order) && $order['status'] != 'Upcoming')) { ?>
                        if (promotions_infos.length > 0){
                            $.each(promotions_infos, function(key, promotion_info) {
                                if (promotion_info['settings_data']){
                                    settings_data = $.parseJSON(promotion_info['settings_data']);
                                }
                                
                                if (promotion_info['type_code'] == 'BOGO'){
                                    buy_free = 'Buy 1 Get 1 Free';
                                } else if (promotion_info['type_code'] == 'multi_buy'){
                                    buy_free = 'Buy ' + settings_data['amount_1'] + ' Get ' + settings_data['amount_2'] + ' Free';
                                } else if (promotion_info['type_code'] == 'percent_off'){
                                    discount_price = parseFloat(data_array[1]) * (1-(parseInt(settings_data['percentage']) / 100));
                                } else if (promotion_info['type_code'] == 'dollar_dis'){
                                    discount_price = settings_data['discounted_price'];
                                } 
                            });
                        }
                        <?php } ?>

                        var html;

                        html += '<tr class="my-2">';
                        html +=     '<td style="white-space: normal;" class="ps-3">';
                        html +=         name

                    if (buy_free){
                        html +=         '<br><span class="text-danger">(' + buy_free + ')</span>'
                    }

                        html +=     '</td>';
                        html +=     '<td class="price">';
                    
                    if (discount_price){
                        html +=         '<span class="text-decoration-line-through">RM ' + parseFloat(data_array[1]).toFixed(2) + '</span>';
                        html +=         '<br>';
                        html +=         '<span class="text-danger price_data">RM ' + parseFloat(discount_price).toFixed(2) + '</span>';
                    } else {
                        html +=         '<span class="price_data">RM ' + parseFloat(data_array[1]).toFixed(2) + '</span>';
                    }

                        html +=     '</td>';
                        html +=     '<td>';
                        html +=         '<input type="text" class="d-none item_id" value="' + data_array[0] + '">';
                        html +=         '&nbsp;&nbsp;<input type="number" min="0" name="food_qty[' + data_array[0] + ']" class="form-control qty d-inline-block ps-2 pe-1" style="width: 45px; border-radius: 0px; border: 0px !important; background-color: #dee2e6;" value="1">';
                        html +=     '</td>';
                        html +=     '<td class="subtotal">';

                    if (discount_price){
                        html +=         '<span class="text-danger subtotal_data">RM ' + discount_price + '</span>';
                    } else {
                        html +=         '<span class="subtotal_data">RM ' + parseFloat(data_array[1]).toFixed(2) + '</span>';
                    }
                        
                        html +=     '</td>';
                        html += '</tr>';

                        if ($('#order-food-table tbody').find('.empty_td').length == 1)
                            $('#order-food-table tbody tr').remove();
                        
                        var repeat = false;
                        $('.item_id').each(function() {
                            if ($(this).val() == data_array[0]){
                                repeat = true;
                            }
                        });

                        if (!repeat){
                            $('#order-food-table tbody').append(html);
                            $('#order-food-table tfoot').removeClass('d-none');
                            var total_element = $('#order-food-table tfoot .total');
                            var grand_total_element = $('#order-food-table tfoot .grand_total');
                            var total = total_element.text().substring(3);
                            var grand_total = grand_total_element.text().substring(3);
                            if (discount_price){
                                total_element.text('RM ' + (parseFloat(total) + parseFloat(discount_price)).toFixed(2));
                                if (grand_total_element.text() == 'RM 0.00'){
                                    var grand_total_value = parseFloat(total_element.text().substring(3)) - parseFloat($('.voucher_value').text().substring(5));

                                    if (grand_total_value < 0)
                                        grand_total_value = 0.0;

                                    grand_total_element.text('RM ' + grand_total_value.toFixed(2));
                                } else
                                    grand_total_element.text('RM ' + (parseFloat(grand_total) + parseFloat(discount_price)).toFixed(2));
                                total_element.addClass('text-danger');
                            } else {
                                total_element.text('RM ' + (parseFloat(total) + parseFloat(data_array[1])).toFixed(2));
                                if (grand_total_element.text() == 'RM 0.00'){
                                    var grand_total_value = parseFloat(total_element.text().substring(3)) - parseFloat($('.voucher_value').text().substring(5));

                                    if (grand_total_value < 0)
                                        grand_total_value = 0.0;

                                    grand_total_element.text('RM ' + grand_total_value.toFixed(2));
                                } else
                                    grand_total_element.text('RM ' + (parseFloat(grand_total) + parseFloat(data_array[1])).toFixed(2));
                            }
                        }
                    }
                    
                    $('#food-item').val('');

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
                });

                $('#search-offers').on('input', function() {
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

                $("#order-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('.text-danger').not('.total, .subtotal_data').text('');

                    $.ajax({
                        url: '../../helpers/order.php',
                        data: $('#order-form select, #order-form input, #order-form textarea'),
                        method: 'post',
                        success: function(output) {
                            var json = $.parseJSON(output);

                            if(json['warning']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + json['warning'] + '<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#order-form');
                                $('#order-form').addClass('mt-4');
                            } else if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#order-form');
                                $('#order-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('#spinner').removeClass('d-none');
                                $('#spinner').parent().prop('disabled', true);
                                $('input[name="submit_check_preorder"]').attr('name', "submit_form_preorder");
                                if ($('.grand_total').length != 0){
                                    $('<td class="d-none"><input type="number" name="grand_total" value="' + parseFloat($('.grand_total').text().substring(3)) + '"></td>').insertBefore('.grand_total');
                                } else {
                                    $('<td class="d-none"><input type="number" name="total" value="' + parseFloat($('.total').text().substring(3)) + '"></td>').insertBefore('.total');
                                }
                                $('#order-form').unbind().submit();
                            }
                        }
                    });
                });

                $('textarea').each(function() {
                    var limit = 1000;
                    var current_char = $(this).val().length;
                    $(this).parent().find('.char-counter').text((limit - current_char).toString() + ' characters');                
                });

                $('textarea').on('input', function(){
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

                var red_text = false;
                $('.subtotal_data').each(function() {
                    if ($(this).hasClass('text-danger'))
                        red_text = true;
                });

                if (red_text){
                    $('.total').addClass('text-danger');
                }
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