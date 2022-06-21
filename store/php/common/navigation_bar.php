<?php 
    if (isset($_SESSION['login_cus_id'])){
        $sql = "SELECT * FROM `chat` WHERE receiver_id = '" . $_SESSION['login_cus_id'] . "' AND seen = 0";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
    }
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-light color-2">
        <div class="container-fluid">
            <a class="navbar-brand" style="margin-left: 10px;" href="../homepage/index.php"><img height="50px" src="../../../assest/Logo.png"></a>
            <a class="font-bernard font-size-28" href="../homepage/index.php">F&amp;B Corner</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                <?php if (!isset($_SESSION['login_cus_id'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link active ps-2 me-1 align-self-center" href="../../../../admin/php/pages/signin_signup/sign_in.php" style="padding-top: 10px;"><i class="far fa-handshake" style="font-size: 30px; color: grey" data-bs-toggle="tooltip" data-bs-placement="bottom" title="For Restaurateurs"></i></a>
                    </li>
                <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link active ps-2 me-1 align-self-center" href="../promotion/promotional_ads.php" style="padding-top: 10px;"><i class="fas fa-bullhorn" style="font-size: 25px; color: grey" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Promotions"></i></a>
                    </li>
                <?php if (!isset($_SESSION['login_cus_id'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link active" id="sign-up-button" href="#" style="line-height: 32px;"><button type="button" class="btn btn-primary">Sign Up</button></a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link active ps-2 me-1 align-self-center" href="../restaurant/my_fav_restaurant.php" style="padding-top: 10px;"><i class="far fa-heart" style="font-size: 32px; color: grey" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Favourite Restaurants"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-2 pe-3" id="my_message" href="../message/my_message.php" style="line-height: 32px;">
                            <div class="position-relative" style="padding-top: 3px; height: 30px; width: fit-content;">
                                <i class="far fa-comment-alt" style="font-size: 30px; color: grey" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Messages"></i>
                                <span class="position-absolute translate-middle p-2 bg-danger border border-light rounded-circle<?=$results ? '' : ' d-none'?>" style="top: 15%; left: 90%" id="my_message_noti">
                                    <span class="visually-hidden">New alerts</span>
                                </span>
                            </div>
                        </a>
                    </li>
                <?php } ?>
                    <li class="nav-item dropdown">
                        <?php if (!isset($_SESSION['login_cus_id'])) { ?>
                            <a class="nav-link active" id="sign-in-button" href="#" style="line-height: 32px;"><button type="button" class="btn btn-secondary" style="width: 86.28px;">Sign In</button></a>
                        <?php } else { ?>
                            <?php
                                include_once '../../helpers/customer.php';
                                $customer = getCustomer($connection, $_SESSION['login_cus_id']);
                                $profile_img = $customer['profile_pic'];
                            ?>
                            <a class="nav-link dropdown-toggle p-0 mt-1" id="nav-bar-dropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><img src="../../../uploads/profile_pic/<?=$profile_img?>" alt="" width="45" height="45" class="rounded-circle" style="border: 3px solid grey;"></a>
                            <ul class="dropdown-menu dropdown-menu-end box-style" style="background-color: white;" id="nav-bar-dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                <div class="personal m-3 ms-4">
                                    <h5 class="fw-bold">Personal</h5>
                                    <img width='60' height='60' style="border: 3px solid #d0efff;" class='float-start me-2 rounded-circle' src="../../../uploads/profile_pic/<?=$profile_img?>">
                                    <p class="mb-1 mt-3">&nbsp;&nbsp;<?=$customer['firstname'] . ' ' . $customer['lastname']?></p>
                                    <a href="../customer/profile_manage.php"><p style="cursor: pointer; color: blue">&nbsp;&nbsp;Manage Profile Settings</p></a>
                                </div>
                                <li><a class="dropdown-item" href="../reservation/my_reservation.php"><i class="bi bi-calendar-event-fill"></i>&nbsp;&nbsp;&nbsp;&nbsp;My Reservations</a></li>
                                <li>
                                    <a class="dropdown-item" href="../cart/my_cart.php">
                                        <div>
                                            <i class="fas fa-shopping-cart"></i>&nbsp;&nbsp;&nbsp;&nbsp;My Cart&nbsp;&nbsp;
                                            <span class="badge bg-danger" id="my_cart_total"></span>
                                        </div>
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="../order/my_order.php"><i class="bi bi-clipboard-fill"></i>&nbsp;&nbsp;&nbsp;&nbsp;My Orders</a></li>
                                <li><a class="dropdown-item" href="../voucher/my_voucher.php"><i class="fas fa-ticket-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp;My Vouchers</a></li>
                                <li><a class="dropdown-item" id="btn-sign-out" href="#"><i class="fas fa-sign-out-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp;Sign Out</a></li>
                            </ul>
                        <?php } ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
