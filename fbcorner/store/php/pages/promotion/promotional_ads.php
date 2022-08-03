<?php 
  
    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/promotion.php';

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

    $promotional_ads = getPromotionalAds($connection, ['status' => 1, 'order_by' => 'created_date', 'asc_desc' => 'DESC']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>

    <style>
      .grid-imgs-col-2, .grid-imgs-col-3{
        box-sizing: border-box;
        display: grid;
        gap: 16px;
        grid-auto-rows: 300px;
      }

      .grid-imgs-col-1{
        grid-template-columns: repeat(1, 1fr);
      }

      .grid-imgs-col-2{
        grid-template-columns: repeat(2, 1fr);
      }

      .grid-imgs-col-3{
        grid-template-columns: repeat(3, 1fr);
      }

      .grid-imgs-col-2 img, .grid-imgs-col-3 img{
        width: 100%;
        height: 100%;
        /* object-fit: cover; */
      }

      .grid-imgs-col-1 img{
        height: 600px;
        /* object-fit: cover; */
        max-width: 100%;
      }

      body{
        height: 100%;
      }

      .content-wrapper p{
        -webkit-line-clamp: 3;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      #close-photo-btn:hover{
        opacity: 0.9 !important;
      }

      #close-photo-btn i:before{
        color: #fff;
        background-color: #212529;
        border-color: #212529;
        border-radius: 0.25rem;
      }

      .img-wrapper{
        cursor: pointer;
      }
    </style>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>
    
    <?php include_once '../../common/signin_signup.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
      <?php if (!$promotional_ads) { ?>
        <div class="m-5">
          <div class="w-100 text-center my-5 alert alert-info">
              <i class="fas fa-bullhorn" style="font-size: 40px;"></i>&nbsp;&nbsp;
              <span style="line-height: 50px; font-size: 30px;">
                There are currently no promotions.
              </span>
          </div>
        </div>
      <?php } else { ?>
        <?php foreach ($promotional_ads as $promotional_ad) { ?>
          <div class="my-3" style="background-color: #f0f0f0;">
            <div class="d-flex mx-4 py-4 pb-3">
              <a href="../restaurant/profile.php?visit_rest_id=<?=$promotional_ad['rest_id']?>"><img src="../../../../admin/uploads/profile_pic/<?=$promotional_ad['rest_profile']?>" style="border: 1px solid black" height="50" width="50" class="rounded-circle"></a>
              <div class="ms-2">
                <a href="../restaurant/profile.php?visit_rest_id=<?=$promotional_ad['rest_id']?>"><div class="fw-bold font-century"><?=$promotional_ad['rest_name']?></div></a>
                <small class="font-century" stlye="font-size: 0.8rem;"><?=date('d/m/Y h:i a', strtotime($promotional_ad['created_date']))?></small>
              </div>
            </div>
            <div class="mx-4">
              <div class="fw-bold font-size-22"><?=$promotional_ad['title']?></div>
              <div class="content-wrapper"><p class="mb-1"><?=$promotional_ad['content']?></p></div>
              <span class="text-primary read-more-less d-none" style="cursor: pointer">Read <span class="text-primary">More</span><span class="d-none text-primary">Less</span></span>
            </div>
            <div class="mx-4 mt-1 pb-4">
            <?php if (!empty($promotional_ad['photos'])) { ?>
              <?php $photos = json_decode($promotional_ad['photos'], true); ?>
              <div class="grid-img-wrap <?=count($photos) == 1 ? 'grid-imgs-col-1 d-flex justify-content-center' : (count($photos) < 4 ? 'grid-imgs-col-2' : 'grid-imgs-col-3') ?>">
              <?php foreach ($photos as $key => $photo) {?>
                <?php if ($key == 4) break; ?>
                <?php $modified_photo_names = str_replace('"', "'", $promotional_ad['photos']); ?>
                <div class="img-wrapper position-relative text-center text-white fw-bold font-size-18" onclick="loadModalCarouselImg(<?=$modified_photo_names?>); $('.carousel').carousel(<?=$key?>)" data-bs-toggle="modal" data-bs-target="#photoModal">
                    <?php if ($key == 3) { ?>
                        <span class="position-absolute top-0 bg-dark bg-opacity-75 d-flex w-100 h-100 img-more" style="cursor: pointer;"><span class="w-100 align-self-center">+ <?=(string)(count($photos) - 3)?> more</span></span>
                    <?php } ?>
                    <img src="../../../../admin/uploads/promo_ads_photo/<?=$photo?>"<?=$key == 0 ? ' class="first-img grid-img"' : ' class="grid-img"' ?>>
                </div>
              <?php } ?>
              </div>
            <?php } ?>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <!-- Photo Modal + Carousel -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark position-relative">
                <div id="carouselPhotoFade" class="carousel slide carousel-fade" data-bs-interval="false">
                    <div class="carousel-inner">
                        
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotoFade" data-bs-slide="prev">
                        <span class="p-4 d-inline-block" aria-hidden="true"><i class="bi bi-caret-left-fill btn-dark rounded" style="font-size: 50px;"></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotoFade" data-bs-slide="next">
                        <span class="p-4 d-inline-block" aria-hidden="true"><i class="bi bi-caret-right-fill btn-dark rounded" style="font-size: 50px;"></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
                <button type="button" class="position-absolute top-0 end-0 p-3 pt-0 text-white" data-bs-dismiss="modal" aria-label="Close" id="close-photo-btn" style="z-index: 1; opacity: 0.5"><i class="bi bi-x" style="font-size: 50px;"></i></button>
            </div>
        </div>
    </div>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>

    <script>
      function loadModalCarouselImg(img_names){
        var html = '';
        $.each(img_names, function(key, photo) {
          html += '<div class="carousel-item' + (key == 0 ? ' active' : '') + '">'
          html +=   '<img src="../../../../admin/uploads/promo_ads_photo/' + photo + '" class="d-block m-auto carousel-img">'
          html += '</div>';
        });

        $('#photoModal .carousel-inner').html(html);

        $('.carousel-img').each(function() {
          var this_element = $(this);
          $("<img>").attr('src', $(this).attr('src'))
          .on('load', function() {
              if (parseInt(`${this.height}`) > parseInt(`${this.width}`)){
                  this_element.css({
                      'height': '100vh',
                      'max-width': '100%'
                  });
              } else if (parseInt(`${this.height}`) == parseInt(`${this.width}`)){
                  this_element.css({
                      'height': '100vh',
                      'max-width': '100vw'
                  });
              } else {
                  this_element.css({
                      'height': '100vh',
                      'width': '100vw'
                  });
              }
          });
        });
      }

      $(document).ready(function() {
        $('.first-img').each(function() {
          var this_element = $(this);
          $("<img>").attr('src', $(this).attr('src'))
          .on('load', function() {
              var total_img_num = this_element.parent().parent().find('.grid-img').length;
              if (parseInt(`${this.height}`) >= parseInt(`${this.width}`)){
                if (total_img_num != 1){
                    this_element.parent().css('grid-row', 'span 2');
                    if (total_img_num == 2){
                        this_element.parent().next().css('grid-row', 'span 2');
                    } else if (total_img_num == 4){
                        var child_to_remove = this_element.parent().parent().find('div:nth-child(4)');
                        var img_more_text = child_to_remove.find('.img-more span').text();
                        child_to_remove.find('.img-more span').text('+ ' + (parseInt(img_more_text.substring(2, img_more_text.indexOf(' more'))) + 1).toString() + ' more');
                        this_element.parent().parent().find('div:nth-child(3)').prepend(child_to_remove.find('.img-more'));
                        child_to_remove.remove();
                        this_element.parent().parent().toggleClass('grid-imgs-col-2 grid-imgs-col-3')
                    }
                }
              } else {
                if (total_img_num == 1){
                    this_element.css('width', '100%');
                    this_element.parent().parent().removeClass('d-flex justify-content-center');
                } else {
                    this_element.parent().css('grid-column', 'span 2');
                    if (total_img_num == 2){
                        this_element.parent().next().css('grid-column', 'span 2');
                    } else if (total_img_num == 4){
                        this_element.parent().css('grid-column', 'span 3');
                    }
                }
              }
          });
        });

        $('.content-wrapper p').each(function () {
          if ($(this)[0].offsetHeight < $(this)[0].scrollHeight || $(this)[0].offsetWidth < $(this)[0].scrollWidth)
            $(this).parent().next().removeClass('d-none');
        });

        $('.read-more-less').click(function() {
          $(this).children('span').toggleClass('d-none');
          $(this).prev().toggleClass('content-wrapper');
        });
      });
    </script>
  </body>
</html>