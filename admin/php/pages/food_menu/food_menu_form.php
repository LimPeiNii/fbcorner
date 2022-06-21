<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        
        include_once '../../../../db_connect.php';
        include_once '../../helpers/food_menu.php';
        include_once '../../helpers/category.php';
        include_once '../../helpers/inventory.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'food_menu/food_menu', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            if (isset($_GET['item_id'])) {
                $food_menu_item = getFoodMenuItem($connection, $_GET['item_id']);
                $current_category_id = $food_menu_item['category_id'];
            }

            $categories = getCategories($connection, 'food_menu');
            $ingredients = getInventories($connection, $_SESSION['login_rest_id']);
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
            table{
                width: 100%;
            }

            thead, tbody, tfoot{
                border: 1px solid #ccc !important;
                border-width: 2px !important;
            }

            tbody tr{
                border-color: #dee2e6 !important;
            }

            #category_others, #category_others tbody, #category_others tbody tr{
                border-color: transparent !important;
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
                <a href="food_menu.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="food-menu-item-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($food_menu_item)) { ?>
                        Edit Food Menu Item
                    <?php } else { ?>
                        Add Food Menu Item
                    <?php } ?>
                </h3>

                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($food_menu_item)) { ?>
                        <form action="../../helpers/food_menu.php?item_id=<?=$food_menu_item['item_id']?>" class="m-5" id="food-menu-item-form" method="POST" enctype="multipart/form-data">
                    <?php } else { ?>    
                        <form action="../../helpers/food_menu.php" class="m-5" id="food-menu-item-form" method="POST" enctype="multipart/form-data">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_check_food_menu">
                            <label for="item-code" class="form-label fw-bold">Item Code</label>
                            <?php if (isset($food_menu_item)) { ?>
                                <input type="text" style="display: none;" name="food_menu_item_id" value="<?=$food_menu_item['item_id']?>">
                                <input type="text" class="form-control" id="item-code" value="<?=$food_menu_item['item_code']?>" disabled>
                                <input type="hidden" class="form-control" id="item-code-hidden" value="<?=$food_menu_item['item_code']?>" name="item-code">
                            <?php } else { ?>
                                <input type="text" style="display: none;" name="food_menu_item_id">
                                <input type="text" class="form-control" id="item-code" disabled>
                                <input type="hidden" class="form-control" id="item-code-hidden" name="item-code">
                            <?php } ?>
                        </div>
                        <div class="mb-4">
                            <label for="item-name" class="form-label fw-bold">Item Name <span class="required-star">*</span></label>
                            <?php if (isset($food_menu_item)) { ?>
                                <input type="text" class="form-control" id="item-name" value="<?=$food_menu_item['item_name']?>" name="item-name">
                            <?php } else { ?>
                                <input type="text" class="form-control" id="item-name" name="item-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="category" class="form-label fw-bold">Category <span class="required-star">*</span></label>
                            <select class="form-select" id="category" name="category[category]">
                                <option value="0">-- Please Select --</option>
                                <?php foreach ($categories as $category) { ?>
                                    <?php if (isset($food_menu_item) && $food_menu_item['category_id'] == $category['category_id']) { ?>
                                        <option value="<?=$category['category_id']?>" selected="selected"><?=$category['category_name']?></option>
                                    <?php } else { ?>
                                        <option value="<?=$category['category_id']?>"><?=$category['category_name']?></option>
                                    <?php } ?>
                                <?php } ?>
                                <option value="others">Others</option>
                            </select>
                            <table id="category_others" class="d-none w-100">
                                <tr>
                                    <td width="60%" style="padding-right: 8px;" class="align-top"><input type="text" id="category_others_name" class="form-control mt-2" name="category[category_name]" placeholder="Category Name (E.g. western)"><p class="d-block text-danger"></p></td>
                                    <td width="40%" style="padding-left: 8px;" class="align-top"><input type="text" id="category_others_code" class="form-control mt-2" placeholder="Category Code (E.g. W)" name="category[category_code]"><p class="d-block text-danger"></p></td>
                                </tr>
                            </table>
                        </div>
                        <div class="mb-4">
                            <div>
                                <label class="form-label fw-bold" for="image">Image <span class="required-star">*</span></label>
                                <?php if (isset($food_menu_item)) { ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="img-upload-btn"><small>Click to replace</small></button>
                                <?php } else { ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="img-upload-btn"><small>Click to add</small></button>
                                <?php } ?>
                                <input type="file" id="image" class="d-none picture-upload" name="image">
                            </div>
                            <div class="mt-2" id="img-content" style="min-height: 24px;">
                                <?php if (isset($food_menu_item)) { ?>
                                    <img style="min-width: 200px;max-width: 280px;" height="200" src="../../../uploads/food_menu_photo/<?=$food_menu_item['image']?>">
                                <?php } ?>
                            </div>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Description <span class="required-star">*</span></label>
                            <?php if (isset($food_menu_item)) { ?>
                                <textarea class="form-control" id="description" name="description" rows="6"><?=$food_menu_item['description']?></textarea>
                            <?php } else { ?>
                                <textarea class="form-control" id="description" name="description" rows="6"></textarea>                    
                            <?php } ?>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="price" class="form-label fw-bold">Price (RM) <span class="required-star">*</span></label>
                            <?php if (isset($food_menu_item)) { ?>
                                <input type="text" class="form-control" id="price" value="<?=number_format($food_menu_item['price'], 2, '.', '')?>" name="price">
                            <?php } else { ?>
                                <input type="text" class="form-control" id="price" name="price">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($food_menu_item) && $food_menu_item['status'] == 0) { ?>
                                    <option value="1">Available</option>
                                    <option value="0" selected="selected">Unavailable</option>
                                <?php } else { ?>
                                    <option value="1">Available</option>
                                    <option value="0">Unavailable</option>                        
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Ingredients</label>
                            <div class="table-responsive">
                                <table class="table table-striped mt-1 align-middle" id="ingredient-table" style="white-space: nowrap;">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="min-width: 90px;" class="text-center">No.</th>
                                            <th scope="col" style="min-width: 301px; width: 401px;">Item <span class="required-star">*</span></th>
                                            <th scope="col" style="min-width: 201px; width: 401px;">Quantity <span class="required-star">*</span></th>
                                            <th scope="col" style="min-width: 114px;">Unit</th>
                                            <th scope="col" style="width: 63px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $ingredient_row_counter = 1; 
                                            if (isset($food_menu_item) && !empty($food_menu_item['ingredients'])) {
                                                $ingredient_array = json_decode($food_menu_item['ingredients'], true);
                                                foreach ($ingredient_array as $item_id => $qty) {
                                        ?>
                                            <tr>
                                                <th scope="row" style="min-width: 90px;" class="text-center"><?=$ingredient_row_counter?></th>
                                                <td style="min-width: 301px; width: 401px;">
                                                    <select class="form-select ingredient-name" id="ingredient_item_<?=$ingredient_row_counter?>" name="ingredient[<?=$ingredient_row_counter?>][inventory_id]">
                                                        <option value="0">-- Please Select --</option>
                                                        <?php foreach ($ingredients as $ingredient) { ?>
                                                            <?php if ($ingredient['inventory_id'] == $item_id) { ?>
                                                                <option value="<?=$ingredient['inventory_id']?>" selected="selected"><?=$ingredient['item_name']?></option>
                                                                <?=$ingredient_unit = $ingredient['unit_2']; ?>
                                                            <?php } else { ?>
                                                                <option value="<?=$ingredient['inventory_id']?>"><?=$ingredient['item_name']?></option>                       
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                    <p class="d-block text-danger m-0"></p>
                                                </td>
                                                <td style="min-width: 201px; width: 401px;"><input type="text" class="form-control" name="ingredient[<?=$ingredient_row_counter?>][quantity]" id="ingredient_qty_<?=$ingredient_row_counter?>" value="<?=$qty?>"><p class="d-block text-danger m-0"></p></td>
                                                <td style="min-width: 114px;" class="ingredient-unit"><?=$ingredient_unit?></td>
                                                <td style="width: 63px;"><button type="button" class="btn bg-danger text-white remove-ingredient-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove"><i class="fas fa-minus-circle font-size-20" style="line-height: 30px;"></i></button></td>
                                            </tr>
                                            <?php $ingredient_row_counter++; ?>
                                            <?php } ?>
                                        <?php } ?>                            
                                    </tbody>
                                    <tfoot>
                                        <td colspan="4"></td>
                                        <td><button type="button" id="add-ingredient-btn" class="btn bg-info text-white" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus-circle font-size-20" style="line-height: 30px;"></i></button></td>
                                    </tfoot>
                                </table>
                            </div>
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
                $('#food-menu-item-form').removeClass('mt-4');
            }

            $(document).ready(function() {
                $("#food-menu-item-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('input, select, textarea').removeClass('red-box-shadow is-invalid');
                    $('.text-danger').text('');

                    $.ajax({
                        url: '../../helpers/food_menu.php',
                        data: $('#food-menu-item-form select, #food-menu-item-form input, #food-menu-item-form textarea'),
                        method: 'post',
                        success: function(output) {
                            var json = $.parseJSON(output);
                            
                            if (json['enable_error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + json['enable_error'] + '<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#food-menu-item-form');
                                $('#food-menu-item-form').addClass('mt-4');
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#food-menu-item-form');
                                $('#food-menu-item-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    if (key == 'image')
                                        $('#' + key).parent().parent().find('p').text(value);
                                    else
                                        $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('#spinner').removeClass('d-none');
                                $('#spinner').parent().prop('disabled', true);
                                $('input[name="submit_check_food_menu"]').attr('name', "submit_form_food_menu");
                                $('#food-menu-item-form').unbind().submit();
                            }
                        }
                    });
                });

                $('textarea').each(function() {
                    var limit = 1000;
                    var current_char = $(this).val().length;
                    $(this).parent().find('.char-counter').text((limit - current_char).toString() + ' characters');                
                });

                $('textarea').on('input', function(){
                    var current_char = $(this).val().length;
                    var limit = 1000;
                    if (current_char > limit) {
                        $(this).val($(this).val().substring(0, limit));
                    }
                    var char_left = limit - current_char;
                    if (char_left < 0)
                        char_left = 0;
                    $(this).parent().find('.char-counter').text(char_left.toString() + ' characters');
                    
                });

                $('#category').change(function() {          
                    if ($(this).val() == 'others'){
                        $('#category_others').removeClass('d-none');
                    } else{
                        $('#category_others').addClass('d-none');
                    }

                    if ($(this).val() == '0' || $(this).val() == 'others'){
                        $('#item-code').val('');
                        $('#item-code-hidden').val('');
                        return;
                    }

                    var to_continue = true;
                    <?php if (isset($current_category_id)) { ?>
                        if (<?=$current_category_id?> == $(this).val()){
                            to_continue = false;
                            $('#item-code').val('<?=$food_menu_item['item_code']?>');
                            $('#item-code-hidden').val('<?=$food_menu_item['item_code']?>');
                        }
                    <?php } ?>

                    if (to_continue){
                        $.ajax({
                            url: '../../helpers/food_menu.php',
                            data: {category_id: $(this).val()},
                            method: 'post',
                            success: function(output){
                                $('#item-code').val(output);
                                $('#item-code-hidden').val(output);
                            }
                        });
                    }
                });

                $('#img-upload-btn').click(function() {
                    $('#image').trigger('click');
                });

                $('#image').change(function() {
                    $('.form-message').remove();
                    $('#food-menu-item-form').removeClass('mt-4');

                    var upload_img = this.files[0];
                    var img_name = upload_img.name
                    var allowed_ext = ["jpg", "jpeg", "png"]; 

                    if ($.inArray(img_name.substring(img_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;You can only select files with type "jpg", "jpeg", "png"!</div>').insertBefore('#food-menu-item-form');
                        $(this).val('');
                        $('#food-menu-item-form').addClass('mt-4');
                        $('html, body').animate({ scrollTop: 0 }, 0);
                    } else if (upload_img){
                        $('#img-content img').remove();
                        let reader = new FileReader();
                        reader.onload = function(event){
                            $('#img-content').append('<img style="min-width: 200px;max-width: 280px;" height="200" src="' + event.target.result + '">')
                        }
                        reader.readAsDataURL(upload_img);
                    }
                });

                var ingredient_row_counter = <?=$ingredient_row_counter?>;
                $('#add-ingredient-btn').click(function () {
                    var html;

                    html += '<tr>';
                    html +=     '<th scope="row" style="min-width: 90px;" class="text-center">' + ingredient_row_counter + '</th>';
                    html +=     '<td style="min-width: 301px; width: 401px;">';
                    html +=         '<select class="form-select ingredient-name" id="ingredient_item_' + ingredient_row_counter + '" name="ingredient[' + ingredient_row_counter + '][inventory_id]">';
                    html +=             '<option value="0">-- Please Select --</option>';
                    
                <?php foreach ($ingredients as $ingredient) { ?>
                    html +=             '<option value="<?=$ingredient['inventory_id']?>"><?=$ingredient['item_name']?></option>';
                <?php } ?>

                    html +=        '</select>';
                    html +=        '<p class="d-block text-danger m-0"></p>';
                    html +=     '</td>';
                    html +=     '<td style="min-width: 201px; width: 401px;"><input type="text" class="form-control" name="ingredient[' + ingredient_row_counter + '][quantity]" id="ingredient_qty_' + ingredient_row_counter + '"><p class="d-block text-danger m-0"></p></td>';
                    html +=     '<td style="min-width: 114px;" class="ingredient_unit ingredient-unit"></td>';
                    html +=     '<td style="width: 63px;"><button type="button" class="btn bg-danger text-white remove-ingredient-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove"><i class="fas fa-minus-circle font-size-20" style="line-height: 30px;"></i></button></td>';
                    html += '</tr>';

                    $('#ingredient-table tbody').append(html);
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    $('[data-bs-toggle="tooltip"]').on('click', function () {
                        $(this).tooltip('hide');
                    })

                    ingredient_row_counter++;
                });

                $(document).on('click', '.remove-ingredient-btn', function () {
                    $(this).parentsUntil('tr').parent().remove();

                    if ($('#ingredient-table tbody').children().length == 0){
                        ingredient_row_counter = 1;
                    } else {
                        ingredient_row_counter = parseInt($('#ingredient-table tbody tr:last-child th').text()) + 1;
                    }
                });

                $(document).on('change', '.ingredient-name', function () {
                    if ($(this).val() != '0'){
                        var current_element = $(this);
                        $.ajax({
                            url: '../../helpers/food_menu.php',
                            data: {an_inventory_id: $(this).val()},
                            method: 'post',
                            success: function(output){
                                current_element.parentsUntil('tr').parent().find('.ingredient-unit').text(output);
                            }
                        });
                    } else {
                        $(this).parentsUntil('tr').parent().find('.ingredient-unit').text('');
                    }
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