<?php 
    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/message.php';
    include_once '../../../../admin/php/helpers/message.php';

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

        $conversations = getConversationsByCusID($connection, $_SESSION['login_cus_id']);
        foreach ($conversations as $key => $conversation){
            $total_result = getConversationChatsTotal($connection, $conversation['conversation_id'], ['seen' => 0, 'receiver_id' => $_SESSION['login_cus_id']]);
            $conversations[$key]['unseen_total'] = $total_result['COUNT(*)'];
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages &VerticalLine; F&amp;B Corner</title>

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

        #conversation-table{
            border: 1px solid black !important;
        }

        #conversation-table tr{
            border-color: #dee2e6 !important;
        }
        
        #conversation-table tbody{
            border-width: 2px;
        }

        .text-truncate{
            width: 67vw !important;
        }

        @media(max-width: 1300px){
            .text-truncate{
                width: 60vw !important;
            }
        }

        @media(max-width: 1000px){
            .text-truncate{
                width: 55vw !important;
            }
        }

        @media(max-width: 970px){
            .text-truncate{
                width: 50vw !important;
            }
        }

        @media(max-width: 900px){
            .text-truncate{
                width: 45vw !important;
            }
        }

        @media(max-width: 780px){
            .text-truncate{
                width: 40vw !important;
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
                <h1 style="cursor: default; font-size: 3rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Messages</h1>
                <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
            </div>
        </div>

        <?php if (count($conversations) > 0) { ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover" style="white-space: nowrap;" id="conversation-table">
              <tbody>
                  <?php foreach ($conversations as $conversation) { ?>
                      <tr id="con-<?=$conversation['conversation_id']?>">
                          <td style="cursor: pointer;">
                              <a href="chatroom.php?rest_id=<?=$conversation['rest_id']?>">
                              <div class="d-flex">
                                  <img src="../../../../admin/uploads/profile_pic/<?=$conversation['rest_profile']?>" height="80" width="80" class="m-3 rounded-circle" style="border: 3px solid #d0efff;">
                                  <div class="flex-grow-1 d-flex">
                                      <div class="flex-grow-1">
                                          <div class="d-flex">
                                              <span class="fw-bold font-size-28 d-inline-block m-3 mb-2"><?=$conversation['rest_name']?></span>
                                          </div>
                                          <span class="d-flex m-3 mt-0 text-muted last-msg-wrapper"><?=empty($conversation['message']) ? '<i class="fas fa-file m-2 ms-0" style="font-size: 20px;"></i><span class="d-inline-block text-truncate align-self-center"' . ($conversation['unseen_total'] == 0 ? '' : ' style="color: #48cb68; font-weight: bolder;"') . ' id="last-msg-' . $conversation['chat_id'] . '">' . substr($conversation['file'], strpos($conversation['file'], '-') + 1) . '</span>' : '<span class="d-inline-block text-truncate"' . ($conversation['unseen_total'] == 0 ? '' : ' style="color: #48cb68; font-weight: bolder;"') . ' id="last-msg-' . $conversation['chat_id'] . '">' . $conversation['message'] . '</span>'?></span>
                                      </div>
                                  </div>
                                  <div class="d-flex time-wrapper flex-column">
                                      <span class="align-self-end d-inline-block mb-0 mt-2 me-3 date-or-time">
                                      <?php $date = date('d/m/Y', strtotime($conversation['sent_at'])); ?>
                                      <?php if ($date == date('d/m/Y')) { ?>
                                          <?=date('h:i a', strtotime($conversation['sent_at']))?>
                                      <?php } else { ?>
                                          <?=date('d/m/Y', strtotime($conversation['sent_at']))?><br>
                                      <?php } ?>
                                      </span>
                                      <span class="badge m-3 bg-primary rounded-circle align-self-end font-size-20 new-msg-total<?=$conversation['unseen_total'] == 0 ? ' d-none' : '' ?>"><?=$conversation['unseen_total']?></span>
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
                <i class="fas fa-comments" style="font-size: 40px;"></i>&nbsp;&nbsp;
                <span style="line-height: 50px; font-size: 30px;">
                    No message history.
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

    <!-- custom javascript -->
    <script src="../../../javascript/message.js"></script>
  </body>
</html>


<?php

  } else {
    header("Location: ../homepage/index.php");
    exit;
  }
  
?>