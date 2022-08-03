<?php 
    $sql = "SELECT * FROM `chat` WHERE receiver_id = '" . $_SESSION['login_rest_id'] . "' AND seen = 0";
    $statement = $connection->query($sql);
    $results = $statement->fetch_all(MYSQLI_ASSOC);
?>

<nav class="navbar navbar-expand-lg navbar-light color-2">
    <div class="container-fluid justify-content-start">
        <button type="button" class="ms-2" id="side-bar-expand">
            <i class="font-size-25"></i>
        </button>
        <a class="navbar-brand ms-3" href="../../../../store/php/pages/homepage/index.php" target="_blank"><img height="50px" src="../../../../store/assest/Logo.png"></a>
        <a class="font-bernard font-size-28" href="../../../../store/php/pages/homepage/index.php" target="_blank">F&amp;B Corner</a>
        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active ps-2 me-2 mt-1 align-self-center" href="../../../../store/php/pages/restaurant/profile.php?visit_rest_id=<?=$_SESSION['login_rest_id']?>" target="_blank" style="padding-top: 10px; color: grey"><i class="fas fa-store font-size-25" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Store" aria-label="Store"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active px-2 pe-3" id="my_message" href="../message/message.php" style="line-height: 32px;">
                        <div class="position-relative" style="padding-top: 3px; height: 30px; width: fit-content;">
                            <i class="far fa-comment-alt" style="font-size: 27px; line-height: 38px; color: grey" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Messages"></i>
                            <span class="position-absolute translate-middle p-2 bg-danger border border-light rounded-circle<?=$results ? '' : ' d-none'?>" style="top: 30%; left: 90%" id="my_message_noti">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <?php if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) { ?>
                        <a class="nav-link active" id="btn-sign-out" href="#" style="line-height: 32px;"><button type="button" class="btn btn-primary">Sign Out</button></a>
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>
</nav>