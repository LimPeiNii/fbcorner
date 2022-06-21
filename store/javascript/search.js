$('#location-dropdown').on('change', function() {
    localStorage.setItem('selected-location', $(this).val());
});

if (localStorage.getItem('selected-location')){
    var id = '#location-' + (localStorage.getItem('selected-location').replace(' ', '-'));
    $(id).attr('selected', 'selected');
}

function highlightSearchResults(search_input, data){
    if (data.toLowerCase().indexOf(search_input) != -1){
        var start_index = data.toLowerCase().indexOf(search_input);
        var end_index = start_index + search_input.length;
        var result = data.substring(0, start_index) + '<strong style="background-color: #e9c5ff;">' + data.substring(start_index, end_index) + '</strong>' + data.substring(end_index);
        return result;
    } else {
        return data;
    }
}

function searchResult(search_input, data){
    $.ajax({
        url: '../../helpers/restaurant.php',
        data: data,
        method: 'post',
        success: function(output){
            var json = $.parseJSON(output);

            $('#no-result').remove();

            if (!jQuery.isEmptyObject(json)){
                $('#autocomplete-restaurant-food').removeClass('d-none');

                if (json['restaurants']){
                            var html = '';

                    $.each(json['restaurants'], function(key, restaurant) {
                            html += '<a href="../restaurant/profile.php?visit_rest_id=' + restaurant['id'] + '">';
                            html += '<div class="p-3 d-flex flex-wrap search-option">';
                            html +=   '<img class="align-self-center mb-2 me-3" height="80" width="80" style="border: 1px solid grey;" src="../../../../admin/uploads/profile_pic/' + restaurant['image'] + '">'
                            html +=   '<div class="d-inline-block flex-grow-1 me-3" style="border: 0px;">';
                            html +=     '<h5 class="d-inline">' + highlightSearchResults(search_input, restaurant['rest_name']) + '</h5><br>';
                    
                      if (restaurant['tags'] && restaurant['tags'].length > 0){
                            html +=     '<span style="color: blue">';

                        for (i in restaurant['tags']){
                            html +=     '<strong>#</strong>' + highlightSearchResults(search_input, restaurant['tags'][i]).toLowerCase() + ' ';
                        }

                            html +=     '</span><br>';
                      }

                            html +=     '<span class="text-muted" style="font-size: 0.75rem;">RESTAURANT</span>';
                            html +=   '</div>';
                    
                      if (restaurant['dining_style']){
                            html +=   '<div style="border: 0px; white-space: nowrap;" class="text-muted">';
                            html +=     '<i class="fas fa-utensils"></i>&nbsp;&nbsp;Dining Style<br>'
                            html +=     '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><span style="text-transform: capitalize;">' + highlightSearchResults(search_input, restaurant['dining_style']) + '</span></small><br>';
                            html +=   '</div>';
                      }

                            html += '</div>';
                            html += '</a>';
                    });

                    $('#autocomplete-restaurant-list').html(html);
                    $('#autocomplete-restaurant-list').removeClass('d-none');
                } else {
                    $('#autocomplete-restaurant-list').html('');
                }

                if (json['foods']){
                    var html2 = '';

                  $.each(json['foods'], function(key, food) {
                    html2 += '<a href="../order/food_list.php?visit_rest_id=' + food['rest_id'] + '#food-' + food['id'] + '">';
                    html2 += '<div class="p-3 d-flex flex-wrap search-option">';
                    html2 +=   '<img class="align-self-center mb-2 me-3" style="border: 1px solid grey" height="80" width="80" src="../../../../admin/uploads/food_menu_photo/' + food['image'] + '">'
                    html2 +=     '<div class="d-inline-block flex-grow-1 me-3" style="border: 0px;">';
                    html2 +=       '<h5 class="d-inline">' + highlightSearchResults(search_input, food['item_name']) + '</h5><br>';
                    html2 +=       '<span class="text-muted" style="font-size: 0.75rem;">FOOD</span>';
                    html2 +=     '</div>';
                    html2 +=   '<div style="border: 0px; white-space: nowrap;" class="text-muted">';
                    html2 +=     '<i class="fas fa-thumbtack"></i>&nbsp;&nbsp;Category&nbsp;&nbsp;&nbsp;&nbsp;<br>'
                    html2 +=     '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small><span style="text-transform: capitalize;">' + highlightSearchResults(search_input, food['category_name']) + '</span></small><br>';
                    html2 +=   '</div>';
                    html2 += '</div>';
                    html2 += '</a>';
                  });

                    $('#autocomplete-food-list').html(html2);
                    $('#autocomplete-food-list').removeClass('d-none');
                } else {
                    $('#autocomplete-food-list').html('');
                }

            } else {
                var html = '';

                html += '<div class="p-3 d-flex" id="no-result">';
                html +=   '<i class="bi bi-search"></i>'
                html +=     '<div class="d-inline-block flex-grow-1 mx-3 align-self-center" style="border: 0px;">';
                html +=       '<h5 class="d-inline">' + $('#search-bar').val() + '</h5><br>';
                html +=     '</div>';
                html += '</div>';

                $('#autocomplete-food-list').next().prepend(html);
                $('#autocomplete-restaurant-food').removeClass('d-none');
                $('#autocomplete-restaurant-list').html('');
                $('#autocomplete-food-list').html('');
            }
        }
    });
}

function addActive(index){
    $('.search-option').removeClass('active');
    $('.search-option').eq(index-1).addClass('active');
}

$(document).ready(function() {
    $('#search-bar').on('input', function() {
        if ($(this).val() == ''){
            $('#autocomplete-restaurant-food').addClass('d-none');
        } else {
            var data = {search_input: $('#search-bar').val().toLowerCase()};

            if ($('#location-dropdown').val() != '')
                data['location_select'] = $('#location-dropdown').val();

            searchResult($(this).val().toLowerCase(), data);
        }
    });

    $('#search-bar').on('keydown', function(e) {
        if ($(this).val() == '')
            $('#autocomplete-restaurant-food').addClass('d-none');
        else{
            if ($('.search-option.active').length != 0)
                var current_focus = $('.search-option').index($('.search-option.active')) + 1;
            else
                var current_focus = 0;
            
            if (e.keyCode == 40) {
                //If the arrow DOWN key is pressed
                current_focus++;
                if (current_focus > $('.search-option').length)
                current_focus = 1;

                addActive(current_focus);
            } else if (e.keyCode == 38) {
                //If the arrow UP key is pressed
                current_focus--;
                if (current_focus <= 0)
                current_focus = $('.search-option').length;

                addActive(current_focus);
            } else if (e.keyCode == 13) {
                //If the ENTER key is pressed, prevent the form from being submitted
                if ($('.search-option.active').length != 0){
                e.preventDefault();
                $('.search-option.active').trigger('click');
                }
            }
        }
    });

    $('#location-dropdown').on('change', function() {
        if ($('#search-bar').val() != ''){
            var data = {search_input: $('#search-bar').val().toLowerCase()};

            if ($('#location-dropdown').val() != '')
                data['location_select'] = $('#location-dropdown').val();

            searchResult($('#search-bar').val().toLowerCase(), data);
        }
    });

    $(document).on('click', function (event) {
        if (!$(event.target).closest('#search-bar, #location-dropdown').length) {
            $('#autocomplete-restaurant-food').addClass('d-none');
        }
    });

    $('#search-bar').click(function () {
        if ($(this).val())
            $('#autocomplete-restaurant-food').removeClass('d-none');
    });

    $(document).on('mouseover', '.search-option', function () {
        $('.search-option').removeClass('active');
        $(this).addClass('active');
    });

    $(document).on('mouseleave', '.search-option', function () {
        $('.search-option').removeClass('active');
    });
});