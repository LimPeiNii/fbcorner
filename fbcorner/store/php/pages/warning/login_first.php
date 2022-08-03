<?php 
    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_POST['doc_title']) && isset($_POST['redirect'])){
        $_SESSION['doc_title'] = $_POST['doc_title'];
        $_SESSION['redirect'] = $_POST['redirect'];
    }

    if (!isset($_SESSION['login_cus_id'])){
        $success_msg = '';
        $error_msg = '';
        $info_msg = '';
        if (isset($_SESSION['success'])){
        $success_msg = $_SESSION['success'];
        unset($_SESSION['success']);
        } elseif (isset($_SESSION['error'])){
        $error_msg = $_SESSION['error'];
        unset($_SESSION['error']);
        } elseif (isset($_SESSION['info'])){
        $info_msg = $_SESSION['info'];
        unset($_SESSION['info']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?=$_SESSION['doc_title']?>

    <?php include_once '../../../../link.php'; ?>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>

    <?php include_once '../../common/signin_signup.php'; ?>

    <main style="height: 87vh;">
        <div class="d-flex h-100">
            <div class="m-auto d-flex flex-column">
                <img src="../../../assest/sad_face.png" height="300" width="300" class="align-self-center">
                <h4 class="mx-4">Please log in to your account to access the service</h4>
            </div>
            
        </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>
    
    <?php include_once '../../../../script.php'; ?>

    <?php include_once '../../../script.php'; ?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>
  </body>
</html>

<?php 

    } else {
        header('Location: ' . $_SESSION['redirect']);
    }

?>