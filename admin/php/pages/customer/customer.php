<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/customer.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'customer/customer', 'access_permission');
   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer &VerticalLine; F&amp;B Corner</title>

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

            .customer-list{
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
            <?php
                if ((int)$has_permission['has_permission']){
                    $customers = getRestaurantCustomers($connection, $_SESSION['login_rest_id']);
            ?>
                    <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                        Customer
                    </h3>

                    <div class="table-responsive customer-list">
                        <?php if (count($customers) == 0 || count($customers) == 1) { ?>
                        <table class="table table-bordered" style="white-space: nowrap;">
                        <?php } else { ?>
                        <table class="table table-bordered table-hover" style="white-space: nowrap;">
                        <?php } ?>
                            <thead>
                                <tr>
                                    <th width="5%" class="px-4">No.</td>
                                    <th width="15%" class="text-center">Image</td>
                                    <th width="25%" class="px-3">Name</td>
                                    <th width="35%" class="px-3">Email</td>
                                    <th width="15%" class="px-3">Contact Number</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $counter = 0;
                                    foreach ($customers as $customer) { 
                                        $counter++;
                                ?>
                                    <tr class="align-middle">
                                        <td width="5%" class="text-center"><?=$counter?></td>
                                        <td width="15%" class="text-center"><img src="../../../../store/uploads/profile_pic/<?=$customer['profile_pic']?>" height="80" width="80" class="rounded-circle" style="border: 3px solid #d0efff;"></td>
                                        <td class="px-3"><?=$customer['firstname'] . ' ' . $customer['lastname']?></td>
                                        <td class="px-3"><?=$customer['email']?></td>
                                        <td class="px-3"><?=$customer['contact_num']?></td>
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
            $(document).ready(function() {
                $('.customer-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($customers) == 0) { ?>
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