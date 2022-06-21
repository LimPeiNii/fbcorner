<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/promotion.php';
        include_once '../../helpers/food_menu.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'promotion/promotion', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['promotion_id'])) {
                $promotion = getPromotion($connection, $_GET['promotion_id']);
            }

            $food_items = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['order_by' => 'item_name', 'asc_desc' => 'ASC']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            table{
                width: 100%;
            }

            .overflow{
                border: 1px solid black; 
                border-radius: 5px;
                background-color: rgb(243, 243, 243);
                overflow: auto;
                height: 200px;
                max-height: 200px;
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
                <a href="promotion.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="promotion-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($promotion)) { ?>
                        Edit Promotion
                    <?php } else { ?>
                        Add Promotion
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($promotion)) { ?>
                        <form action="../../helpers/promotion.php?promotion_id=<?=$promotion['promotion_id']?>" class="m-5" id="promotion-form" method="POST">
                    <?php } else { ?> 
                        <form action="../../helpers/promotion.php" class="m-5" id="promotion-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_check_promotion">
                            <input type="text" style="display: none;" name="promo_id" value="<?=isset($promotion) ? $promotion['promotion_id'] : ''?>">
                            <label for="promo-type" class="form-label fw-bold">Promotion Type <span class="required-star">*</span></label>
                            <select class="form-select" id="promo-type" name="promo-type">
                                <?php
                                    $promo_types = array(
                                        'BOGO'        => 'Buy One Get One',
                                        'multi_buy'   => 'Multi-Buys',
                                        'percent_off' => 'Percent Off',
                                        'dollar_dis'  => 'Dollar Discount',
                                        'promo_code'  => 'Promotional Code',
                                    );
                                ?>
                                <option value="*">-- Please Select --</option>
                                <?php
                                foreach ($promo_types as $key => $promo_type){
                                    if (isset($promotion) && $promotion['type_code'] == $key) { 
                                ?>
                                    <option value="<?=$key?>" selected="selected"><?=$promo_type?></option>
                                <?php } else { ?>
                                    <option value="<?=$key?>"><?=$promo_type?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4" id="details-wrapper">
                            <label for="details" class="form-label fw-bold">Details <span class="required-star">*</span></label>
                        </div>
                        <div class="mb-4" id="food-items-wrapper">
                            <label class="form-label fw-bold">Food Items <span class="required-star">*</span></label>
                            <div class="p-3 overflow" id="food-items">
                                <?php foreach ($food_items as $food_item) { ?>
                                    <div class="mb-1">
                                        <input type="checkbox" id="food-item-<?=$food_item['item_id']?>" class="form-check-input me-2" value="<?=$food_item['item_id']?>" name="food_items[]">
                                        <label class="form-check-label" for="food-item-<?=$food_item['item_id']?>"><?=$food_item['item_name']?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <span style="color: blue; cursor: default;"><small><span class="select-all" style="cursor: pointer;">Select All</span> / <span class="unselect-all" style="cursor: pointer;">Unselect All</span></small></span>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4" id="food-item-wrapper">
                            <label class="form-label fw-bold">Food Item <span class="required-star">*</span></label>
                            <div class="p-3 overflow" id="food-item">
                                <?php foreach ($food_items as $food_item) { ?>
                                    <div class="mb-1">
                                        <input type="radio" id="food-item-radio-<?=$food_item['item_id']?>" class="form-check-input me-2" value="<?=$food_item['item_id']?>" name="food-item">
                                        <label class="form-check-label" for="food-item-radio-<?=$food_item['item_id']?>"><?=$food_item['item_name']?> (RM <?=number_format($food_item['price'], 2, '.', '')?>)</label>
                                    </div>
                                <?php } ?>
                            </div>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4" id="description-wrapper">
                            <label for="description" class="form-label fw-bold">Description <span class="required-star">*</span></label>
                            <?php if (isset($promotion)) { ?>
                                <textarea class="form-control" id="description" name="description" rows="6"><?=$promotion['description']?></textarea>
                            <?php } else { ?>
                                <textarea class="form-control" id="description" name="description" rows="6"></textarea>                    
                            <?php } ?>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($promotion)) { 
                                    if ($promotion['status'] == 0) { ?>
                                        <option value="1">Available</option>
                                        <option value="0" selected="selected">Unavailable</option>
                                <?php } else { ?>
                                        <option value="1" selected="selected">Available</option>
                                        <option value="0">Unavailable</option>                        
                                <?php } 
                                    } else { ?>
                                        <option value="1">Available</option>
                                        <option value="0">Unavailable</option> 
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-4" id="reset-redemption-wrapper">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="reset" id="reset-redemption" name="reset-redemption">
                                <label class="form-check-label" for="reset-redemption">
                                    Reset Redemption Chances
                                </label>
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
                $('#promotion-form').removeClass('mt-4');
            }

            $('#details-wrapper').hide();
            $('#food-items-wrapper').hide();
            $('#food-item-wrapper').hide();
            $('#description-wrapper').hide();
            $('#reset-redemption-wrapper').hide();

            $(document).ready(function() {
                $('.select-all').on('click', function () {
                    $(this).parent().parent().parent().find('div .form-check-input').prop('checked', true);
                });

                $('.unselect-all').on('click', function () {
                    $(this).parent().parent().parent().find('div .form-check-input').prop('checked', false);
                });

                $('textarea').each(function() {
                    var limit = 2000;
                    var current_char = $(this).val().length;
                    $(this).parent().find('.char-counter').text((limit - current_char).toString() + ' characters');                
                });

                $('textarea').on('input', function(){
                    var current_char = $(this).val().length;
                    var limit = 2000;
                    if (current_char > limit) {
                        $(this).val($(this).val().substring(0, limit));
                    }
                    var char_left = limit - current_char;
                    if (char_left < 0)
                        char_left = 0;
                    $(this).parent().find('.char-counter').text(char_left.toString() + ' characters');
                });

                $('#promo-type').change(function() {
                    $('#details-wrapper').hide();
                    $('#food-items-wrapper').hide();
                    $('#food-item-wrapper').hide();
                    $('#description-wrapper').hide();
                    $('#reset-redemption-wrapper').hide();
                    $('input[type=checkbox]').prop('checked', false);
                    $('#food-items, #food-item').removeClass('red-box-shadow is-invalid');
                    $('#food-items, #food-item').parent().find('.text-danger').text('');
                    $('#food-item').val('*');

                    if ($(this).val() == 'BOGO' || $(this).val() == 'multi_buy' || $(this).val() == 'percent_off'){
                        <?php if (isset($promotion)) { ?>
                            if ($(this).val() == '<?=$promotion['type_code']?>') { 
                                <?php if ($promotion['type_code'] != 'promo_code') { ?>
                                <?php foreach (json_decode($promotion['food_items'], true) as $food_item) { ?>
                                    $('#food-item-<?=$food_item?>').prop('checked', true);
                                <?php } ?>
                                <?php } ?>
                            }
                        <?php } ?>
                    } else {
                        <?php if (isset($promotion)) { ?>
                            if ($(this).val() == '<?=$promotion['type_code']?>') { 
                                <?php if ($promotion['type_code'] != 'promo_code') { ?>
                                <?php foreach (json_decode($promotion['food_items'], true) as $food_item) { ?>
                                    $('#food-item-radio-<?=$food_item?>').prop('checked', true);
                                <?php } ?>
                                <?php } ?>
                            }
                        <?php } ?>
                    }

                    <?php if (isset($promotion)) { ?>
                        if ($(this).val() == '<?=$promotion['type_code']?>' && $(this).val() == 'dollar_dis') { 
                            <?php if ($promotion['type_code'] == 'dollar_dis') { ?>
                            $('#food-item').val('<?=json_decode($promotion['food_items'], true)[0]?>');
                            <?php } ?>
                        }
                    <?php } ?>

                    if ($(this).val() == 'BOGO'){
                        $.ajax({
                            url: '../../helpers/promotion.php',
                            data: {
                                promotion_type: ['BOGO', 'multi_buy'],
                                get_unavailable_foods: true
                            },
                            method: 'post',
                            success: function(output) {
                                var json = $.parseJSON(output);
                                
                                for (i in json){
                                    if (!$('#food-item-' + json[i]).prop('checked')){
                                        $('#food-item-' + json[i]).prop('disabled', true);
                                        $('#food-item-' + json[i]).parent().css('opacity', '0.75');
                                    }
                                }
                            }
                        });

                        $('#food-items-wrapper').show();
                    } else if ($(this).val() == 'multi_buy'){
                        var html = '';

                        $('.promo-details').remove();

                        <?php if (isset($promotion)) { ?>
                            if ($(this).val() == '<?=$promotion['type_code']?>') { 
                                <?php $settings_data_multi_buy = json_decode($promotion['settings_data'], true) ?>
                            }
                        <?php } ?>

                        html += '<table class="mt-1 w-100 promo-details" style="white-space: nowrap;">';
                        html +=     '<tbody class="align-top">';
                        html +=         '<tr>';
                        html +=             '<td class="text-center pe-2 pt-3">Buy<p class="d-block text-danger"></p></td>';

                    <?php if (isset($settings_data_multi_buy)) { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="multi-buy-amount-1" name="multi-buy-amount-1" style="min-width: 90px;" placeholder="Amount" value="<?=(isset($settings_data_multi_buy['amount_1']) ? $settings_data_multi_buy['amount_1'] : '')?>"><label for="multi-buy-amount-1">Amount</label><p class="d-block text-danger"></p></div></td>';
                    <?php } else { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="multi-buy-amount-1" name="multi-buy-amount-1" style="min-width: 90px;" placeholder="Amount"><label for="multi-buy-amount-1">Amount</label><p class="d-block text-danger"></p></div></td>';
                    <?php } ?>

                        html +=             '<td class="text-center pt-3 px-2">Get<p class="d-block text-danger"></p></td>';

                    <?php if (isset($settings_data_multi_buy)) { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="multi-buy-amount-2" name="multi-buy-amount-2" style="min-width: 90px;" placeholder="Amount" value="<?=(isset($settings_data_multi_buy['amount_2']) ? $settings_data_multi_buy['amount_2'] : '')?>"><label for="multi-buy-amount-2">Amount</label><p class="d-block text-danger"></p></div></td>';
                    <?php } else { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="multi-buy-amount-2" name="multi-buy-amount-2" style="min-width: 90px;" placeholder="Amount"><label for="multi-buy-amount-2">Amount</label><p class="d-block text-danger"></p></div></td>';
                    <?php } ?>
                        
                        html +=         '</tr>';
                        html +=     '</tbody>';
                        html += '</table>';

                        $('#details-wrapper').append(html);

                        $.ajax({
                            url: '../../helpers/promotion.php',
                            data: {
                                promotion_type: ['BOGO','multi_buy'],
                                get_unavailable_foods: true
                            },
                            method: 'post',
                            success: function(output) {
                                var json = $.parseJSON(output);
                                
                                for (i in json){
                                    if (!$('#food-item-' + json[i]).prop('checked')){
                                        $('#food-item-' + json[i]).prop('disabled', true);
                                        $('#food-item-' + json[i]).parent().css('opacity', '0.75');
                                    }
                                }
                            }
                        });

                        $('#details-wrapper').show();
                        $('#food-items-wrapper').show();
                    } else if ($(this).val() == 'percent_off'){
                        var html = '';

                        $('.promo-details').remove();

                        <?php if (isset($promotion)) { ?>
                            if ($(this).val() == '<?=$promotion['type_code']?>') { 
                                <?php $settings_data_percent_off = json_decode($promotion['settings_data'], true) ?>
                            }
                        <?php } ?>

                        html += '<table class="mt-1 w-100 promo-details" style="white-space: nowrap;">';
                        html +=     '<tbody class="align-top">';
                        html +=         '<tr>';

                    <?php if (isset($settings_data_percent_off)) { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="percent-off-percent" name="percent-off-percent" placeholder="Discount Percentage" value="<?=(isset($settings_data_percent_off['percentage']) ? $settings_data_percent_off['percentage'] : '')?>"><label for="percent-off-percent">Discount Percentage</label><p class="d-block text-danger"></p></div></td>';
                    <?php } else { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="percent-off-percent" name="percent-off-percent" placeholder="Discount Percentage"><label for="percent-off-percent">Discount Percentage</label><p class="d-block text-danger"></p></div></td>';
                    <?php } ?>
                        
                        html +=             '<td width="3%" class="text-center pt-3 ps-2">%<p class="d-block text-danger"></p></td>';
                        html +=         '</tr>';
                        html +=     '</tbody>';
                        html += '</table>';

                        $('#details-wrapper').append(html);

                        $.ajax({
                            url: '../../helpers/promotion.php',
                            data: {
                                promotion_type: ['percent_off','dollar_dis'],
                                get_unavailable_foods: true
                            },
                            method: 'post',
                            success: function(output) {
                                var json = $.parseJSON(output);
                                
                                for (i in json){
                                    if (!$('#food-item-' + json[i]).prop('checked')){
                                        $('#food-item-' + json[i]).prop('disabled', true);
                                        $('#food-item-' + json[i]).parent().css('opacity', '0.75');
                                    }
                                }
                            }
                        });

                        $('#details-wrapper').show();
                        $('#food-items-wrapper').show();
                    } else if ($(this).val() == 'dollar_dis'){
                        var html = '';

                        $('.promo-details').remove();

                        <?php if (isset($promotion)) { ?>
                            if ($(this).val() == '<?=$promotion['type_code']?>') { 
                                <?php $settings_data_dollar_dis = json_decode($promotion['settings_data'], true) ?>
                            }
                        <?php } ?>

                        html += '<table class="mt-1 w-100 promo-details" style="white-space: nowrap;">';
                        html +=     '<tbody class="align-top">';
                        html +=         '<tr>';
                        html +=             '<td width="5%" class="text-center pt-3 pe-2">RM<p class="d-block text-danger"></p></td>';

                    <?php if (isset($settings_data_dollar_dis)) { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="dollar-dis-price" name="dollar-dis-price" placeholder="Discounted Price" value="<?=(isset($settings_data_dollar_dis['discounted_price']) ? number_format($settings_data_dollar_dis['discounted_price'], 2, '.', '') : '')?>"><label for="dollar-dis-price">Discounted Price</label><p class="d-block text-danger"></p></div></td>';
                    <?php } else { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="dollar-dis-price" name="dollar-dis-price" placeholder="Discounted Price"><label for="dollar-dis-price">Discounted Price</label><p class="d-block text-danger"></p></div></td>';
                    <?php } ?>
                        
                        html +=         '</tr>';
                        html +=     '</tbody>';
                        html += '</table>';

                        $('#details-wrapper').append(html);

                        $.ajax({
                            url: '../../helpers/promotion.php',
                            data: {
                                promotion_type: ['percent_off','dollar_dis'],
                                get_unavailable_foods: true
                            },
                            method: 'post',
                            success: function(output) {
                                var json = $.parseJSON(output);
                                
                                for (i in json){
                                    if (!$('#food-item-radio-' + json[i]).prop('checked')){
                                        $('#food-item-radio-' + json[i]).prop('disabled', true);
                                        $('#food-item-radio-' + json[i]).parent().css('opacity', '0.75');
                                    }
                                }
                            }
                        });

                        $('#details-wrapper').show();
                        $('#food-item-wrapper').show();
                    } else if ($(this).val() == 'promo_code'){
                        var html = '';

                        $('.promo-details').remove();

                        <?php if (isset($promotion)) { ?>
                            if ($(this).val() == '<?=$promotion['type_code']?>') { 
                                <?php $settings_data_promo_code = json_decode($promotion['settings_data'], true) ?>
                            }
                        <?php } ?>

                        html += '<span class="promo-details">';
                        html +=     '<div class="form-floating">';

                    <?php if (isset($settings_data_promo_code)) { ?>
                        html +=         '<input type="text" class="form-control" id="promo-code-name" placeholder="Promotional Code Name" value="<?=(isset($settings_data_promo_code['promo_code_name']) ? $settings_data_promo_code['promo_code_name'] : '')?>" disabled>';
                        html +=         '<input type="text" class="d-none" value="<?=(isset($settings_data_promo_code['promo_code_name']) ? $settings_data_promo_code['promo_code_name'] : '')?>" name="promo-code-name">'
                    <?php } else { ?>
                        html +=         '<input type="text" class="form-control" id="promo-code-name" name="promo-code-name" placeholder="Promotional Code Name">';
                    <?php } ?>

                        html +=         '<label for="promo-code-name" class="text-nowrap">Promotional Code Name</label>'
                        html +=         '<p class="d-block text-danger"></p>';
                        html +=     '</div>';
                        html += '</span>';

                        html += '<table class="mt-1 w-100 promo-details" style="white-space: nowrap;">';
                        html +=     '<tbody class="align-top">';
                        html +=         '<tr>';
                        html +=             '<td width="5%" class="text-center pt-3 pe-2">RM<p class="d-block text-danger"></p></td>';

                    <?php if (isset($settings_data_promo_code)) { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="promo-code-min-price" name="promo-code-min-price" placeholder="Minimum Spend" value="<?=(isset($settings_data_promo_code['min_price']) ? number_format($settings_data_promo_code['min_price'], 2, '.', '') : '')?>"><label for="promo-code-min-price">Minimum Spend</label><p class="d-block text-danger"></p></div></td>';
                    <?php } else { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="promo-code-min-price" name="promo-code-min-price" placeholder="Minimum Spend"><label for="promo-code-min-price">Minimum Spend</label><p class="d-block text-danger"></p></div></td>';
                    <?php } ?>
                        
                        html +=         '</tr>';
                        html +=     '</tbody>';
                        html += '</table>';

                        html += '<table class="mt-1 w-100 promo-details" style="white-space: nowrap;">';
                        html +=     '<tbody class="align-top">';
                        html +=         '<tr>';
                        html +=             '<td width="5%" class="text-center pt-3 pe-2">RM<p class="d-block text-danger"></p></td>';

                    <?php if (isset($settings_data_promo_code)) { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="promo-code-dis-price" name="promo-code-dis-price" placeholder="Actual Discount" value="<?=(isset($settings_data_promo_code['actual_discount']) ? number_format($settings_data_promo_code['actual_discount'], 2, '.', '') : '')?>"><label for="promo-code-dis-price">Actual Discount</label><p class="d-block text-danger"></p></div></td>';
                    <?php } else { ?>
                        html +=             '<td><div class="form-floating"><input type="number" class="form-control" id="promo-code-dis-price" name="promo-code-dis-price" placeholder="Actual Discount"><label for="promo-code-dis-price">Actual Discount</label><p class="d-block text-danger"></p></div></td>';
                    <?php } ?>
                        
                        html +=         '</tr>';
                        html +=     '</tbody>';
                        html += '</table>';

                        html += '<span class="promo-details">';
                        html +=     '<div class="form-floating">';

                    <?php if (isset($settings_data_promo_code)) { ?>
                        html +=         '<input type="number" class="form-control" id="promo-code-max-redemption" name="promo-code-max-redemption" placeholder="Redemption Limit" value="<?=(isset($settings_data_promo_code['max_redemption']) ? $settings_data_promo_code['max_redemption'] : '')?>">';
                    <?php } else { ?>
                        html +=         '<input type="number" class="form-control" id="promo-code-max-redemption" name="promo-code-max-redemption" placeholder="Redemption Limit">';
                    <?php } ?>

                        html +=         '<label for="promo-code-max-redemption">Redemption Limit</label>'
                        html +=         '<p class="d-block text-danger"></p>';
                        html +=     '</div>';
                        html += '</span>';

                        $('#details-wrapper').append(html);

                        $('#details-wrapper').show();
                        $('#description-wrapper').show();
                        $('#reset-redemption-wrapper').show();
                    }
                });

                $(document).on('input', '#promo-code-name', function(){
                    $('#promo-code-name').val($('#promo-code-name').val().toUpperCase());
                });
                
                <?php if (isset($promotion)) { ?>
                    $('#promo-type').trigger('change');
                <?php } ?>

                $("#promotion-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('input, select, div, textarea').removeClass('is-invalid red-box-shadow');
                    $('.text-danger').text('');

                    $.ajax({
                        url: '../../helpers/promotion.php',
                        data: $('#promotion-form select, #promotion-form input:not([type=checkbox], [type=radio]), #promotion-form input[type=checkbox]:checked, #promotion-form input[type=radio]:checked, textarea'),
                        method: 'post',
                        success: function(output) {
                            var json = $.parseJSON(output);

                            if (json['warning']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + json['warning'] + '<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#promotion-form');
                                $('#promotion-form').addClass('mt-4');
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#promotion-form');
                                $('#promotion-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('is-invalid red-box-shadow');
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('input[name="submit_check_promotion"]').attr('name', "submit_form_promotion");
                                $('#promotion-form').unbind().submit();
                            }
                        }
                    });
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