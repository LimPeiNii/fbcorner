<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/food_menu.php';
        include_once '../../helpers/category.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'food_menu/food_menu', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'food_menu/food_menu', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            $food_items = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['order_by' => 'item_code', 'asc_desc' => 'ASC']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Menu &VerticalLine; F&amp;B Corner</title>

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

            tbody, thead{
                border-width: 2px;
            }

            th{
                background-color: rgb(235, 235, 235) !important;
            }

            .food-menu-list{
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
                <a href="food_menu_form.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Food Menu
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>
                    
                <div class="table-responsive food-menu-list">
                    <?php if (count($food_items) == 0 || count($food_items) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr class="align-middle">
                                <th width="5%" class="px-4 text-center">No.</td>
                                <th width="15%" class="px-3 text-center">Image</td>
                                <th width="11%" class="px-3">Item Code</td>
                                <th width="20%" class="px-3">Item Name</td>
                                <th width="15%" class="px-3">Category</td>
                                <th width="10%" class="px-3">Price (RM)</td>
                                <th width="12%" class="px-3">Status</td>
                                <th width="15%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 0; ?>
                            <?php foreach ($food_items as $food_item) { ?>
                            <?php $counter++; ?>
                                <tr class="align-middle">
                                    <td width="5%" class="text-center"><?=$counter?></td>
                                    <td width="15%" class="px-3 text-center"><img height="100" src="../../../uploads/food_menu_photo/<?=$food_item['image']?>" style="min-width: 100px;max-width: 120px;"></td>
                                    <td width="11%" class="px-3"><?=$food_item['item_code']?></td>
                                    <td width="20%" class="px-3"><?=$food_item['item_name']?></td>
                                    <td width="15%" class="px-3"><?=getCategory($connection, $food_item['category_id'])['category_name']?></td>
                                    <td width="10%" class="px-3"><?=number_format($food_item['price'], 2, '.', '')?></td>
                                    <?php if ($food_item['status'] == '1') { ?>
                                        <td width="12%" class="px-3">Available</td>
                                    <?php } else { ?>
                                        <td width="12%" class="px-3">Unavailable</td>
                                    <?php } ?>
                                    <td width="15%" class="text-center">
                                        <button type="button" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$food_item['item_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                        <a href="food_menu_form.php?item_id=<?=$food_item['item_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
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

    <?php if ((int)$has_permission['has_permission']){ ?>
        <div class="modal fade p-0" id="detailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Food Item Details</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script>
            function close_message() {
                $('.form-message').remove();
            }

            function viewDetails(id) {
                $.ajax({ 
                    url: '../../helpers/food_menu.php',
                    data: { id: id },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        html += "<tr>";
                        html += "<td width='30%' class='px-3 fw-bold'>Image</td>";
                        html += "<td width='70%' class='p-3'><img style='min-width: 100px; max-width: 120px;' height='100' src='../../../uploads/food_menu_photo/" + json['Image'] + "'></td>";
                        html += "</tr>";

                        for (i in json){
                            if (i != 'Image' && i != 'Ingredient' && i != 'Promotions'){
                                html += "<tr>";
                                html += "<td width='30%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='70%' class='px-3 py-2'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }
                        
                        html += "<tr>";
                        html += "<td width='30%' class='px-3 fw-bold'>Promotions</td>";
                        html += "<td width='70%' class='px-3 py-2'>"
                        html += "<table style='border: 0px solid black !important;'>"
                        html += "<tbody style='border: 0px solid black !important;'>"

                        if (json['Promotions']){
                            for (j in json['Promotions']){
                                html += "<tr class='align-top'>";
                                html += "<td width='3%' class='px-1 py-1'>" + (parseInt(j)+1).toString() + ".</td>";
                                html += "<td width='97%' class='px-2 py-1'>" + json['Promotions'][j] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</tbody>";
                        html += "</table>";
                        html += "</td>";
                        html += "</tr>";

                        html += "</table>";

                        if (!jQuery.isEmptyObject(json["Ingredient"])){
                            html += "<br><table class='table w-100 mt-1 align-middle'>";

                            html += "<tr class='table-secondary'>";
                            html += "<th width='30%' class='px-3 fw-bold'>Ingredients</td>";
                            html += "<th width='30%' class='px-3 fw-bold py-2'>Quantity</td>";
                            html += "<th width='40%' class='px-3 fw-bold py-2'>Unit</td>";
                            html += "</tr>";

                            for (i in json['Ingredient']){
                                html += "<tr>";
                                html += "<td width='30%' class='px-3'>" + i + "</td>";
                                html += "<td width='30%' class='px-3 py-2'>" + json['Ingredient'][i]['qty'] + "</td>";
                                html += "<td width='40%' class='px-3 py-2'>" + json['Ingredient'][i]['unit'] + "</td>";
                                html += "</tr>";
                            }
                            html += "</table>";
                        }
                        
                    
                        $("#detailsModal .modal-body").html(html);
                        $("#detailsModal .modal-title").text("Food Item Details (#" + id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#detailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });
            }

            $(document).ready(function() {
                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                });

                $('.food-menu-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($food_items) == 0) { ?>
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