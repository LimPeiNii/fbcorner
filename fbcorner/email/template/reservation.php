<?php

    include_once '../../../db_connect.php';
    include_once '../helpers/restaurant.php';
    include_once '../../../store/php/helpers/customer.php';

    $restaurant = getRestaurant($connection, $_SESSION['send_email']['rest_id']);
    $customer = getCustomer($connection, $_SESSION['send_email']['cus_id']);

    $reservation_data = json_decode($_SESSION['send_email']['reservation_data'], true);

    if (isset($_SESSION['send_email']['order_data'])){
        $order_data = json_decode($_SESSION['send_email']['order_data'], true);
        $the_order = $order_data['Order'];
    }
    
    if ($_SESSION['send_email']['type'] == 'add' && $_SESSION['send_email']['to'] == 'customer'){
        $message = "<p>Dear " . trim($reservation_data['Name']) . ",</p>
        <p>Thank you for your interest in " . $restaurant['rest_name'] . ". Your " . ($_SESSION['send_email']['reservation_or_preorder'] == 'reservation' ? 'reservation' : 'preorder for reservation ' . $_SESSION['send_email']['reservation_id'] ) . " has been received.</p>";
    } elseif ($_SESSION['send_email']['type'] == 'add' && $_SESSION['send_email']['to'] == 'restaurant'){
        $message = "<p>You have received " . ($_SESSION['send_email']['reservation_or_preorder'] == 'reservation' ? 'a reservation' : 'the preorder for reservation ' . $_SESSION['send_email']['reservation_id'] ) . ".</p>";
    } elseif ($_SESSION['send_email']['type'] == 'edit' && $_SESSION['send_email']['to'] == 'customer'){
        $message = "<p>Dear " . trim($reservation_data['Name']) . ",</p>
        <p>Thank you for your interest in " . $restaurant['rest_name'] . ". Your " . ($_SESSION['send_email']['reservation_or_preorder'] == 'reservation' ? 'reservation' : 'preorder for reservation ' . $_SESSION['send_email']['reservation_id'] ) . " has an update, as shown below.</p>";
    } elseif ($_SESSION['send_email']['type'] == 'edit' && $_SESSION['send_email']['to'] == 'restaurant'){
        $message = "<p>" . ($_SESSION['send_email']['reservation_or_preorder'] == 'reservation' ? 'A reservation' : 'The preorder for reservation ' . $_SESSION['send_email']['reservation_id'] ) . " has been updated, as shown below.</p>";
    }

?>

<html>
<head>
  <title><?=$restaurant['rest_name']?> &ndash; Reservation <?=$_SESSION['send_email']['reservation_id']?><?=$_SESSION['send_email']['type'] == 'edit' ? ' Update' : '' ?></title>
  <style>
        td{ 
            padding: 0.3rem 0.55rem;
            border-left: 2px solid #dee2e6; 
            border-right: 2px solid #dee2e6; 
        }

        .data-title{ 
            font-weight: bold; 
        }

        .reservation-details td, .preorder-details td{
            width: 50%
        }
  </style>
</head>
<body>
    <div style="margin: 1rem;">
        <?=$message?>
        <table style="margin-left: auto; margin-right: auto; width: 100%; border: 2px solid #dee2e6; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #e2e2e2;"><th colspan="2" style="padding: 0.75rem;" class="data-title"> Reservation Details</th></tr>
            </thead>
            <tbody class="reservation-details">
                <tr>
                    <td><span class="data-title">Reservation ID:</span> <?=$_SESSION['send_email']['reservation_id']?></td>
                    <td><span class="data-title">Name:</span> <?=trim($reservation_data['Name'])?></td>
                </tr>
                <tr>
                    <td><span class="data-title">Table Number (Name):</span> <?=$reservation_data['Table Number (Name)']?></td>
                    <td><span class="data-title">E-mail:</span> <a href="mailto:<?=$customer['email']?>"><?=$customer['email']?></a></td>
                </tr>
                <tr>
                    <td><span class="data-title">Date:</span> <?=$reservation_data['Date']?></td>
                    <td><span class="data-title">Contact Number:</span> <?=$customer['contact_num']?></td>
                </tr>
                <tr>
                    <td><span class="data-title">Time:</span> <?=$reservation_data['Time']?></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span class="data-title">Created Date:</span> <?=$reservation_data['Created Date']?></td>
                    <td></td>
                </tr>
                <tr>
                    <?php $reservation_status = substr($reservation_data['Status'], (strpos($reservation_data['Status'], '>') + 1));?>
                    <?php $reservation_status = substr($reservation_status, 0, (strpos($reservation_status, '</')));?>
                    <td><span class="data-title">Status:</span> <?=$reservation_status?></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span class="data-title">Special Request:</span> <?=$reservation_data['Special Request']?></td>
                    <td></td>
                </tr>
                <tr>
                    <td><span class="data-title">Remarks:</span> <?=$reservation_data['Remarks']?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <?php if (isset($_SESSION['send_email']['order_data'])){ ?>
        <table style="margin-left: auto; margin-right: auto; width: 100%; border: 2px solid #dee2e6; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #e2e2e2;"><th style="padding: 0.75rem;" class="data-title"> Preorder Details</th></tr>
            </thead>
            <tbody class="preorder-details">
                <tr>
                    <td><span class="data-title">Order ID:</span> <?=$_SESSION['send_email']['order_id']?></td>
                </tr>
                <tr>
                    <?php $preorder_status = substr($order_data['Status'], (strpos($order_data['Status'], '>') + 1));?>
                    <?php $preorder_status = substr($preorder_status, 0, (strpos($preorder_status, '</')));?>
                    <td><span class="data-title">Status:</span> <?=$preorder_status?></td>
                </tr>
                <tr>
                    <td><span class="data-title">Special Request:</span> <?=$order_data['Special Request']?></td>
                </tr>
                <tr>
                    <td><span class="data-title">Remarks:</span> <?=$order_data['Remarks']?></td>
                </tr>
            </tbody>
        </table>

        <table style="margin-left: auto; margin-right: auto; margin-top: 1.5rem; width: 100%; border: 2px solid #dee2e6; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #e2e2e2; text-align: left;">
                    <th width='30%' style="padding: 1rem;" class="data-title">Name</td>
                    <th width='20%' style="padding: 1rem;" class="data-title">Price</td>
                    <th width='20%' style="padding: 1rem;" class="data-title">Quantity</td>
                    <th width='30%' style="padding: 1rem;" class="data-title">Subtotal</td>
                </tr>
            </thead>

            <tbody>
                <?php $red_total = false; ?>
                <?php foreach ($the_order as $key => $value) { ?>
                <?php   if ($key !== 'Total' && $key !== 'promo_code' && $key !== 'voucher') { ?>
                <tr>
                    <?php   foreach ($value as $key2 => $value2) { ?>
                    <?php       if ($key2 !== 'food_id' && $key2 !== 'Promotions') { ?>
                    <?php           if (isset($value['Promotions']['discount_price'])) $red_total = true; ?>
                    <?php           $style = 'padding-right: 1rem !important; padding-left: 1rem !important; padding-top: .5rem !important; padding-bottom: .5rem!important;'; ?>
                    <?php           $style .= ($key2 === 'Subtotal' && isset($value['Promotions']['discount_price']) ? ' color: RGB(220,53,69);' : '')?>
                    <?php           $style .= ($key2 === 'Price' && isset($value['Promotions']['discount_price']) ? ' text-decoration: line-through !important; text-decoration-color: RGB(220, 53, 69) !important;' : '')?>
                    <td style="<?=$style?>">
                        <?=$value2?>
                        <?=($key2 === 'Name' && isset($value['Promotions']['buy_free']) ? '<br><span style="color: RGB(220,53,69);">' . $value['Promotions']['buy_free'] . '</span>' : '')?>
                        <?=($key2 === 'Quantity' && isset($value['Promotions']['buy_free_value']) && $value['Promotions']['buy_free_value'] !== 0 ? '&nbsp;<span style="color: RGB(220,53,69);">+ ' . (string)$value['Promotions']['buy_free_value'] . ' Free</span>' : '')?>
                        <?=($key2 === 'Price' && isset($value['Promotions']['discount_price']) ? '<br><span style="color: RGB(220,53,69); text-decoration: none !important; display: inline-block;">RM ' . number_format((float)$value['Promotions']['discount_price'], 2, '.', '') . '</span>' : '')?>
                    </td>
                    
                    <?php       } ?>
                    <?php   } ?>
                </tr>
                <?php   } else if ($key === 'Total') { ?>
                <tr style="border-top: 2px solid #dee2e6;">
                    <td colspan='3' style='padding-right: 1rem !important; padding-left: 1rem !important; border: 0px; font-weight: bold; text-align: right;'>Total</td>
                    <td style='padding-right: 1rem !important; padding-left: 1rem !important; border: 0px;<?=$red_total ? " color: RGB(220,53,69);" : "" ?>'><?=$value?></td>
                </tr>
                <?php   } else if ($key === 'promo_code'){ ?>
                <tr>
                    <td colspan='3' style='padding-right: 1rem !important; padding-left: 1rem !important; color: RGB(0, 196, 49); border: 0px; font-weight: bold; text-align: right;'>Promo Code (<?=$value['name']?>)</td>
                    <td style='padding-right: 1rem !important; padding-left: 1rem !important; border: 0px;'><?=$value['discount']?></td>
                </tr>
                <tr>
                    <td colspan='3' style='padding-right: 1rem !important; padding-left: 1rem !important; font-weight: bold; text-align: right; border: 0px;'>Grand Total</td>
                    <td style='padding-right: 1rem !important; padding-left: 1rem !important; border: 0px;'>RM <?=number_format(((float)substr($the_order['Total'], 3) - (float)substr($value['discount'], 5)), 2, '.', '')?></td>
                </tr>
                <?php   } else { ?>
                <?php       $grand_total_value = ((float)substr($the_order['Total'], 3) - (float)substr($value['price'], 5)); ?>
                <?php       if ($grand_total_value < 0) $grand_total_value = 0; ?>
                <tr>
                    <td colspan='3' style='padding-right: 1rem !important; padding-left: 1rem !important; color: RGB(0, 196, 49); border: 0px; font-weight: bold; text-align: right;'>Voucher</td>
                    <td style='padding-right: 1rem !important; padding-left: 1rem !important; border: 0px;'><?=$value['price']?></td>
                </tr>
                <tr>
                    <td colspan='3' style='padding-right: 1rem !important; padding-left: 1rem !important; font-weight: bold; text-align: right; border: 0px;'>Grand Total</td>
                    <td style='padding-right: 1rem !important; padding-left: 1rem !important; border: 0px;'>RM <?=number_format($grand_total_value, 2, '.', '')?></td>
                </tr>
                <?php   } ?>
                <?php } ?>
            </tbody>  
        </table>
        <?php } ?>

        <?php if (($_SESSION['send_email']['type'] == 'add' || $_SESSION['send_email']['type'] == 'edit') && $_SESSION['send_email']['to'] == 'customer'){?>
            <p>Please do not reply to this e-mail. If you have any questions, please contact the relevant restaurant.</p>
        <?php } ?>
    </div>
</body>
</html>