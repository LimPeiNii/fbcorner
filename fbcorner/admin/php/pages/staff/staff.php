<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/staff.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'staff/staff', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'staff/staff', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            $staffs = getStaffs($connection, $_SESSION['login_rest_id']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff &VerticalLine; F&amp;B Corner</title>

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

            .staff-list{
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
                <button type="button" onclick="delete_data();" class="btn bg-danger bg-opacity-75 float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><i class="fas fa-trash-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <a href="staff_form.php"><button type="button" class="btn btn-secondary float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Staff
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive staff-list">
                    <?php if (count($staffs) == 0 || count($staffs) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4">No.</td>
                                <th width="15%" class="text-center">Image</td>
                                <th width="25%" class="px-3">Name</td>
                                <th width="30%" class="px-3">Email</td>
                                <th width="10%" class="px-3">Contact Number</td>
                                <th width="10%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                foreach ($staffs as $staff) { 
                                    $counter++;
                            ?>
                                <tr class="align-middle">
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$staff['staff_id']?>"></td>
                                    <td width="5%" class="text-center align-middle"><?=$counter?></td>
                                    <td width="15%" class="text-center"><img src="../../../../admin/uploads/staff_img/<?=$staff['image']?>" height="80" width="80" class="rounded-circle" style="border: 3px solid #d0efff;"></td>
                                    <td class="px-3"><?=$staff['firstname'] . ' ' . $staff['lastname']?></td>
                                    <td class="px-3"><?=$staff['email']?></td>
                                    <td class="px-3"><?=$staff['contact_num']?></td>
                                    <td width="10%" class="text-center">
                                        <button type="button" class="btn bg-success bg-opacity-75" onclick="viewStaffDetails(<?=$staff['staff_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                        <a href="staff_form.php?staff_id=<?=$staff['staff_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
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

    <?php if ((int)$has_permission['has_permission']){ ?>
        <div class="modal fade p-0" id="detailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Staff Details</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script>
            function close_message() {
                $('.form-message').remove();
            }

            function delete_data(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (confirm("Are you sure you want to delete the staff(s)?\n* Relevant user(s) will also be deleted")){
                            $.ajax({ 
                                url: '../../helpers/staff.php',
                                data: {selected_group: selected_data},
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to delete the staff(s).</div>').insertBefore('.staff-list');
                    <?php } ?>
                }
            }

            function viewStaffDetails(id) {
                $.ajax({ 
                    url: '../../helpers/staff.php',
                    data: { 
                        id: id,
                        get_staff_data: true
                    },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        html += "<tr>";
                        html += "<td width='40%' class='px-3 fw-bold'>Image</td>";
                        html += "<td width='60%' class='p-3'><img width='80' height='80' class='me-2 rounded-circle' src='../../../uploads/staff_img/" + json['image'] + "'></td>";
                        html += "</tr>";

                        for (i in json){
                            if (i != 'image'){
                                html += "<tr>";
                                html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='60%' class='px-3 py-2'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</table>";
                    
                        $("#detailsModal .modal-body").html(html);
                        $("#detailsModal .modal-title").text("Staff Details (#" + id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#detailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });
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

                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                });

                $('.staff-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($staffs) == 0) { ?>
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