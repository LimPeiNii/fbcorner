<?php 
  
    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/restaurant.php';

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

    $top_rated_restaurants = searchRestaurants($connection, ['rating' => [5]]);
    $on_promotions_restaurants = searchRestaurants($connection, ['promotion' => true]);
    
    $timestamp = strtotime('-1 month');
    $date = date('Y-m-d', $timestamp);
    $newly_joined_restaurants = searchRestaurants($connection, ['joined_date' => $date]);

    $most_favoured_restaurants = searchRestaurants($connection, ['no_zero_fav_count' => true, 'sort_by' => 'total DESC', 'limit' => 20]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>

    <style>
      body{
        height: 100%;
      }
      
      #autocomplete-restaurant-list div, #autocomplete-food-list div{
        border: 2px solid #d4d4d4;
        border-top: 0px;
        border-bottom: 0px;
      }

      #autocomplete-restaurant-list div.active, #autocomplete-food-list div.active {
        background-color: #e9e9e9;
      }

      #top-rated .owl-item img, #on-promotion .owl-item img, #most-favoured .owl-item img, #new-restaurants .owl-item img{
        height: 178.36px;
      }
    </style>

</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>
    
    <?php include_once '../../common/signin_signup.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
      <!-- owl carousel banner-->
      <section id="banner">
        <div class="owl-carousel owl-theme">
          <div class="item">
            <img src="../../../assest/banner1.png" alt="banner1">
            <div class="banner-text-right text-white">
              <h1 class="font-fjalla font-size-6vw">Ipoh Kway Teow Soup</h1><div class="font-lora font-size-2vw">Flat rice noodles in chicken soup, topped with chives, spring onions and bean sprouts.<br><br></div>
              <a href="../restaurant/profile.php?visit_rest_id=R1"><button type="button" class="btn btn-light" style="font-size: 1.5vw;">Visit Restaurant</button></a>
            </div>
          </div>
          <div class="item">
            <img data-src="../../../assest/banner2.png" class="owl-lazy" alt="banner2">
            <div class="banner-text-left text-black">
              <h1 class="font-fjalla font-size-6vw">Lasagna</h1><div style="width: 50%;" class="font-lora font-size-2vw">Lasagna with vegetables, minced meat, cheese bolognese and bechamel sauce.<br><br></div>
              <a href="../restaurant/profile.php?visit_rest_id=R3"><button type="button" class="btn btn-dark" style="font-size: 1.5vw;">Visit Restaurant</button></a>
            </div>
          </div>
          <div class="item">
            <img data-src="../../../assest/banner3.png" class="owl-lazy" alt="banner3">
            <div class="banner-text-left text-black">
              <h1 class="font-fjalla font-size-6vw">Sushi Rolls</h1><div class="font-lora font-size-2vw">Sushi rolls set with salmon and tuna fish <br><br></div>
              <a href="../restaurant/profile.php?visit_rest_id=R5"><button type="button" class="btn btn-dark" style="font-size: 1.5vw;">Visit Restaurant</button></a>
            </div>
          </div>
        </div>
      </section>

      <!-- search bar -->
      <?php include_once '../../common/search.php'; ?>

      <!-- top rated -->
      <div id="top-rated" class="mx-5 mt-3">
        <div class="py-4 px-4 mx-auto">
          <h5>Top Rated</h5>
          <hr style="border-width: 3px;">
          
          <!-- restaurants -->
          <div class="owl-carousel owl-theme mx-2" style="min-height: 100px;">
            <?php if ($top_rated_restaurants) { ?>
              <?php foreach ($top_rated_restaurants as $top_rated_restaurant) { ?>
                <div class="item shadow mx-2 p-3 mb-5 bg-body rounded border border-secondary">
                  <a href="../restaurant/profile.php?visit_rest_id=<?=$top_rated_restaurant['rest_id']?>"><img src="../../../../admin/uploads/profile_pic/<?=$top_rated_restaurant['rest_profile']?>" alt="<?=$top_rated_restaurant['rest_name']?>" class="img-fluid"></a>
                  <div class="text-center py-2">
                    <span class="font-fjalla"><?=$top_rated_restaurant['rest_name']?></span>
                    <div class="text-danger font-size-12 py-2">
                      <?php for ($i=0; $i<floor($top_rated_restaurant['avg_rating']); $i++) { ?>
                        <span><i class="fas fa-star"></i></span>
                      <?php } ?>
                      <?php for ($j=0; $j<(5-floor($top_rated_restaurant['avg_rating'])); $j++) { ?>
                        <span><i class="far fa-star"></i></span>
                      <?php } ?>
                    </div>
                    <a href="../restaurant/profile.php?visit_rest_id=<?=$top_rated_restaurant['rest_id']?>"><button type="button" class="btn btn-secondary font-size-12">Visit Restaurant</button></a>
                  </div>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- on promotion -->
      <div id="on-promotion" class="mx-5">
        <div class="py-4 px-4 mx-auto">
          <h5>On Promotions</h5>
          <hr style="border-width: 3px;">
          
          <!-- restaurants -->
          <div class="owl-carousel owl-theme mx-2" style="min-height: 100px;">
            <?php if ($on_promotions_restaurants) { ?>
              <?php foreach ($on_promotions_restaurants as $on_promotions_restaurant) { ?>
                <div class="item shadow mx-2 p-3 mb-5 bg-body rounded border border-secondary">
                  <a href="../restaurant/profile.php?visit_rest_id=<?=$on_promotions_restaurant['rest_id']?>"><img src="../../../../admin/uploads/profile_pic/<?=$on_promotions_restaurant['rest_profile']?>" alt="<?=$on_promotions_restaurant['rest_name']?>" class="img-fluid"></a>
                  <div class="text-center py-2">
                    <span class="font-fjalla"><?=$on_promotions_restaurant['rest_name']?></span>
                    <div class="text-danger font-size-12 py-2">
                      <?php for ($i=0; $i<floor($on_promotions_restaurant['avg_rating']); $i++) { ?>
                        <span><i class="fas fa-star"></i></span>
                      <?php } ?>
                      <?php for ($j=0; $j<(5-floor($on_promotions_restaurant['avg_rating'])); $j++) { ?>
                        <span><i class="far fa-star"></i></span>
                      <?php } ?>
                    </div>
                    <a href="../restaurant/profile.php?visit_rest_id=<?=$on_promotions_restaurant['rest_id']?>"><button type="button" class="btn btn-secondary font-size-12">Visit Restaurant</button></a>
                  </div>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- most favoured -->
      <div id="most-favoured" class="mx-5">
        <div class="py-4 px-4 mx-auto">
          <h5>Most Favoured</h5>
          <hr style="border-width: 3px;">
          
          <!-- restaurants -->
          <div class="owl-carousel owl-theme mx-2" style="min-height: 100px;">
            <?php if ($most_favoured_restaurants) { ?>
              <?php foreach ($most_favoured_restaurants as $most_favoured_restaurant) { ?>
                <div class="item shadow mx-2 p-3 mb-5 bg-body rounded border border-secondary">
                  <a href="../restaurant/profile.php?visit_rest_id=<?=$most_favoured_restaurant['rest_id']?>"><img src="../../../../admin/uploads/profile_pic/<?=$most_favoured_restaurant['rest_profile']?>" alt="<?=$most_favoured_restaurant['rest_name']?>" class="img-fluid"></a>
                  <div class="text-center py-2">
                    <span class="font-fjalla"><?=$most_favoured_restaurant['rest_name']?></span>
                    <div class="text-danger font-size-12 py-2">
                      <?php for ($i=0; $i<floor($most_favoured_restaurant['avg_rating']); $i++) { ?>
                        <span><i class="fas fa-star"></i></span>
                      <?php } ?>
                      <?php for ($j=0; $j<(5-floor($most_favoured_restaurant['avg_rating'])); $j++) { ?>
                        <span><i class="far fa-star"></i></span>
                      <?php } ?>
                    </div>
                    <a href="../restaurant/profile.php?visit_rest_id=<?=$most_favoured_restaurant['rest_id']?>"><button type="button" class="btn btn-secondary font-size-12">Visit Restaurant</button></a>
                  </div>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- newly joined restaurants -->
      <div id="new-restaurants" class="mx-5">
        <div class="py-4 px-4 mx-auto">
          <h5>Newly Joined</h5>
          <hr style="border-width: 3px;">
          
          <!-- restaurants -->
          <div class="owl-carousel owl-theme mx-2" style="min-height: 100px;">
            <?php if ($newly_joined_restaurants) { ?>
              <?php foreach ($newly_joined_restaurants as $newly_joined_restaurant) { ?>
                <div class="item shadow mx-2 p-3 mb-5 bg-body rounded border border-secondary">
                  <a href="../restaurant/profile.php?visit_rest_id=<?=$newly_joined_restaurant['rest_id']?>"><img src="../../../../admin/uploads/profile_pic/<?=$newly_joined_restaurant['rest_profile']?>" alt="<?=$newly_joined_restaurant['rest_name']?>" class="img-fluid"></a>
                  <div class="text-center py-2">
                    <span class="font-fjalla"><?=$newly_joined_restaurant['rest_name']?></span>
                    <div class="text-danger font-size-12 py-2">
                      <?php for ($i=0; $i<floor($newly_joined_restaurant['avg_rating']); $i++) { ?>
                        <span><i class="fas fa-star"></i></span>
                      <?php } ?>
                      <?php for ($j=0; $j<(5-floor($newly_joined_restaurant['avg_rating'])); $j++) { ?>
                        <span><i class="far fa-star"></i></span>
                      <?php } ?>
                    </div>
                    <a href="../restaurant/profile.php?visit_rest_id=<?=$newly_joined_restaurant['rest_id']?>"><button type="button" class="btn btn-secondary font-size-12">Visit Restaurant</button></a>
                  </div>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <script src="../../../javascript/index.js"></script>

    <script src="../../../javascript/signin_signup.js"></script>

    <script src="../../../javascript/search.js"></script>

    <script>
      $(document).ready(function() {
        $('#view-all-result').click(function() {
          $('#search-btn').click();
        });
      });
    </script>
  </body>
</html>