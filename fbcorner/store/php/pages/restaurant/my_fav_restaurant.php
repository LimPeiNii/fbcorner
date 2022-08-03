<?php 
    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/promotion.php';

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

        $favourite_restaurants = getFavouriteRestaurants($connection, $_SESSION['login_cus_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourite Restaurants &VerticalLine; F&amp;B Corner</title>

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

        #fav-rest-table{
            border: 1px solid black !important;
        }

        #fav-rest-table tr{
            border-color: #dee2e6 !important;
        }
        
        #fav-rest-table tbody{
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
                <h1 style="cursor: default; font-size: 2.5rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Fav Restaurants</h1>
                <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
            </div>
        </div>

        <?php if (count($favourite_restaurants) > 0) { ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover" style="white-space: nowrap;" id="fav-rest-table">
              <tbody>
                  <?php foreach ($favourite_restaurants as $favourite_restaurant) { ?>
                      <tr>
                          <td class="p-3">
                            <?php 
                                $on_promotions = getPromotions($connection, $favourite_restaurant['rest_id'], ['status' => '1']);
                                if ($on_promotions)
                                    $on_promo_label = '<div class="d-inline-block px-2 py-1 rounded bg-danger bg-opacity-75 text-white font-size-12">PROMO</div>';
                                else
                                    $on_promo_label = '';
                            ?>
                            <a href="profile.php?visit_rest_id=<?=$favourite_restaurant['rest_id']?>" class="bg-opacity-25" style="border: 0px;">
                                <div class="d-flex w-100">
                                    <img src="../../../../admin/uploads/profile_pic/<?=$favourite_restaurant['rest_profile']?>" height="150" width="150" class="align-self-center">
                                        <div class="flex-grow-1">
                                            <div class="ms-3 d-flex">
                                                <div class="flex-grow-1">
                                                    <h4 class="mb-1 me-2 d-inline-block" style="cursor: pointer"><?=$favourite_restaurant['rest_name']?></h4><?=!empty($on_promo_label) ? $on_promo_label : ''?>
                                                    <div class="text-muted">
                                                        <?php if (!empty($favourite_restaurant['avg_rating'])) { ?>
                                                            <?php 
                                                                if (floor($favourite_restaurant['avg_rating']) == ceil($favourite_restaurant['avg_rating'])) { 
                                                                    $final_avg_rating = $favourite_restaurant['avg_rating'];
                                                                } else {
                                                                    $final_avg_rating = floor($favourite_restaurant['avg_rating']);
                                                                }
                                                            ?>
                                                            <?php for ($i=0; $i<$final_avg_rating; $i++) { ?>
                                                                <i class="fas fa-star text-danger"></i>
                                                            <?php } ?>
                                                            <?php if (floor($favourite_restaurant['avg_rating']) != ceil($favourite_restaurant['avg_rating'])) { ?>
                                                                <i class="fas fa-star-half-alt text-danger"></i>
                                                                <?php for ($i=0; $i<(4-$final_avg_rating); $i++) { ?>
                                                                    <i class="far fa-star text-danger"></i>
                                                                <?php } ?>
                                                            <?php } else { ?>
                                                                <?php for ($i=0; $i<(5-$final_avg_rating); $i++) { ?>
                                                                    <i class="far fa-star text-danger"></i>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <?php for ($i=0; $i<5; $i++) { ?>
                                                                <i class="far fa-star text-danger"></i>
                                                            <?php } ?>
                                                            &nbsp; <span class="text-muted">0 Ratings</span>
                                                        <?php } ?>
                                                    </div>
                                                    <?php if (!empty($favourite_restaurant['city'])) { ?>
                                                        <small class="text-muted"><i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;<?=$favourite_restaurant['city']?></small>
                                                        <?php if (!empty($favourite_restaurant['dining_style']) || (!empty($favourite_restaurant['opening_time']) && !empty($favourite_restaurant['closing_time']))) { ?>
                                                        &nbsp;
                                                        <?php } else { ?>
                                                        <br>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if (!empty($favourite_restaurant['dining_style'])) { ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-utensils"></i>&nbsp;&nbsp;<span style="text-transform: capitalize;"><?=$favourite_restaurant['dining_style']?></span>
                                                        </small>
                                                        <?php if (!empty($favourite_restaurant['opening_time']) && !empty($favourite_restaurant['closing_time'])) { ?>
                                                        &nbsp;
                                                        <?php } else { ?>
                                                        <br>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php if (!empty($favourite_restaurant['opening_time']) && !empty($favourite_restaurant['closing_time'])) { ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock"></i>&nbsp;&nbsp;<?=date('h:i a', strtotime($favourite_restaurant['opening_time']))?> to <?=date('h:i a', strtotime($favourite_restaurant['closing_time']))?>
                                                        </small>
                                                        <br>
                                                    <?php } ?>
                                                    <?php $tags = json_decode($favourite_restaurant['tags'], true);?>
                                                    <?php if (!empty($tags)) {
                                                            foreach ($tags as $tag){
                                                    ?>
                                                        <small class="text-muted">#<?=$tag?>&nbsp;</small>
                                                    <?php } ?>
                                                        <br>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
            <div class="w-100 text-center my-5 alert alert-info">
                <i class="fas fa-heart" style="font-size: 40px;"></i>&nbsp;&nbsp;
                <span style="line-height: 50px; font-size: 30px;">
                    No saved favourite restaurant.
                </span>
            </div>
        <?php } ?>
      </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>
  </body>
</html>


<?php

  } else {
    header("Location: ../homepage/index.php");
    exit;
  }
  
?>