<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/voucher.php';
    include_once '../../helpers/customer.php';

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

      $old_vouchers = getVouchers($connection, $_SESSION['login_cus_id'], ['invalid_or_used' => true]);
      $new_vouchers = getVouchers($connection, $_SESSION['login_cus_id'], ['applied_order_id' => '0', 'valid' => true]);
      $points = getCustomerPoints($connection, $_SESSION['login_cus_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vouchers &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
    
    <style>
        #title-image, #title-image img{
            min-width: 180px;
            max-width: 180px;
        }

        #vouchers-wrapper .col{
            max-height: 500px;
            min-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19); 
        }

        #vouchers-wrapper .col table{
          width: 95%;
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
                <h1 style="cursor: default; font-size: 3rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Vouchers</h1>
                <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
            </div>
        </div>

        <div class="d-flex flex-column mt-5">
            <div class="text-end mx-4 fw-bold main-points">Points: <span class="point-wrapper"><?=$points['points']?></span></div>
            <button type="button" class="btn btn-outline-primary mx-4 mt-2 align-self-end" style="width: fit-content;" onclick="showVoucherStore(); ">Redeem Voucher</button>
        </div>
        <div id="vouchers-wrapper" class="row row-cols-1 row-cols-sm-1 row-cols-md-2 m-auto mb-3 mt-3" style="margin-bottom: 100px !important;">
          <div class="box-style col p-0 m-4 mt-0 color-2">
            <div class="color-2 px-3 pt-2 w-100">
              <h5 class="fw-bold m-2">Invalid & Used Vouchers</h5>
              <div class="p-3 my-4" style="min-width: fit-content;">
                <?php $color = ['bg-info', 'bg-warning bg-opacity-75', 'bg-danger bg-opacity-50', 'bg-primary bg-opacity-50'];?>
                <?php foreach ($old_vouchers as $old_voucher) { ?>
                  <div class="d-flex bd-highlight mb-4 position-relative" style="min-width: 427px; border: 2px solid black; border-left-style: dashed; opacity: 0.5; background-color: #fafafa;">
                      <div class='px-4 py-2 bd-highlight align-self-center <?=$color[(int)$old_voucher['voucher_type_id'] - 1]?>' style='font-size: 63px;'><i class='bi bi-ticket-perforated-fill'></i></div>
                      <div class="ps-3 bd-highlight flex-grow-1 align-self-center">
                        <h5><strong><?=$old_voucher['name']?></strong></h5>
                        <h6>Value: RM <?=number_format($old_voucher['equal_price'], 2, '.', '')?><h6>
                        <small class="text-muted">Valid Till: <?=date('d/m/Y', strtotime($old_voucher['expired_date']))?></small>
                      </div>
                      <span class="position-absolute px-3 translate-middle-y bg-secondary" style="top: 20px; right: -4px; color: white;"><?=$old_voucher['applied_order_id'] != '0' ? 'Used' : 'Expired'?></span>
                  </div>
                <?php } ?>
                </div>
            </div>
          </div>
          <div class="box-style col p-0 m-4 mt-0 color-2">
            <div class="color-2 px-3 pt-2 w-100">
              <h5 class="fw-bold m-2">Latest Vouchers</h5>
                <div class="p-3 my-4" style="min-width: fit-content;">
                <?php $color = ['bg-info', 'bg-warning bg-opacity-75', 'bg-danger bg-opacity-50', 'bg-primary bg-opacity-50'];?>
                <?php foreach ($new_vouchers as $new_voucher) { ?>
                  <div class="d-flex bd-highlight mb-4 position-relative" style="min-width: 427px; border: 2px solid black; border-left-style: dashed; background-color: #fafafa;">
                      <div class='px-4 py-2 bd-highlight align-self-center <?=$color[(int)$new_voucher['voucher_type_id'] - 1]?>' style='font-size: 63px;'><i class='bi bi-ticket-perforated-fill'></i></div>
                      <div class="ps-3 bd-highlight flex-grow-1 align-self-center">
                        <h5><strong><?=$new_voucher['name']?></strong></h5>
                        <h6>Value: RM <?=number_format($new_voucher['equal_price'], 2, '.', '')?><h6>
                        <small class="text-muted">Valid Till: <?=date('d/m/Y', strtotime($new_voucher['expired_date']))?></small>
                      </div>
                      <?php if (!empty($new_voucher['in_use_voucher_id'])) { ?>
                      <span class="position-absolute px-3 translate-middle-y bg-secondary" style="top: 20px; right: -4px; color: white;">In Use</span>
                      <?php } ?>
                  </div>
                <?php } ?>
                </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <div class="modal fade p-0" id="voucherModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Voucher Store</h5>
                    <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="text-end mx-4 mb-2 fw-bold" style="color: RGB(0, 127, 255);">Points: <span class="point-wrapper"><?=$points['points']?></span></div>
                        <div id="voucher-body"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>    

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <script>
      function showVoucherStore() {
        $.ajax({ 
            url: '../../helpers/voucher.php',
            data: {get_voucher_types: true},
            type: 'post',
            success: function(output){
                var json = $.parseJSON(output);

                var html = "";
                var color = ['text-info', 'text-warning', 'text-danger', 'text-primary'];

                for (i in json){
                  html += "<div class='mb-3 mx-3 py-2 px-3 d-flex flex-wrap bd-highlight mb-3' style='border: 2px solid black; border-left-style: dashed;'>";
                  html += "<div class='pe-4 p-3 bd-highlight align-self-center " + color[i] + "' style='font-size: 63px;'><i class='bi bi-ticket-perforated-fill'></i></div>";
                  html += "<div class='p-2 bd-highlight flex-grow-1'>";

                  for (j in json[i]){
                    if (j != 'id'){
                      if (j == 'Name')
                        html += "<h5><strong>" + json[i][j] + "</strong></h5>";
                      else
                        html += j + ":&nbsp;" + json[i][j] + "<br>";
                    }
                  }

                  html += "</div>";
                  html += "<div class='py-3 ps-2 pe-1 bd-highlight align-self-center'><button type='button' class='btn btn-success' onclick=redeemVoucher('" + json[i]['id'] + "');> Redeem</button></div>";
                  html += "</div>";
                }
            
                $("#voucherModal .modal-body #voucher-body").html(html);
                
                $('#voucherModal').modal('show');
                $("nav, main, footer").addClass('blur');
            }
        });
      }

      var refresh_page = false;
      function redeemVoucher(voucher_type_id){
        $.ajax({
          url: '../../helpers/voucher.php',
          data: {
            voucher_type_id: voucher_type_id,
            points: parseInt($('.main-points .point-wrapper').text()),
            redeem_voucher: true
          },
          method: 'post',
          success: function(output) {
            var json = $.parseJSON(output);
            
            if (json['error']){
              slideInMsg('error', json['error']);
            } else {
              slideInMsg('success', json['success']);
              $('.point-wrapper').text(json['points_left']);
              refresh_page = true;
            }
          }
        })
      }

      $(document).ready(function() {
        $('.close-details-modal').click(function() {
          if (refresh_page)
            window.location.reload();
          else {
            $("nav, main, footer").removeClass('blur');
          }
        });

        slideInMsg('info', 'Your points will be auto reset every three months, use them before they gone!')
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