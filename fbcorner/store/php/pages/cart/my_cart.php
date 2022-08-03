<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/cart.php';
    include_once '../../../../admin/php/helpers/promotion.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/food_menu.php';

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

      $carts = getCarts($connection, $_SESSION['login_cus_id'], ['placed' => '0']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
    
    <style>
        body{
            height: 100%;
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
                    <h1 style="cursor: default; font-size: 3rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Cart</h1>
                    <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
                </div>
            </div>
            <div id="cart-wrapper">
            <?php foreach ($carts as $cart) { ?>
            <?php 
                $rest = getRestaurant($connection, $cart['rest_id']); 
                $rest_name = $rest['rest_name'];
            ?>
            <div style="width: 95%;" class="m-auto cart-subwrapper">
                <div style="height: 50px; line-height: 50px;" class="font-size-18 fw-bold bg-danger bg-opacity-25 mt-5 ps-4"><a href="../restaurant/profile.php?visit_rest_id=<?=$cart['rest_id']?>"><?=$rest_name?></a></div>
                <div class="d-none rest_id"><?=$rest['rest_id']?></div>
                <div class="mt-0 table-responsive">
                    <table class="w-100 table mt-2" style="white-space: nowrap;">
                        <thead>
                            <tr style="height: 50px; border: solid 2px black;">
                                <th class="ps-4" colspan="2" style="border-bottom: 0px;">Item</th>
                                <th style="border-bottom: 0px;">Price</th>
                                <th style="border-bottom: 0px;">Quantity</th>
                                <th style="border-bottom: 0px;">Subtotal</th>
                                <th style="border-bottom: 0px;"></th>
                            </tr>
                        </thead>
                        <tbody style="border: solid 2px black; border-top: 0px; border-bottom: 0px;" class="align-middle">
                            <?php $items = json_decode($cart['item_quantity'], true); 
                                $total = 0.0;
                                foreach ($items as $key => $value) {
                                    $item_info = getFoodMenuItem($connection, $key);
                                    $promotion_infos = getPromotions($connection, $cart['rest_id'], ['food_item_id' => $item_info['item_id'], 'status' => '1']);

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
                                    <div class="d-none item_id"><?=$key?></div>
                                    <input type="number" min="1" class="form-control qty d-inline-block ps-2 pe-1" style="width: 45px; border-radius: 0px; border: 0px !important; background-color: #dee2e6" value="<?=$value?>">
                                </td>
                                <td class="subtotal">
                                    <?php if (isset($discount_price) && !empty($discount_price)) { ?>
                                        <span class="text-danger subtotal_data">RM <?=number_format(round($discount_price * $value, 2), 2, '.', '')?></span>
                                    <?php } else { ?>
                                        <span class="subtotal_data">RM <?=number_format($item_info['price'] * $value, 2, '.', '')?></span>
                                    <?php } ?>
                                </td>
                                <td width="10%" style="min-width: 65px;" class="text-center"><button type="button" class="btn bg-danger text-white" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove" onclick="removeFoodItem($(this), &quot;<?=$cart['rest_id']?>&quot;,&quot;<?=$key?>&quot;); "><i class="fas fa-minus-circle font-size-20" style="line-height: 30px;"></i></button></td>
                            </tr>
                            <?php 
                                if (isset($discount_price) && !empty($discount_price)) {
                                    $total += number_format(round($discount_price * $value, 2), 2, '.', '');
                                } else {
                                    $total += number_format($item_info['price'] * $value, 2, '.', '');
                                } 
                            ?>
                            <?php } ?>
                        </tbody>
                        <tfoot style="border-top: 0px;">
                            <tr style="border: solid 2px black; border-top: 0px; ">
                                <td colspan="4" class="text-end fw-bold">Total</td>
                                <td class="total">RM <?=number_format($total, 2, '.', '')?></td>
                                <td width="10%" style="min-width: 65px;" class="text-center">
                                    <a href="checkout.php?rest_id=<?=$cart['rest_id']?>"><button type="button" class="btn bg-primary text-white bg-opacity-75 checkout-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Checkout" style="width: 46px; padding-left: 9px;"><i class="bi bi-cart-check-fill" style="line-height: 30px; font-size: 26px"></i></button></a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php } ?>
            <?php if (empty($carts)) { ?>
                <div class="w-100 text-center my-5 alert alert-info">
                    <i class="bi bi-cart-fill" style="font-size: 50px;"></i>&nbsp;&nbsp;
                    <span style="line-height: 50px; font-size: 30px;">
                        Your cart is empty.
                    </span>
                </div>
            <?php } ?>
            </div>
        </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <script>
      function removeFoodItem(this_element, rest_id, food_item_id){
        $.ajax({
            url: '../../helpers/cart.php',
            data: {
                rest_id: rest_id,
                food_item_id: food_item_id,
                update_cart: true
            },
            method: 'post',
            success: function(){
                var row = this_element.parentsUntil('tr').parent();
                var subtotal_price = row.find('.subtotal_data').text().substring(3);
                var total = this_element.parentsUntil('table').parent().find('tfoot .total');
                var new_total = (parseFloat(total.text().substring(3)) - parseFloat(subtotal_price)).toFixed(2);
                total.text('RM ' + new_total);

                if (this_element.parentsUntil('tbody').parent().children('tr').length == 1){
                    this_element.parentsUntil('.cart-subwrapper').parent().remove();

                    if ($('#cart-wrapper').children().length == 0){
                        html = '<div class="w-100 text-center my-5 alert alert-info">';
                        html += '<i class="bi bi-cart-fill" style="font-size: 50px;"></i>';
                        html += '<span style="line-height: 50px; font-size: 30px;">Your cart is empty.</span></div>';

                        $('#cart-wrapper').append(html);
                    }
                } else {
                    this_element.parentsUntil('tr').parent().remove();
                    
                    var red_text = false;
                    $('.subtotal_data').each(function() {
                        if ($(this).hasClass('text-danger'))
                            red_text = true;
                    });

                    if (!red_text){
                        $('.total').removeClass('text-danger');
                    }
                }
            }
        });
      }

      function updateCart(this_element, rest_id) {
        var tbody = this_element.parentsUntil('table').parent().find('tbody');
        var item_qty = {};
        tbody.find('tr').each(function(){
            item_qty[$(this).find('.item_id').text()] = $(this).find('.qty').val();
        });

        $.ajax({
            url: '../../helpers/order.php',
            data: {
                item_quantity: JSON.stringify(item_qty),
                rest_id: rest_id,
                checkout_cart: true
            },
            method: 'post'
        });
      }

      $(document).ready(function() {
          $('.qty').change(function () {
            var row = $(this).parentsUntil('tr').parent();
            var subtotal_price = row.find('.subtotal_data').text().substring(3);
            var total = $(this).parentsUntil('table').parent().find('tfoot .total');
            var total_1 = (parseFloat(total.text().substring(3)) - parseFloat(subtotal_price)).toFixed(2);

            if ($(this).val() <= 0){
                $(this).val(1);
                row.find('.subtotal_data').text(row.find('.price_data').text());
            } else {
                $(this).val(Math.floor($(this).val()));
                var price_per_item = row.find('.price_data').text().substring(3);
                var new_subtotal = (parseFloat(price_per_item) * $(this).val()).toFixed(2);
                row.find('.subtotal_data').text('RM ' + new_subtotal);
                var new_total = (parseFloat(total_1) + parseFloat(new_subtotal)).toFixed(2);
                total.text('RM ' + new_total);
            }

            updateCart($(this), $(this).parentsUntil('.cart-subwrapper').parent().find('.rest_id').text());
          });

          $('.checkout-btn').click(function(e) {
            var this_element = $(this);
            e.preventDefault();
            
            updateCart($(this), $(this).parentsUntil('.cart-subwrapper').parent().find('.rest_id').text());

            //check opening time and availability
            $.ajax({
                url: '../../helpers/order.php',
                data: {
                    rest_id: $(this).parentsUntil('.cart-subwrapper').parent().find('.rest_id').text(),
                    check_out_checking: true
                },
                method: 'post',
                success: function(output){
                    var json = $.parseJSON(output);

                    if (json['error']){
                        slideInMsg('error', json['error']);
                    } else {
                        this_element.unbind().click();
                    }
                }
            });
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