<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../../../admin/php/helpers/food_menu.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/inventory.php';
    include_once '../../../../admin/php/helpers/promotion.php';

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

    $food_item = getFoodMenuItem($connection, $_GET['food_item_id']);

    if (!isset($_GET['visit_rest_id'])){
      $_GET['visit_rest_id'] = $food_item['rest_id'];
    }

    $visit_restaurant = getRestaurant($connection, $_GET['visit_rest_id']);
    $promotion_infos = getPromotions($connection, $_GET['visit_rest_id'], ['food_item_id' => $food_item['item_id'], 'status' => '1']);

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
          $discount_price = $food_item['price'] * (1-((int)$settings_data['percentage'] / 100));
        } elseif ($promotion_info['type_code'] == 'dollar_dis'){
          $discount_price = $settings_data['discounted_price'];
        } 
      }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$food_item['item_name']?> &VerticalLine; <?=$visit_restaurant['rest_name']?></title>

    <?php include_once '../../../../link.php'?>

    <style>
      body{
        height: 100%;
      }

      @media (max-width: 777px) {
          #image-container{
              padding-left: 0px !important;
              padding-right: 0px !important;
          }
      }
    </style>

</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>

    <?php include_once '../../common/signin_signup.php'; ?>
    
    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
      <section class="m-4">
        <div class="mb-0">
            <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 d-inline-block font-bernard font-size-35">
                <?=$visit_restaurant['rest_name']?>
            </h3>
        </div>
            <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 my-5 mx-3 pt-3 pb-5">
                <div class="col p-4 mt-0" id="image-container">
                    <img class="box-style" height="500" style="width: 100%;" src="../../../../admin/uploads/food_menu_photo/<?=$food_item['image']?>">
                </div>
                <div class="col box-style p-4 mt-4 color-2 overflow-auto" style="max-height: 500px;">
                    <div class="d-flex flex-wrap">
                      <div class="d-inline-block flex-grow-1">
                      <h2 class="m-4 d-inline-block <?=(isset($buy_free) ? 'mb-0' : 'mb-2')?>"><?=$food_item['item_name']?></h2>
                      <?php if (isset($buy_free)) { ?>
                          <br><span class="text-danger d-inline-block ms-4"><small>(<?=$buy_free?>)</small></span>
                      <?php } ?>
                      </div>
                      <div class="font-size-18 mx-4 align-self-center d-inline-block" <?=(isset($discount_price) ? 'style="margin-top: 2rem !important;"' : 'style="line-height: 40px;"')?>>
                          <span class="<?=(isset($discount_price) ? 'text-decoration-line-through d-inline-block' : '')?>">RM <?=number_format($food_item['price'], 2, '.', '')?></span>
                          <?php if (isset($discount_price)) { ?>
                              <br><span class="text-danger d-inline-block" style="height: fit-content;"><small>RM <?=number_format($discount_price, 2, '.', '')?></small></span>
                          <?php } ?>
                      </div>
                    </div>
                    <div class="d-flex flex-wrap">
                      <div class="d-inline-block m-4 mb-3">
                        <button type="button" class="btn btn-dark rounded-0" id="button-minus"><i class="fas fa-minus"></i></button>
                        <input type="number" min="1" class="form-control d-inline-block px-1 mx-3" style="width: 40px; border: 0px !important; background-color: transparent;" value="1" id="qty">
                        <button type="button" class="btn btn-dark rounded-0" id="button-add"><i class="fas fa-plus"></i></button>
                      </div>
                      <div class="d-inline-block mx-4 align-self-center mt-2">
                        <button type="button" style="width: 145px;" class="btn btn-dark rounded-0" id="add-to-cart">Add to Cart</button>
                      </div>
                    </div>
                    <div class="font-size-18 my-4 mx-4">
                      <strong>Description</strong><br>
                      <?=$food_item['description']?>
                    </div>
                    <?php if (!empty($food_item['ingredients'])) { ?>
                      <?php $ingredients = json_decode($food_item['ingredients'], true)?>
                      <div class="font-size-18 mx-4">
                      <strong>Ingredients</strong><br>
                        <ol class="mb-4">
                          <?php foreach ($ingredients as $key => $value) { ?>
                            <li class="pt-1">&nbsp;&nbsp;<?=getInventory($connection, $key)['item_name']?></li>
                          <?php } ?>
                        </ol>
                      </div>
                    <?php } ?>
                </div>
            </div>
      </section>

    </main>

    <?php include_once '../../common/notification.php'; ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>

    <script>

      $(document).ready(function() {
        $('#button-minus').click(function () {
          var value = parseInt($('#qty').val()) - 1;
          if (value <= 0)
            value = 1;
          $('#qty').val(value);
        });

        $('#button-add').click(function () {
          $('#qty').val(parseInt($('#qty').val()) + 1);
        });

        $('#add-to-cart').click(function () {
          <?php if (isset($_SESSION['login_cus_id'])) { ?>
            $.ajax({
              url: '../../helpers/order.php',
              data: {
                quantity: $('#qty').val(),
                rest_id: '<?=$_GET['visit_rest_id']?>',
                food_item_id: '<?=$food_item['item_id']?>',
                cus_id: '<?=$_SESSION['login_cus_id']?>',
                add_to_cart: true
              },
              method: 'post',
              success: function(output){
                var json = $.parseJSON(output);

                if (json['success'])
                  slideInMsg('success', json['success'] + ' <a href="../cart/my_cart.php">View Cart</a>');
                else
                  slideInMsg('error', json['error']);
              }
            });
          <?php } else { ?>
            $.ajax({
              url: '../warning/login_first.php',
              data: {
                doc_title: "<title><?=$food_item['item_name']?> &VerticalLine; <?=$visit_restaurant['rest_name']?></title>",
                redirect: "../order/<?=basename($_SERVER['REQUEST_URI'])?>"
              },
              method: 'post',
              success: function(){
                window.location.href = '../warning/login_first.php';
              }
            });
          <?php } ?>
        });
      });
    </script>

  </body>
</html>