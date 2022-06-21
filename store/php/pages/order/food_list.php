<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../../../admin/php/helpers/food_menu.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/category.php';
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

    $visit_restaurant = getRestaurant($connection, $_GET['visit_rest_id']);
    $food_menu_items_results = getFoodMenuItems($connection, $_GET['visit_rest_id'], ['order_by' => 'item_code', 'asc_desc' => 'ASC']);
    $food_menu_items = [];
    foreach ($food_menu_items_results as $result){
        $food_menu_items[$result['category_id']][] = $result;
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu &VerticalLine; <?=$visit_restaurant['rest_name']?></title>

    <?php include_once '../../../../link.php'?>

    <style>
        body{
            height: 100%;
        }
        
        .col.enabled:hover{
            transform: scale(1.1);
        }

        .col{
            transition: transform 0.5s ease;
        }
    </style>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>
    
    <?php include_once '../../common/signin_signup.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
      <section class="mx-4">
        <div class="m-4 mb-0">
            <h3 style="cursor: default; line-height: 45px;" class="m-0 d-inline-block font-bernard font-size-35">
                <?=$visit_restaurant['rest_name']?>
            </h3>
        </div>
        <div class="px-0 mb-5 mx-4 mt-2">
            <?php foreach ($food_menu_items as $key => $categories) { ?>
                <?php $category_name = getCategory($connection, $key)['category_name']; ?>
                <div class="pt-4">
                    <span style="font-size: 1.5rem;" class="ps-2"><?=ucwords($category_name)?></span>
                    <hr style="border-width: 3px;" class="my-2">
                </div>
                
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 mt-0 px-4 pb-5">
                    <?php foreach ($categories as $food_menu_item) { ?>
                        <?php
                          $promotion_infos = getPromotions($connection, $_GET['visit_rest_id'], ['food_item_id' => $food_menu_item['item_id'], 'status' => '1']);

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
                                    $discount_price = $food_menu_item['price'] * (1-((int)$settings_data['percentage'] / 100));
                                } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                                    $discount_price = $settings_data['discounted_price'];
                                } 
                            }
                          }
                        ?>
                        <?php if ($food_menu_item['status'] == '1') { ?>
                        <a href="food_profile.php?food_item_id=<?=$food_menu_item['item_id']?>" target="_blank">
                        <div class="col enabled my-2" id="food-<?=$food_menu_item['item_id']?>">
                        <?php } else { ?>
                        <div class="col disabled my-2" style="opacity: 0.5;" id="food-<?=$food_menu_item['item_id']?>">
                        <?php } ?>
                            <div class="card h-100 shadow">
                                <img height="250" src="../../../../admin/uploads/food_menu_photo/<?=$food_menu_item['image']?>" class="card-img-top">
                                <div class="card-body">
                                    <h5 class="card-title <?=(isset($buy_free) && !empty($buy_free) ? 'mb-0' : '')?>"><?=$food_menu_item['item_name']?></h5>
                                    <?php if ($food_menu_item['status'] == '1') { ?>
                                    <p class="card-text">
                                      <?php if (isset($buy_free) && !empty($buy_free)) { ?>
                                          <span class="text-danger"><small>(<?=$buy_free?>)</small></span>
                                      <?php } ?>
                                    </p>
                                    <?php } else { ?>
                                    <p class="card-text"><small>Unavailable</small></p>
                                    <?php } ?>
                                </div>
                                <div class="card-footer text-end bg-danger bg-opacity-75">
                                    <?php if (isset($discount_price) && !empty($discount_price) && $food_menu_item['status'] == '1') { ?>
                                        <span class="text-white">RM <?=number_format($discount_price, 2, '.', '')?>&nbsp;</span>
                                    <?php } ?>
                                    <span class="text-muted fw-bold font-century <?=(isset($discount_price) && !empty($discount_price) && $food_menu_item['status'] == '1' ? 'text-decoration-line-through' : '')?>" style="color: white !important;">RM <?=number_format($food_menu_item['price'], 2, '.', '')?></span>
                                </div>
                            </div>
                        </div>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
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
  </body>
</html>