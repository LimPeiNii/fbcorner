<!-- javascript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<!-- jquery cdn -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- owl carousel cdn -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- chartjs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- bootstrap datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.2.0/js/bootstrap-datepicker.min.js"></script>

<!-- bootstrap pagination datatable -->
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

<!-- Full Calander -->
<script src='https://cdn.jsdelivr.net/npm/moment@2.27.0/min/moment.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/moment@5.5.0/main.global.min.js'></script>

<!-- custom script -->
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })

    function datetimeFormatter(dateTime, dateOrTime){
        var time = new Date(dateTime);
        if (time.getHours() > 12){
            var hour = parseInt(time.getHours()) - 12;
            var amPm = "pm";
        } else if (time.getHours() == 0) {
            var hour = 12; 
            var amPm = "am";
        } else if (time.getHours() == 12) {
            var hour = 12; 
            var amPm = "pm";
        } else {
            var hour = time.getHours(); 
            var amPm = "am";
        }

        if (hour < 10){
            var hour = '0' + hour;
        } else {
            var hour = hour;
        }

        if (time.getMinutes() < 10){
            var minute = '0' + time.getMinutes();
        } else {
            var minute = time.getMinutes();
        }

        if (time.getDate() < 10){
            var date = '0' + time.getDate();
        } else {
            var date = time.getDate();
        }

        if (time.getMonth() + 1 < 10){
            var month = '0' + (time.getMonth() + 1);
        } else {
            var month = time.getMonth() + 1;
        }

        if (dateOrTime == 'datetime')
            return (date + "/" + month + "/" + time.getFullYear() + " " + hour + ":" + minute + " " + amPm);
        else if (dateOrTime == 'time')
            return (hour + ":" + minute + " " + amPm);
        else if (dateOrTime == 'date')
            return (date + "/" + month + "/" + time.getFullYear());
        else if (dateOrTime == 'input_date')
            return (time.getFullYear() + "-" + month + "-" + date);
        else if (dateOrTime == 'datetime_local')
            return (time.getFullYear() + "-" + month + "-" + date + "T" + (time.getHours() < 10 ? '0' + time.getHours() : time.getHours()) + ":" + minute + ":" + (time.getSeconds() < 10 ? '0' + time.getSeconds() : time.getSeconds()));
    }

    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').on('click', function () {
            $(this).tooltip('hide');
        });

        //auto reload page if one tab is logged out
        window.addEventListener('storage', function(event){
            if (event.key == 'logout-event') { 
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        });

        //check session expired every 10 s
        setInterval(function() {
            $.ajax({
                url: '../../../../session.php',
                data: {check_session_expired: true},
                method: 'post',
                success: function(output){
                    if (output){
                        alert(output);
                        localStorage.setItem('logout-event', 'logout' + Math.random());
                        window.location.reload();
                    }
                }
            });
        }, 10000);

    });
</script>