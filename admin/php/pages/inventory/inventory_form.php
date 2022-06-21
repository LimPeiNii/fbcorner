<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/inventory.php';
        include_once '../../helpers/category.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'inventory/inventory', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['inventory_id'])) {
                $inventory = getInventory($connection, $_GET['inventory_id']);
                $current_category_id = $inventory['category_id'];
            }

            $categories = getCategories($connection, 'inventory');
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
                <a href="inventory.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="inventory-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($inventory)) { ?>
                        Edit Inventory
                    <?php } else { ?>
                        Add Inventory
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($inventory)) { ?>
                        <form action="../../helpers/inventory.php?inventory_id=<?=$inventory['inventory_id']?>" class="m-5" id="inventory-form" method="POST">
                    <?php } else { ?>
                        <form action="../../helpers/inventory.php" class="m-5" id="inventory-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_form_inventory">
                            <label for="item-code" class="form-label fw-bold">Item Code</label>
                            <?php if (isset($inventory)) { ?>
                                <input type="text" class="form-control" id="item-code" value="<?=$inventory['item_code']?>" disabled>
                                <input type="hidden" class="form-control" id="item-code-hidden" value="<?=$inventory['item_code']?>" name="item-code">
                            <?php } else { ?>
                                <input type="text" class="form-control" id="item-code" disabled>
                                <input type="hidden" class="form-control" id="item-code-hidden" name="item-code">
                            <?php } ?>
                        </div>
                        <div class="mb-4">
                            <label for="item-name" class="form-label fw-bold">Item Name <span class="required-star">*</span></label>
                            <?php if (isset($inventory)) { ?>
                                <input type="text" class="form-control" id="item-name" value="<?=$inventory['item_name']?>" name="item-name">
                            <?php } else { ?>
                                <input type="text" class="form-control" id="item-name" name="item-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="category" class="form-label fw-bold">Category <span class="required-star">*</span></label>
                            <select class="form-select" id="category" name="category">
                                <option value="0">-- Please Select --</option>
                                <?php foreach ($categories as $category) { ?>
                                    <?php if (isset($inventory) && $inventory['category_id'] == $category['category_id']) { ?>
                                        <option value="<?=$category['category_id']?>" selected="selected"><?=$category['category_name']?></option>
                                    <?php } else { ?>
                                        <option value="<?=$category['category_id']?>"><?=$category['category_name']?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="unit" class="form-label fw-bold">Stock Unit <span class="required-star">*</span></label>
                            <?php if (isset($inventory)) { ?>
                                <input type="text" class="form-control" id="unit" value="<?=$inventory['unit']?>" name="unit">
                            <?php } else { ?>
                                <input type="text" class="form-control" id="unit" name="unit">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="qty1" class="form-label fw-bold">Unit Conversion <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<p class='text-start'>Convert unit to a smaller unit. System will update inventory automatically using the smaller unit. Main unit will be used if this section is left empty.<br><br>E.g. 1 packet = 10 pieces</p>"><i class="fas fa-question-circle"></i></span></label>
                            <table class="mt-1 w-100" id="unit_conversion" style="white-space: nowrap;">
                                <tbody class="align-top">
                                    <?php if (isset($inventory)) { ?>
                                        <tr>
                                            <td class="pe-1"><input placeholder="Quantity" type="text" class="form-control" name="unit_convert[qty1]" id="qty1" value="<?=$inventory['unit_convert_qty1']?>"><p class="d-block text-danger m-0"></p></td>
                                            <td class="text-center pt-2 px-1" id="unit_convert_unit1"><?=$inventory['unit']?><p class="d-block text-danger m-0"></p></td>
                                            <td class="text-center pt-2 px-1">=<p class="d-block text-danger m-0"></p></td>
                                            <td class="px-1"><input placeholder="Quantity" type="text" class="form-control" name="unit_convert[qty2]" id="qty2" value="<?=$inventory['unit_convert_qty2']?>"><p class="d-block text-danger m-0"></p></td>
                                            <td class="ps-1"><input placeholder="Smaller Unit" type="text" class="form-control" name="unit_convert[unit2]" id="unit2" value="<?=$inventory['unit_2']?>"><p class="d-block text-danger m-0"></p></td>
                                        </tr>
                                    <?php } else { ?>
                                        <tr>
                                            <td class="pe-1"><input placeholder="Quantity" type="text" class="form-control" name="unit_convert[qty1]" id="qty1"><p class="d-block text-danger m-0"></p></td>
                                            <td class="text-center pt-2 px-1" id="unit_convert_unit1"><p class="d-block text-danger m-0"></p></td>
                                            <td class="text-center pt-2 px-1">=<p class="d-block text-danger m-0"></p></td>
                                            <td class="px-1"><input placeholder="Quantity" type="text" class="form-control" name="unit_convert[qty2]" id="qty2"><p class="d-block text-danger m-0"></p></td>
                                            <td class="ps-1"><input placeholder="Smaller Unit" type="text" class="form-control" name="unit_convert[unit2]" id="unit2"><p class="d-block text-danger m-0"></p></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-4">
                            <label for="current-stock" class="form-label fw-bold">Current Stock <span class="required-star">*</span></label>
                            <table class="mt-1 w-100" style="white-space: nowrap;">
                                <tbody class="align-top">
                                <?php if (isset($inventory)) { ?>
                                    <td class="pe-1"><input type="number" class="form-control" id="current-stock" value="<?=$inventory['current_stock']?>" name="current-stock"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 main_unit"><?=$inventory['unit']?><p class="d-block text-danger"></p></td>
                                    <td class="px-1"><input type="number" class="form-control" id="current-stock-2" value="<?=$inventory['current_stock_2']?>" name="current-stock-2"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 sub_unit"><?=$inventory['unit_2']?><p class="d-block text-danger"></p></td>
                                <?php } else { ?>
                                    <td class="pe-1"><input type="number" class="form-control" id="current-stock" name="current-stock"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 main_unit"><p class="d-block text-danger"></p></td>
                                    <td class="px-1"><input type="number" class="form-control" id="current-stock-2" name="current-stock-2"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 sub_unit"><p class="d-block text-danger"></p></td>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-4">
                            <label for="reorder-level" class="form-label fw-bold">Reorder Level</label>
                            <table class="mt-1 align-middle w-100" style="white-space: nowrap;">
                                <tbody class="align-top">
                                <?php if (isset($inventory)) { ?>
                                    <td class="pe-1"><input type="number" class="form-control" id="reorder-level" value="<?=$inventory['reorder_level']?>" name="reorder-level"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 main_unit"><?=$inventory['unit']?><p class="d-block text-danger"></p></td>
                                    <td class="px-1"><input type="number" class="form-control" id="reorder-level-2" value="<?=$inventory['reorder_level_2']?>" name="reorder-level-2"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 sub_unit"><?=$inventory['unit_2']?><p class="d-block text-danger"></p></td>
                                <?php } else { ?>
                                    <td class="pe-1"><input type="number" class="form-control" id="reorder-level" name="reorder-level"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 main_unit"><p class="d-block text-danger"></p></td>
                                    <td class="px-1"><input type="number" class="form-control" id="reorder-level-2" name="reorder-level-2"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 sub_unit"><p class="d-block text-danger"></p></td>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-4">
                            <label for="total-stock" class="form-label fw-bold">Total Stock <span class="required-star">*</span></label>
                            <table class="mt-1 align-middle w-100" style="white-space: nowrap;">
                                <tbody class="align-top">
                                <?php if (isset($inventory)) { ?>
                                    <td class="pe-1"><input type="number" class="form-control" id="total-stock" value="<?=$inventory['total_stock']?>" name="total-stock"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 main_unit" width="8%"><?=$inventory['unit']?><p class="d-block text-danger"></p></td>
                                <?php } else { ?>
                                    <td class="pe-1"><input type="number" class="form-control" id="total-stock" name="total-stock"><p class="d-block text-danger"></p></td>
                                    <td class="text-center pt-2 px-1 main_unit" width="8%"><p class="d-block text-danger"></p></td>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($inventory) && $inventory['status'] == 0) { ?>
                                    <option value="1">Available</option>
                                    <option value="0" selected="selected">Unavailable</option>
                                <?php } else { ?>
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>                        
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                    </form>
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
                $('#inventory-form').removeClass('mt-4');
            }


            $(document).ready(function() {        
                $("#inventory-form").submit(function(event){
                    event.preventDefault();

                    $('#inventory-form').removeClass('mt-4');
                    $('.form-message').remove();
                    $('.text-danger').text('');
                    $('input select').removeClass('red-box-shadow is-invalid');
                    $.ajax({ 
                        url: '../../helpers/inventory.php',
                        data: {
                            item_name: $('#item-name').val(),
                            category: $('#category').val(),
                            current_stock: $('#current-stock').val(),
                            current_stock_2: $('#current-stock-2').val(),
                            reorder_level: $('#reorder-level').val(),
                            reorder_level_2: $('#reorder-level-2').val(),
                            total_stock: $('#total-stock').val(),
                            unit: $('#unit').val(),
                            unit_conversion: [$('#qty1').val(), $('#qty2').val(), $('#unit2').val()],
                            submit_check_inventory: $("button[type=submit]").val()
                        },
                        type: 'post',
                        success: function(output){
                            var json = $.parseJSON(output);
                            
                            if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#inventory-form');
                                $('#inventory-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('#spinner').removeClass('d-none');
                                $('#spinner').parent().prop('disabled', true);
                                $('#inventory-form').unbind().submit();
                            }
                        }
                    });
                });

                $('#category').change(function() {
                    if ($(this).val() == '0' || $(this).val() == 'others'){
                        $('#item-code').val('')
                        $('#item-code-hidden').val('')
                        return;
                    }

                    var to_continue = true;
                    <?php if (isset($current_category_id)) { ?>
                        if (<?=$current_category_id?> == $(this).val()){
                            to_continue = false;
                            $('#item-code').val('<?=$inventory['item_code']?>');
                            $('#item-code-hidden').val('<?=$inventory['item_code']?>');
                        }
                    <?php } ?>

                    if (to_continue){
                        $.ajax({
                            url: '../../helpers/inventory.php',
                            data: {category_id: $(this).val()},
                            method: 'post',
                            success: function(output){
                                $('#item-code').val(output);
                                $('#item-code-hidden').val(output);
                            }
                        });
                    }
                });

                $('#unit').on('input', function(){
                    $('#unit_convert_unit1').html($(this).val()+'<p class="d-block text-danger"></p>');
                    $('.main_unit').html($(this).val()+'<p class="d-block text-danger"></p>');
                });

                $('#unit2').on('input', function(){
                    $('.sub_unit').html($(this).val()+'<p class="d-block text-danger"></p>');
                });
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