<?php 

    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();
    
    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/order.php';
        include_once '../../helpers/reservation.php';
        include_once '../../helpers/customer.php';
        include_once '../../helpers/food_menu.php';
        include_once '../../helpers/restaurant.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'dashboard/dashboard', 'access_permission');
   
        if ((int)$has_permission['has_permission']){
            $orders = getOrders($connection, $_SESSION['login_rest_id'], ['not_statuses' => ['Completed', 'Cancelled', 'Disabled']]);
            $reservations = getReservations($connection, $_SESSION['login_rest_id'], array('status' => 1, 'not_remarks' => 'Ongoing'));
            $customers = getRestaurantCustomers($connection, $_SESSION['login_rest_id']);
            $share_counts = getSharingCount($connection, $_SESSION['login_rest_id'], date('m'), date('Y'));
            $visitor_counts = getVisitorCount($connection, $_SESSION['login_rest_id'], date('m'), date('Y'));
            $food_items = getFoodMenuItems($connection, $_SESSION['login_rest_id']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            .col{
                cursor: default;
            }

            #datepicker{
                border-top-right-radius: unset;
                border-bottom-right-radius: unset;
            }

            #datepicker-btn{
                margin-left: -4px;
                border-top-left-radius: unset;
                border-bottom-left-radius: unset;
                border: 1px solid black;
                border-left: 0px;
                background-color: #d4dbe0 !important;
            }

            #refresh-btn i{
                transition: transform 1s ease;
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
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Dashboard
                </h3>
                <div class="my-4">
                    <div class="m-auto row row-cols-1 row-cols-sm-2 row-cols-md-3">
                        <div class="col p-3">
                            <div class="text-white h-100 d-flex flex-column" style="background-color: RGB(217, 66, 48)">
                                <div class="p-3 position-relative">
                                    <div>
                                        <div class="font-size-35 fw-bold" id="order-value"><?=count($orders)?></div>
                                        <div>Uncompleted Orders</div>
                                    </div>
                                    <div class="ps-2 position-absolute" style="top: 1rem; right: 1rem;"><i class="fas fa-clipboard" style="font-size: 80px; color: #0000004a"></i></div>
                                </div>
                                <a href="../order/order.php" class="mt-auto"><div class="text-center text-white py-2 view-btn" style="background-color: #0000003d"><i class="fas fa-arrow-circle-right"></i>&nbsp;&nbsp;View</div></a>
                            </div>
                        </div>
                        <div class="col p-3">
                            <div class="text-white h-100 d-flex flex-column" style="background-color: RGB(240, 145, 19)">
                                <div class="p-3 position-relative">
                                    <div>
                                        <div class="font-size-35 fw-bold" id="reservation-value"><?=count($reservations)?></div>
                                        <div>Upcoming Reservations</div>
                                    </div>
                                    <div class="ps-2 position-absolute" style="top: 1rem; right: 1rem;"><i class="fas fa-calendar-alt" style="font-size: 80px; color: #0000004a"></i></div>
                                </div>
                                <a href="../reservation/reservation.php" class="mt-auto"><div class="text-center text-white py-2 view-btn" style="background-color: #0000003d"><i class="fas fa-arrow-circle-right"></i>&nbsp;&nbsp;View</div></a>
                            </div>
                        </div>
                        <div class="col p-3">
                            <div class="text-white h-100 d-flex flex-column" style="background-color: RGB(1, 156, 80)">
                                <div class="p-3 position-relative">
                                    <div>
                                        <div class="font-size-35 fw-bold" id="customer-value"><?=count($customers)?></div>
                                        <div>Customers</div>
                                    </div>
                                    <div class="ps-2 position-absolute" style="top: 1rem; right: 1rem;"><i class="fas fa-users" style="font-size: 80px; color: #0000004a"></i></div>
                                </div>
                                <a href="../customer/customer.php" class="mt-auto"><div class="text-center text-white py-2 view-btn" style="background-color: #0000003d"><i class="fas fa-arrow-circle-right"></i>&nbsp;&nbsp;View</div></a>
                            </div>
                        </div>
                        <div class="col p-3">
                            <div class="text-white h-100 d-flex flex-column" style="background-color: RGB(0, 184, 238)">
                                <div class="p-3 position-relative">
                                    <div>
                                        <div class="font-size-35 fw-bold" id="share-value"><?=!empty($share_counts) ? $share_counts['count'] : '0' ?></div>
                                        <div>Share Counts</div>
                                    </div>
                                    <div class="ps-2 position-absolute" style="top: 1rem; right: 1rem;"><i class="fas fa-share" style="font-size: 80px; color: #0000004a"></i></div>
                                </div>
                                <div class="text-center text-white py-2 view-btn" style="background-color: #0000003d; height: 40px"></div>
                            </div>
                        </div>
                        <div class="col p-3">
                            <div class="text-white h-100 d-flex flex-column" style="background-color: RGB(191, 128, 255)">
                                <div class="p-3 position-relative">
                                    <div>
                                        <div class="font-size-35 fw-bold" id="visitor-value"><?=!empty($visitor_counts) ? $visitor_counts['count'] : '0' ?></div>
                                        <div>Visitors</div>
                                    </div>
                                    <div class="ps-2 position-absolute" style="top: 1rem; right: 1rem;"><i class="fas fa-eye" style="font-size: 80px; color: #0000004a"></i></div>
                                </div>
                                <div class="text-center text-white py-2 view-btn" style="background-color: #0000003d; height: 40px"></div>
                            </div>
                        </div>
                        <div class="col p-3">
                            <div class="text-white h-100 d-flex flex-column" style="background-color: RGB(180, 111, 111)">
                                <div class="p-3 position-relative">
                                    <div>
                                        <div class="font-size-35 fw-bold" id="food-value"><?=count($food_items)?></div>
                                        <div>Food Items</div>
                                    </div>
                                    <div class="ps-2 position-absolute" style="top: 1rem; right: 1rem;"><i class="fas fa-pizza-slice" style="font-size: 80px; color: #0000004a"></i></div>
                                </div>
                                <a href="../food_menu/food_menu.php" class="mt-auto"><div class="text-center text-white py-2 view-btn" style="background-color: #0000003d"><i class="fas fa-arrow-circle-right"></i>&nbsp;&nbsp;View</div></a>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 p-3 d-flex flex-column">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 justify-content-between">
                            <div style="width: fit-content;" class="col p-2">
                                <select class="form-select d-inline-block" id="chart-type" style="width: 155px">
                                    <option value="Orders">Orders</option>
                                    <option value="Reservations">Reservations</option>
                                    <option value="Earning">Earning</option>
                                    <option value="Sharings">Sharings</option>
                                    <option value="Visitors">Visitors</option>
                                </select>
                            </div>
                            <div style="width: fit-content;" class="col p-2 d-flex">
                                <input type="text" class="form-control d-inline-block" placeholder="Year" style="width: 155px" name="datepicker" id="datepicker">
                                <button type="button" class="btn" id="datepicker-btn"><i class="fas fa-calendar-alt"></i></button>
                                <button type="button" id="refresh-btn" class="btn btn-secondary ms-2 text-dark" style="background-color: #d4dbe0 !important" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="Refresh" aria-label="Refresh"><i class="fas fa-undo fa-rotate-90"></i></button>
                            </div>
                        </div>
                        <div>
                            <h2 id="chart-title" class="text-center mt-3 font-bernard"></h2>
                            <div id="chart-parent" style="height: 400px;">
                                <canvas id="chartContainer"></canvas>
                            </div>
                        </div>
                    </div>
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
            var chart;

            function generateChart(year, type) {
                $.ajax({
                    url: '../../helpers/dashboard.php',
                    data: {
                        year: year,
                        type: type,
                        generate_chart: true
                    },
                    method: 'post',
                    success: function(output) {
                        var json = $.parseJSON(output);

                        $('#chart-title').text("Total " + type + " in " + year.toString());

                        var labels = json['labels'];

                        var data = {
                            labels: labels,
                            datasets: [{
                                label: "Total " + type,
                                backgroundColor: 'rgb(255, 99, 132)',
                                borderColor: 'rgb(255, 99, 132)',
                                data: json['data']
                            }]
                        };

                        var config = {
                            type: 'line',
                            data: data,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        };

                        var chart = new Chart(
                            $('#chartContainer'), config
                        );
                    }
                })
            }

            function destroyChart(id){
                var current_chart = Chart.getChart(id);
                if (typeof current_chart !== 'undefined')
                    current_chart.destroy();
            }

            $(document).ready(function() {
                $('#datepicker').val(parseInt((new Date()).getFullYear()));
                generateChart(parseInt((new Date()).getFullYear()), $('#chart-type').val());

                $("#datepicker").datepicker({
                    format: "yyyy",
                    viewMode: "years", 
                    minViewMode: "years",
                    autoclose: true //to close picker once year is selected
                }).on('hide', function() {
                    destroyChart('chartContainer');
                    generateChart(parseInt($('#datepicker').val()), $('#chart-type').val());
                });

                $('#datepicker-btn').click(function() {
                    $("#datepicker").datepicker("show");
                });

                $('#chart-type').change(function() {
                    destroyChart('chartContainer');
                    generateChart(parseInt($('#datepicker').val()), $('#chart-type').val());
                });

                $('#refresh-btn').click(function() {
                    destroyChart('chartContainer');
                    generateChart(parseInt($('#datepicker').val()), $('#chart-type').val());
                    $('#refresh-btn i').css({'transform': 'rotate(-90deg)'});
                    $('#refresh-btn i').on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', function() {
                        $('#refresh-btn i').css({'transform': 'rotate(90deg)'});
                    });
                });

                setInterval(function() {
                    $.ajax({
                        url: '../../helpers/dashboard.php',
                        data: {update_dashboard_data: true},
                        method: 'post',
                        success: function(output){
                            var json = $.parseJSON(output);

                            $.each(json, function(key, value) {
                                $('#' + key + '-value').text(value);
                            });
                        }
                    });
                }, 1000);
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