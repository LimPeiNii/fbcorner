<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/inventory.php';
        include_once '../../helpers/category.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'inventory/inventory', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'inventory/inventory', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            $inventories = getInventories($connection, $_SESSION['login_rest_id']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory &VerticalLine; F&amp;B Corner</title>

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

            .inventory-list{
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
                <a href="inventory_form.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <button type="button" class="btn btn-info float-end" id="reset-all" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Stock"><i class="fas fa-sync-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Inventory
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive inventory-list">
                    <?php if (count($inventories) == 0 || count($inventories) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4 text-center">No.</td>
                                <th width="11%" class="px-3">Item Code</td>
                                <th width="20%" class="px-3">Item Name</td>
                                <th width="20%" class="px-3">Category</td>
                                <th width="8%" class="px-3 text-center">Stock</td>
                                <th width="8%" class="px-3 text-center">Reorder</td>
                                <th width="12%" class="px-3 text-center">Status</td>
                                <th width="10%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $counter = 0; ?>
                            <?php foreach ($inventories as $inventory) { ?>
                            <?php $counter++; ?>
                                <tr>
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$inventory['inventory_id']?>"></td>
                                    <td width="5%" class="align-middle px-3 text-center"><?=$counter?></td>
                                    <td width="11%" class="align-middle px-3"><?=$inventory['item_code']?></td>
                                    <td width="20%" class="align-middle px-3"><?=$inventory['item_name']?></td>
                                    <td width="20%" class="align-middle px-3"><?=getCategory($connection, $inventory['category_id'])['category_name']?></td>
                                    <?php 
                                        if ($inventory['current_stock_2'] == '0'){
                                            $current_stock_string = $inventory['current_stock'] . ' ' . $inventory['unit'];
                                        } else {
                                            $current_stock_string = $inventory['current_stock'] . ' ' . $inventory['unit'] . ' ' . $inventory['current_stock_2'] . ' ' . $inventory['unit_2'];
                                        }
                                    ?>
                                    <td width="8%" class="align-middle px-3 text-center"><span style="color: blue; cursor: pointer;" class="d-inline-block" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-html="true" data-bs-content="Current Stock: <?=$current_stock_string?><br>Total Stock: <?=$inventory['total_stock'] . ' ' . $inventory['unit']?><?=$inventory['unit_convert_qty1'] == $inventory['unit_convert_qty2'] && $inventory['unit'] == $inventory['unit_2'] ? '' : '<br>' . $inventory['unit_convert_qty1'] . ' ' . $inventory['unit'] . ' = ' . $inventory['unit_convert_qty2'] . ' ' . $inventory['unit_2']?>">View</span></td>
                                    <?php if ($inventory['current_stock'] > $inventory['reorder_level'] || ($inventory['current_stock'] == $inventory['reorder_level'] && $inventory['current_stock_2'] > $inventory['reorder_level_2'])) { ?>
                                    <td width="8%" class="align-middle px-3 text-center"></td>
                                    <?php } else { ?>
                                    <td width="10%" class="align-middle px-3 text-center"><i class="fas fa-exclamation-circle font-size-25 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Stock is below re-order level!"></i></td>
                                    <?php } ?>
                                    <?php if ($inventory['status'] == '1') { ?>
                                        <td width="12%" class="align-middle px-3 text-center">Available</td>
                                    <?php } else { ?>
                                        <td width="12%" class="align-middle px-3 text-center">Unavailable</td>
                                    <?php } ?>
                                    <td width="10%" class="text-center">
                                        <a href="inventory_form.php?inventory_id=<?=$inventory['inventory_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
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

                $('#reset-all').click(function () {
                    var all_item_id = [];
                    $('.data-checkbox:checked').each(function() {
                        all_item_id.push($(this).val());
                    });

                    if (all_item_id.length > 0){
                        <?php if ((int)$has_modify_permission['has_permission']){ ?>
                            if (confirm("Are you sure you want to reset the stock for the item(s)?")){
                                $.ajax({ 
                                    url: '../../helpers/inventory.php',
                                    data: {all_item_ids: all_item_id},
                                    type: 'post',
                                    success: function(){
                                        window.location.reload();
                                    }
                                });
                            }
                        <?php } else { ?>
                            close_message();
                            $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to reset the inventory stock(s).</div>').insertBefore('.inventory-list');
                        <?php } ?>
                    }
                });

                $('.inventory-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($inventories) == 0) { ?>
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