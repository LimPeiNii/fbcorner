<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/cart.php';
    include_once '../../helpers/voucher.php';
    include_once '../../../../admin/php/helpers/promotion.php';
    include_once '../../../../admin/php/helpers/food_menu.php';
    include_once '../../../../admin/php/helpers/table.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/reservation.php';


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

      $cart = getUnplacedCart($connection, $_SESSION['login_cus_id'], $_GET['rest_id']);
      $rest = getRestaurant($connection, $_GET['rest_id']);
      $rest_name = $rest['rest_name'];
      $tables = getTables($connection, $_GET['rest_id']);
      $vouchers = getVouchers($connection, $_SESSION['login_cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_in_use' => true]);
      $promo_codes = getPromotions($connection, $rest['rest_id'], ['type_code' => 'promo_code', 'status' => '1']);

      date_default_timezone_set("Asia/Kuala_Lumpur");

      //check if the customer has ongoing reservation
      $ongoing_reservation = getReservations($connection, $_GET['rest_id'], ['cus_id' => $_SESSION['login_cus_id'], 'status' => 1, 'remarks' => 'Ongoing']);
      
      if ($ongoing_reservation){
        $the_ongoing_reservation = $ongoing_reservation[0];
      }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'; ?>

    <style>
        body{
            height: 100%;
        }
        
        #voucher{
            border: 1px solid black; 
            max-height: 250px;
            overflow-y: auto;
            background-color: white;
        }

        @media (max-width: 1176.18px){
            #table_select, #pickup_time_input{
                text-align: left !important;
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
    </style>

</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
        <div class="tab-content">
            <div>
                <div>
                <h3 style="cursor: default; line-height: 45px;" class="m-5 text-center font-century font-size-35">
                    Please Confirm Your Order
                </h3>
                </div>
                <div class="m-4 mb-0">
                    <div class="m-4 mb-2">
                        <div style="height: 50px; line-height: 50px;" class="font-size-18 fw-bold bg-danger bg-opacity-25 ps-4">
                            <span><?=$rest_name?></span>
                        </div>
                        <div style="min-height: 50px; line-height: 50px;" class="font-size-18 fw-bold row m-0">
                            <span class="col p-0 ps-2">
                                <div class="d-inline-block">
                                    <input class="form-check-input" value="dine_in" style="margin-top: 15px;" checked="checked" type="radio" name="dine-in-pickup" id="dine_in">
                                    <label class="form-check-label" for="dine_in">
                                        Dine-In
                                    </label>
                                </div>
                                <div class="d-inline-block">
                                    <input class="form-check-input ms-2" value="pickup" style="margin-top: 15px;" type="radio" name="dine-in-pickup" id="pickup">
                                    <label class="form-check-label" for="pickup">
                                        Self Pickup
                                    </label>
                                </div>
                            </span>
                            <span class="col p-0 text-end ps-2" id="table_select">
                                Table Number (Name)&nbsp; 
                                <select class="form-select d-inline-block mt-1 me-2" style="width: 250px;" id="table_name_num">
                                    <option value="">-- Please Select --</option>
                                    <?php foreach ($tables as $table) { ?>
                                        <?php if ($table['status'] == '1') { ?>
                                            <option value="<?=$table['table_id']?>"><?=$table['table_num_name']?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </span>
                            <span class="col p-0 text-end ps-2 d-none" id="pickup_time_input">
                                Pickup Time&nbsp; 
                                <input type="time" class="form-control d-inline-block mt-1 me-2" style="width: 250px;" id="pickup_time">
                            </span>
                        </div>
                    </div>
                    <div class="m-4 my-2 table-responsive">
                        <table class="w-100 table mt-2" style="white-space: nowrap; border: solid 2px black;" id="order-food-table">
                            <thead>
                                <tr style="height: 50px;">
                                    <th class="ps-4" colspan="2">Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody style="border: solid 2px black;" class="align-middle">
                                <?php $items = json_decode($cart['item_quantity'], true); 
                                    $total = 0.0;
                                    foreach ($items as $key => $value) {
                                        $item_info = getFoodMenuItem($connection, $key);
                                        $promotion_infos = getPromotions($connection, $_GET['rest_id'], ['food_item_id' => $item_info['item_id'], 'status' => '1']);

                                        $buy_free = '';
                                        $buy_free_value = 0;
                                        $discount_price = '';
                                        if (!empty($promotion_infos)){
                                            foreach ($promotion_infos as $promotion_info){
                                                if (isset($promotion_info['settings_data'])){
                                                    $settings_data = json_decode($promotion_info['settings_data'], true);
                                                }
                                                if ($promotion_info['type_code'] == 'BOGO'){
                                                    $buy_free = 'Buy 1 Get 1 Free';
                                                    $buy_free_value = $value;
                                                } elseif ($promotion_info['type_code'] == 'multi_buy'){
                                                    $buy_free = 'Buy ' . $settings_data['amount_1'] . ' Get ' . $settings_data['amount_2'] . ' Free';
                                                    $buy_free_value = floor($value / (int)$settings_data['amount_1']) * (int)$settings_data['amount_2'];
                                                } elseif ($promotion_info['type_code'] == 'percent_off'){
                                                    $discount_price = $item_info['price'] * (1-((int)$settings_data['percentage'] / 100));
                                                } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                                                    $discount_price = $settings_data['discounted_price'];
                                                } 
                                            }
                                        }

                                ?>
                                <tr class="my-2" style="border-color: #dee2e6;">
                                    <td class="ps-3 pe-2 py-2 text-center" style="width: 90px;">
                                        <img class="m-0" height="80" width="80" style="max-height: 80px; max-width: 80px;" src="../../../../admin/uploads/food_menu_photo/<?=$item_info['image']?>">
                                    </td>
                                    <td style="white-space: normal;">
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
                                        &nbsp;<?=$value?>
                                        <?php if (isset($buy_free_value) && $buy_free_value) { ?>
                                            <span class=text-danger>+ <?=(string)$buy_free_value?> Free</span>
                                        <?php } ?>
                                    </td>
                                    <td class="subtotal">
                                        <?php if (isset($discount_price) && !empty($discount_price)) { ?>
                                            <span class="text-danger subtotal_data">RM <?=number_format(round($discount_price * $value, 2), 2, '.', '')?></span>
                                        <?php } else { ?>
                                            <span class="subtotal_data">RM <?=number_format($item_info['price'] * $value, 2, '.', '')?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php 
                                    if (isset($discount_price) && !empty($discount_price)) {
                                        $total += number_format(round($discount_price * $value, 2), 2, '.', '');
                                    } else {
                                        $total += number_format($item_info['price'] * $value, 2, '.', '');
                                    } 
                                ?>
                                <?php } ?>
                                <tr style="border-color: #dee2e6;">
                                    <td colspan="4" class="text-end fw-bold" style="border:0px;">Total</td>
                                    <td class="total" style="border:0px;">RM <?=number_format($total, 2, '.', '')?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="m-4 mt-0">
                    <div class="m-4 mt-0 px-3" style="border: solid 2.5px black;" >
                        <div class="m-2 py-2">
                            <label class="form-label"><h5 class="fw-bold pt-1">Special Request</h5></label>
                            <textarea id="additional-request" rows="6" placeholder="special request" class="d-block mt-2 mb-4 width-100p color-6 form-control" style="border-radius: 0px;"></textarea>
                        </div>
                    </div>
                    <div class="form-switch form-check m-4 mt-0 px-3" style="border: solid 2.5px black;" >
                        <div class="m-2 pt-2 d-flex">
                            <label class="form-check-label flex-grow-1" for="voucher-switch"><h5 class="fw-bold d-inline-block pt-1">Offers and Vouchers</h5>&nbsp;&nbsp;<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<p class='text-start'>-&nbsp;&nbsp;All vouchers can be used in any restaurant<br>-&nbsp;&nbsp;Only one voucher or one promo code can be used at a time<br></p>"><i class="fas fa-question-circle"></i></span></label>
                            <input class="form-check-input m-1" style="height: 20px; width: 40px; margin-top: 0.35rem !important;" id="voucher-switch" type="checkbox" role="switch">
                        </div>
                        <div id="offer-voucher" class="d-none mb-3">
                        <nav>
                        <div class="nav nav-tabs flex-column flex-sm-row" id="nav-tab-offer-voucher" role="tablist">
                            <button class="nav-link active flex-sm-fill text-sm-center " id="nav-offer-tab" data-bs-toggle="tab" data-bs-target="#nav-offer" type="button" role="tab" aria-controls="nav-offer" aria-selected="true">Offers</button>
                            <button class="nav-link flex-sm-fill text-sm-center " id="nav-voucher-tab" data-bs-toggle="tab" data-bs-target="#nav-voucher" type="button" role="tab" aria-controls="nav-voucher" aria-selected="false">Vouchers</button>
                        </div>
                        </nav>
                        <div class="tab-content bg-light" id="nav-tabContent">
                            <div class="tab-pane fade show active p-3" id="nav-offer" role="tabpanel" aria-labelledby="nav-offer-tab">
                                <nav class="navbar navbar-light">
                                    <div class="container-fluid">
                                        <div class="d-flex w-100">
                                        <input class="form-control" id="search-offers" name="" type="search" placeholder="Search" aria-label="Search">
                                        </div>
                                    </div>
                                </nav>
                                <?php if ($promo_codes) { ?>
                                    <div class="list-group" style="max-height: 269.4px; min-height: 269.4px; margin: 0.8rem; <?=count($promo_codes) <= 3 ? 'overflow-y: hidden; ' : 'overflow: auto; '?> border: 1px solid black; background-color: white;">
                                    <?php foreach ($promo_codes as $promo_key => $promo_code) { ?>
                                        <?php $details = json_decode($promo_code['settings_data'], true); ?>
                                        <div style="<?=$promo_key == count($promo_codes)-1 && count($promo_codes) >= 3 ? 'border-bottom: none; ' : 'border-bottom: 1px solid grey; border-bottom-left-radius: unset; border-bottom-right-radius: unset; '?> cursor: pointer;" class="list-group-item list-group-item-action" data-bs-target="#offerModal" data-bs-toggle="modal"  onclick="showOfferDescription('<?=$promo_code['promotion_id']?>', $(this));" aria-current="true">
                                            <div class="d-flex flex-wrap w-100 p-2">
                                                <input type="number" class="d-none" value="<?=(float)$details['min_price']?>">
                                                <?php if ($total < (float)$details['min_price']) { ?>
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
                            <div class="tab-pane fade p-3" id="nav-voucher" role="tabpanel" aria-labelledby="nav-voucher-tab">
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
                        </div>
                    </div>
                    <div>
                        <div class="my-5 mx-3" style="height: 44px;">
                            <button type="button" class="btn btn-secondary float-end mx-3 font-size-20 cancel-btn">Cancel</button>
                            <button type="button" class="btn btn-primary float-end font-size-20 confirm-btn"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span>Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

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

    <?php include_once '../../common/footer.php'; ?>
    </div>
    
    <?php include_once '../../../../script.php'; ?>

    <?php include_once '../../../script.php'; ?>

    <script>

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
                    $("nav, main, footer").addClass('blur');
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
                        $('.close-details-modal').trigger('click');

                        var html = '';

                        $('#order-food-table tbody .promo_code_value').parent().remove();
                        $('#order-food-table tbody .voucher_value').parent().remove();
                        $('#order-food-table tbody .grand_total').parent().remove();

                        html += '<tr style="border-color: #dee2e6;">';
                        html +=     '<td colspan="4" class="fw-bold text-end promo_code_name" style="color: RGB(0, 196, 49); border:0px;">Promo Code (' + json['success']['name'] + ')</td>';
                        html +=     '<td class="promo_code_id d-none"><input type="number" name="promo_code_id" value="' + promo_id + '"></td>';
                        html +=     '<td class="promo_code_value" style="border:0px;">- RM ' + json['success']['value'].toFixed(2) + '</td>';
                        html += '</tr>';

                        html += '<tr style="border-color: #dee2e6;">';
                        html +=     '<td colspan="4" class="fw-bold text-end">Grand Total</td>';
                        html +=     '<td class="grand_total">RM ' + (parseFloat($('.total').text().substring(3)) - json['success']['value']).toFixed(2) + '</td>';
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
            $('#order-food-table tbody .promo_code_value').parent().remove();
            $('#order-food-table tbody .grand_total').parent().remove();

            $('#offerModal').modal('hide');
            $('.close-details-modal').trigger('click');
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

                $('#order-food-table tbody .promo_code_value').parent().remove();
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

        $(document).ready(function() {
            <?php if (isset($the_ongoing_reservation)) { ?>
                $('#dine_in, #pickup, #table_name_num').prop('disabled', true);
                $('#table_name_num').val('<?=$the_ongoing_reservation['table_id']?>');
            <?php } ?>
            
            $('.close-details-modal').click(function() {
                $("nav, main, footer").removeClass('blur');
            });

            $('#voucher-switch').change(function() {
                $('#offer-voucher').toggleClass('d-none');
                if (!$(this).prop('checked'))
                    removeOffer();
            });

            $('#dine_in').change(function() {
                $('#table_select').toggleClass('d-none');
                $('#pickup_time_input').toggleClass('d-none');
                $('#table_select select').prop('disabled', false);
                $('#pickup_time_input input').prop('disabled', true);
            });

            $('#pickup').change(function() {
                $('#table_select').toggleClass('d-none');
                $('#pickup_time_input').toggleClass('d-none');
                $('#table_select select').prop('disabled', true);
                $('#pickup_time_input input').prop('disabled', false);
            })

            $('.cancel-btn').click(function() {
                location = 'my_cart.php';
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

            $('.confirm-btn').click(function() {
                if ($('#table_name_num').val() == '' && $('input[name=\'dine-in-pickup\']:checked').val() == 'dine_in'){
                    slideInMsg('error', 'Please select the table number (name) !');
                } else if ($('#pickup_time').val() == '' && $('input[name=\'dine-in-pickup\']:checked').val() == 'pickup'){
                    slideInMsg('error', 'Please select the pickup time !');
                } else {
                    var error = false;

                    if ($('#pickup_time').val() != '' && $('input[name=\'dine-in-pickup\']:checked').val() == 'pickup'){
                        var current_time = new Date(new Date().setSeconds(0, 0));
                        var pickup_time = new Date('<?=date('Y-m-d')?>' + ' ' + $('#pickup_time').val());
                        if (pickup_time < current_time){
                            slideInMsg('error', 'Selected time must not be in the past!');
                            error = true;
                        } else {
                            var mins_diff = (Math.abs(pickup_time - current_time) / 60000);
                            if (mins_diff < 30){
                                slideInMsg('error', 'Please choose a time at least 30 minutes from now for the restaurant to prepare your food.');
                                error = true;
                            }
                        }

                        //check how many people at that time
                        if (!error){
                            $.ajax({
                                url: '../../helpers/cart.php',
                                data: {
                                    rest_id: "<?=$_GET['rest_id']?>",
                                    selected_time: '<?=date('Y-m-d')?>' + ' ' + $('#pickup_time').val(),
                                    check_pickup_time: true
                                },
                                method: 'post',
                                async: false,
                                success: function(output){
                                    var json = $.parseJSON(output);

                                    if (json['error']){
                                        slideInMsg('error', json['error']);
                                        error = true;
                                    }
                                }
                            });
                        }
                    }

                    if (!error && $('input[name=\'dine-in-pickup\']:checked').val() == 'dine_in'){
                        var pass_code = window.prompt("Please enter the passcode before placing an order");
                        if (pass_code == null)
                            return false;
                        else if (pass_code != null && pass_code != '1234'){
                            slideInMsg('error', 'Wrong passcode! Please try again.');
                            error = true;
                        }
                    }

                    if (!error){
                        var data = {
                            rest_id: "<?=$_GET['rest_id']?>",
                            table_id: $('#table_name_num').val(),
                            pickup_time: $('#pickup_time').val(),
                            additional_notes: $('#additional-request').val(),
                            checkout_cart: true
                        };

                        <?php if (isset($the_ongoing_reservation)) { ?>
                            data['reservation_id'] = '<?=$the_ongoing_reservation['reservation_id']?>';
                        <?php } ?>

                        if ($('input[name=promo_code_id]').length != 0)
                            data['promo_code_id'] = $('input[name=promo_code_id]').val();

                        if ($('input[name=voucher_id]').length != 0)
                            data['voucher_id'] = $('input[name=voucher_id]').val();

                        $.ajax({
                            url: '../../helpers/cart.php',
                            data: data,
                            method: 'post',
                            beforeSend: function(){
                                $('#spinner').removeClass('d-none');
                                $('#spinner').parent().prop('disabled', true);
                            },
                            success: function(){
                                location = 'my_cart.php';
                            }
                        });
                    }
                }
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

  </body>
</html>


<?php

    } else {
        header("Location: ../homepage/index.php");
        exit;
    }

?>