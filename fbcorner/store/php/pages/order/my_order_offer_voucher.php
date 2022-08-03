<?php

    session_start();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/voucher.php';
    include_once '../../../../admin/php/helpers/promotion.php';
    
    $promo_codes = getPromotions($connection, $_POST['rest_id'], ['type_code' => 'promo_code', 'status' => '1']);
    $total = $_POST['total'];
    if (isset($_POST['voucher_id'])){
        $vouchers = getVouchers($connection, $_SESSION['login_cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_other_in_use' => ['this_voucher_id' => $_POST['voucher_id']]]);
    } else {
        $vouchers = getVouchers($connection, $_SESSION['login_cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_in_use' => true]);
    }
    
?>

<?php if ($_POST['status'] == 'Upcoming') { ?>
<label for="search-vouchers" class="form-label fw-bold">Vouchers</label>
<?php } else { ?>
<label for="search-offers" class="form-label fw-bold">Offers and Vouchers</label>
<?php } ?>
<div id="offer-voucher">
<nav>
<div class="nav nav-tabs<?=$_POST['status'] == 'Upcoming' ? '' : ' flex-column flex-sm-row'?>" id="nav-tab-offer-voucher" role="tablist">
    <button class="nav-link flex-sm-fill text-sm-center<?=$_POST['status'] == 'Upcoming' ? ' d-none' : (isset($_POST['promo_id']) || (!isset($_POST['promo_id']) && !isset($_POST['voucher_id'])) ? ' active' : '')?>" id="nav-offer-tab" data-bs-toggle="tab" data-bs-target="#nav-offer" type="button" role="tab" aria-controls="nav-offer" <?=$_POST['status'] == 'Upcoming' ? 'aria-selected="false"' : (isset($_POST['promo_id']) || (!isset($_POST['promo_id']) && !isset($_POST['voucher_id'])) ? 'aria-selected="true"' : 'aria-selected="false"')?>>Offers</button>
    <button class="nav-link text-sm-center<?=$_POST['status'] == 'Upcoming' ? ' active' : (isset($_POST['voucher_id']) ? ' active flex-sm-fill' : ' flex-sm-fill')?>" <?=$_POST['status'] == 'Upcoming' ? 'style="background-color: unset; color: unset; min-width: 50%;"' : ''?> id="nav-voucher-tab" data-bs-toggle="tab" data-bs-target="#nav-voucher" type="button" role="tab" aria-controls="nav-voucher" <?=$_POST['status'] == 'Upcoming' ? 'aria-selected="true"' : (isset($_POST['voucher_id']) ? 'aria-selected="true"' : 'aria-selected="false"')?>>Vouchers</button>
</div>
</nav>
<div class="tab-content bg-light" id="nav-tabContent">
    <div class="tab-pane fade p-3<?=$_POST['status'] == 'Upcoming' ? ' d-none' : (isset($_POST['promo_id']) || (!isset($_POST['promo_id']) && !isset($_POST['voucher_id'])) ? ' active show' : '')?>" id="nav-offer" role="tabpanel" aria-labelledby="nav-offer-tab">
        <nav class="navbar navbar-light">
            <div class="container-fluid">
                <div class="d-flex w-100">
                <input class="form-control" name="" id="search-offers" type="search" placeholder="Search" aria-label="Search">
                </div>
            </div>
        </nav>
        <?php if ($promo_codes) { ?>
            <div class="list-group" style="max-height: 269.4px; min-height: 269.4px; margin: 0.8rem; <?=count($promo_codes) <= 3 ? 'overflow:hidden; ' : 'overflow: auto; '?>; border: 1px solid black; background-color: white;">
            <?php foreach ($promo_codes as $promo_key => $promo_code) { ?>
                <?php $details = json_decode($promo_code['settings_data'], true); ?>
                <div style="<?=$promo_key == count($promo_codes)-1 && count($promo_codes) >= 3 ? 'border-bottom: none; ' : 'border-bottom: 1px solid grey; border-bottom-left-radius: unset; border-bottom-right-radius: unset; '?> cursor: pointer;" class="list-group-item list-group-item-action <?=isset($_POST['promo_id']) && $_POST['promo_id'] == $promo_code['promotion_id'] ? ' bg-primary bg-opacity-25' : ''?>" data-bs-target="#offerModal" data-bs-toggle="modal" onclick="showOfferDescription('<?=$promo_code['promotion_id']?>', $(this));" aria-current="true">
                    <div class="d-flex flex-wrap w-100 p-2">
                        <input type="number" class="d-none" value="<?=(float)$details['min_price']?>">
                        <?php if (isset($_POST['promo_id']) && $_POST['promo_id'] == $promo_code['promotion_id']) { ?>
                        <i class="fas fa-check-circle offer-icon mx-1 me-3 align-self-center text-success" style="font-size: 25px;"></i>
                        <?php } elseif ($total < (float)$details['min_price']) { ?>
                        <i class="fas fa-lock offer-icon mx-1 me-3 align-self-center text-danger" style="font-size: 25px;"></i>
                        <?php } else { ?>
                        <i class="fas fa-lock-open offer-icon mx-1 me-3 align-self-center text-success" style="font-size: 25px;"></i>
                        <?php } ?>
                        <div class="flex-grow-1">
                        <h4 class="mb-1 offer-name"><strong><?=$details['promo_code_name']?></strong></h4>
                        <small>Minimum spend RM <?=$details['min_price']?></small>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        <?php } else { ?>
            <div wfd-invisible="true" style="height: 296px; border: 1px solid black; margin: 0.8rem; border-radius: 0.25rem; background-color: white;">
                <table class="m-0 w-100">
                    <tbody>
                        <tr class="w-100">
                            <td class="text-center align-middle" style="height: 294px;">No available offers</td>  
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
    <div class="tab-pane fade p-3<?=$_POST['status'] == 'Upcoming' ? ' active show' : (isset($_POST['voucher_id']) ? ' active show' : '')?>" id="nav-voucher" role="tabpanel" aria-labelledby="nav-voucher-tab">
        <?php if (count($vouchers) == 0) { ?>
            <div style="margin: 0.8rem; border-radius: 0.25rem; height: 296px;" id="voucher">
                <table class="m-0 w-100">
                <tr class="w-100">
                    <td class="text-center align-middle" style="height: 230px;">No available voucher</td>  
                </tr>
                </table>
            </div>
        <?php } else { ?>
            <div class="list-group" style="max-height: 269.4px; min-height: 269.4px; margin: 0.8rem; <?=count($vouchers) <= 3 ? 'overflow:hidden; ' : 'overflow: auto; '?> border: 1px solid black; background-color: white;">
            <?php foreach ($vouchers as $voucher_key => $voucher) { ?>
                <div style="<?=$voucher_key == count($vouchers)-1 && count($vouchers) >= 3 ? 'border-bottom: none; ' : 'border-bottom: 1px solid grey; border-bottom-left-radius: unset; border-bottom-right-radius: unset; '?> cursor: pointer;" class="list-group-item list-group-item-action<?=isset($_POST['voucher_id']) && $_POST['voucher_id'] == $voucher['the_voucher_id'] ? ' bg-primary bg-opacity-25' : ''?>" onclick="applyRemoveVoucher(<?=$voucher['equal_price']?>, <?=$voucher['the_voucher_id']?>, $(this))">
                    <div class="d-flex flex-wrap w-100 p-1">
                        <?php if (isset($_POST['voucher_id']) && $_POST['voucher_id'] == $voucher['the_voucher_id']) { ?>
                        <i class="fas fa-check-circle voucher-icon mx-1 me-4 align-self-center text-success" style="font-size: 30px;"></i>
                        <?php } else { ?>
                        <i class="bi bi-ticket-perforated-fill voucher-icon mx-1 me-4 align-self-center text-success" style="font-size: 30px;"></i>
                        <?php } ?>
                        <div class="flex-grow-1 pt-1">
                        <input type="number" class="d-none" value="<?=$voucher['the_voucher_id']?>">
                        <h4 class="mb-1 voucher-name"><strong><?=$voucher['name']?></strong></h4>
                        <small>Valid Till <?=date('d/m/Y', strtotime($voucher['expired_date']))?></small>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
</div>