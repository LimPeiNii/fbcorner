<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/promotion.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'promotion/promotional_ads', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'promotion/promotional_ads', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            $promotional_ads = getPromotionalAds($connection, $_SESSION['login_rest_id']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotional Ads &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            input[type=checkbox]{
                transform: scale(1.2);
            }

            table{
                border: 1px solid black !important;
            }

            tr, th{
                border-color: #dee2e6 !important;
            }
            
            thead, tbody{
                border-width: 2px;
            }

            th{
                background-color: rgb(235, 235, 235) !important;
            }

            .promotional-ads-list{
                margin-top: 15px;
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
                <button type="button" onclick="delete_data();" class="btn bg-danger bg-opacity-75 float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><i class="fas fa-trash-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <a href="promotional_ads_form.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Promotional Ads
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive promotional-ads-list">
                    <?php if (count($promotional_ads) == 0 || count($promotional_ads) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4">No.</td>
                                <th width="55%" class="px-3">Title</td>
                                <th width="20%" class="px-3">Status</td>
                                <th width="15%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                foreach ($promotional_ads as $promotional_ad) { 
                                    $counter++;
                            ?>
                                <tr class="align-middle">
                                    <td style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$promotional_ad['promotional_ads_id']?>"></td>
                                    <td width="5%" class="text-center"><?=$counter?></td>
                                    <td width="55%" class="px-3"><?=$promotional_ad['title']?></td>
                                    <td width="20%" class="px-3"><?=$promotional_ad['status'] ? 'Available' : 'Unavailable'?></td>
                                    <td width="15%" class="text-center">
                                        <a href="promotional_ads_form.php?promotional_ads_id=<?=$promotional_ad['promotional_ads_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center mt-3">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>    
    </div>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script>
            function close_message() {
                $('.form-message').remove();
            }

            function delete_data(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (confirm("Are you sure you want to delete the promotional ad(s)?")){
                            $.ajax({ 
                                url: '../../helpers/promotion.php',
                                data: {
                                    selected_promotional_ads: selected_data,
                                    delete_promotional_ads: true
                                },
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to delete the promotional ad(s).</div>').insertBefore('.promotional-ads-list');
                    <?php } ?>
                }
            }

            $(document).ready(function() {
                $('#select-unselect-all').on('click', function () {
                    if ($(this).is(':checked')){
                        $('.content').find('.data-checkbox').prop('checked', true);
                    } else {
                        $('.content').find('.data-checkbox').prop('checked', false);
                    }
                });

                $('.data-checkbox').click(function() {
                    if ($('#select-unselect-all').is(':checked')){
                        $('#select-unselect-all').prop('checked', false);
                    }
                });

                $('.promotional-ads-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($promotional_ads) == 0) { ?>
                    $('.dataTables_empty').addClass('text-center');
                    $('.dataTables_empty').text('No results !');
                <?php } ?>
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