<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/category.php';
    include_once '../../../../admin/php/helpers/restaurant.php';
    include_once '../../../../admin/php/helpers/promotion.php';

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

    //data
    if (isset($_GET['search-restaurant']) && !empty($_GET['search-restaurant'])){
        $search_input = $_GET['search-restaurant'];
    } 
    if (isset($_GET['search-location']) && !empty($_GET['search-location'])) {
        $search_location = $_GET['search-location'];
    }
    if (isset($_GET['search-filters'])){
        $filters = json_decode($_GET['search-filters'], true);
        if ($filters)
            $filters_applied = $filters;
    }
    if (isset($_GET['sort-by'])){
        $sort_by = $_GET['sort-by'];
    } else {
        $sort_by = 'rest_name';
    }

    $filter_data = [];

    //search input & location
    if (isset($search_input) && isset($search_location)){
        $filter_data = ['keyword' => $search_input, 'city' => $search_location];
    } elseif (isset($search_input)){
        $filter_data = ['keyword' => $search_input];
    } elseif (isset($search_location)){
        $filter_data = ['city' => $search_location];
    }

    $filter_data2 = $filter_data;
    $filter_data2['limit'] = 2;

    //filters applied
    if (isset($filters_applied)){
        foreach ($filters_applied as $key => $filter){
            if (strpos($key, 'food-category') !== false){
                $filter_data['category_name'][] = $filter;
            } elseif (strpos($key, 'dining-style') !== false){
                $filter_data['dining_style'][] = $filter;
            } elseif (strpos($key, 'promotion') !== false){
                $filter_data['promotion'] = $filter;
            } elseif (strpos($key, 'opening-time') !== false){
                $filter_data['opening_time'][$key] = $filter;
            } elseif (strpos($key, 'rating') !== false){
                $filter_data['rating'][] = substr($key, 7);
            }
        }
    }

    //sort by
    $sorting = array(
        'avg_rating' => 'DESC',
        'join_date'  => 'DESC'
    );

    if ($sort_by == 'opening_time'){
        $filter_data['sort_by'] = 'opening_time IS NULL, opening_time ASC';
    } elseif ($sort_by != 'rest_name'){
        $filter_data['sort_by'] = $sort_by  . ' ' . $sorting[$sort_by];
    }

    if (!isset($search_input) && !isset($search_location)){
        $search_results['restaurants'] = searchRestaurants($connection, $filter_data);
        $search_results['foods'] = [];
    } else {
        $search_results['restaurants'] = searchRestaurants($connection, $filter_data);
        $search_results['foods'] = searchFoodItems($connection, $filter_data2);
    }

    $final_result = [];
    foreach ($search_results['restaurants'] as $rest_info){
        $final_result[$rest_info['rest_id']] = $rest_info;
    }
    foreach ($search_results['foods'] as $food_info){
        if (!array_key_exists($food_info['rest_id'], $final_result)){
            $rest = getRestaurant($connection, $food_info['rest_id']);
            $dining_style = getDiningStyle($connection, $rest['dining_style_id']);
            $final_result[$food_info['rest_id']] = array(
                'rest_id'      => $rest['rest_id'],
                'rest_name'    => $rest['rest_name'], 
                'tags'         => $rest['tags'], 
                'rest_profile' => $rest['rest_profile'], 
                'dining_style' => $dining_style['dining_style'], 
                'city'         => $rest['city']
            );
        }
        $final_result[$food_info['rest_id']]['foods'][] = $food_info;
    }

    $food_categories = getCategories($connection, 'food_menu');
    $dining_styles = getDiningStyles($connection);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>

    <style>
        body{
            height: 100%;
        }
        
        #autocomplete-restaurant-list div, #autocomplete-food-list div{
            border: 2px solid #d4d4d4;
            border-top: 0px;
            border-bottom: 0px;
        }

        #autocomplete-restaurant-list div.active, #autocomplete-food-list div.active {
            background-color: #e9e9e9;
        }

        #filters-side-bar .list-group-item, #offcanvasFilters .list-group-item{
            border: 0px;
            padding-left: 0px;
        }

        .rest-foods .list-group-item{
            border-top-left-radius: 0px;
            border-top-right-radius: 0px;
        }

        @media (max-width: 768px){
            #filters-side-bar{
                display: none;
            }

            #search-result-wrapper{
                width: 100% !important;
            }

            #filters-btn{
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar.php'; ?>
    
    <?php include_once '../../common/signin_signup.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
        <!-- search bar -->
        <?php include_once '../../common/search.php'; ?>

        <div class="mx-5 px-2">
            <div class="d-flex mt-1 mb-3" id="filter-applied">
                <?php if (isset($filters_applied) && !empty($filters_applied)) { ?>
                    <span class="align-self-center mb-3">Filters:</span>
                    <div class="flex-grow-1 flex-wrap">
                    <?php foreach ($filters_applied as $id => $filter) { ?>
                        <div class="d-inline-block mb-3">
                        <div class="alert alert-light border border-dark fade show p-0 m-0 ms-3 d-flex" style="width: fit-content; height: 40px; border-radius: 5rem;" role="alert">
                            <div class="d-inline-block ps-2 align-self-center"><small><?=$filter?></small></div>
                            <button type="button" class="py-2 pe-2 mb-1" data-bs-dismiss="alert" aria-label="Close" onclick="reSearch($(this), '<?=$id?>');"><i class="bi bi-x-lg"></i></button>
                        </div>
                        </div>
                    <?php } ?>
                    </div>
                    <div class="ms-2 align-self-center mb-3"><button type="button" class="btn btn-sm btn-outline-danger" id="clear-filters-btn" onclick="reSearch($(this), 'clear-filters-btn')">Reset</button></div>
                <?php } ?>
            </div>
            <div class="d-flex">
                <span class="flex-grow-1">Search Results</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="filters-btn" style="display: none;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilters" aria-controls="offcanvasWithBackdrop"><i class="fas fa-filter"></i>&nbsp;Filters</button>

            </div>
            <hr class="mt-2">
            <div class="d-flex" style="min-height: 70vh;">
                <?php if (!$final_result) { ?>
                    <div style="width: 80%; height: fit-content;" id="search-result-wrapper" class="text-center pt-5">
                        <small>No results !</small>
                    </div>
                <?php } else { ?>
                    <div style="width: 80%;" id="search-result-wrapper" class="px-2">
                        <div class="list-group">
                        <?php foreach ($final_result as $result) { ?>
                            <!-- check if restaurant has promotion currently -->
                            <?php 
                                $on_promotions = getPromotions($connection, $result['rest_id'], ['status' => '1']);
                                if ($on_promotions)
                                    $on_promo_label = '<div class="d-inline-block px-2 py-1 rounded bg-danger bg-opacity-75 text-white font-size-12">PROMO</div>';
                                else
                                    $on_promo_label = '';
                            ?>

                            <a href="profile.php?visit_rest_id=<?=$result['rest_id']?>" class="list-group-item list-group-item-action mt-1<?=!isset($result['foods']) ? ' mb-1' : ''?> pt-3 pb-3 bg-opacity-25" style="border: 0px; background-color: #f4f4f4;">
                                <div class="d-flex flex-wrap w-100">
                                    <img src="../../../../admin/uploads/profile_pic/<?=$result
                                    ['rest_profile']?>" height="150" width="150" class="align-self-center me-3 mb-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1 me-2 d-inline-block" style="cursor: pointer"><?=$result['rest_name']?></h4><?=!empty($on_promo_label) ? $on_promo_label : ''?>
                                                <div class="text-muted">
                                                    <?php if (!empty($result['avg_rating'])) { ?>
                                                        <?php 
                                                            if (floor($result['avg_rating']) == ceil($result['avg_rating'])) { 
                                                                $final_avg_rating = $result['avg_rating'];
                                                            } else {
                                                                $final_avg_rating = floor($result['avg_rating']);
                                                            }
                                                        ?>
                                                        <?php for ($i=0; $i<$final_avg_rating; $i++) { ?>
                                                            <i class="fas fa-star text-danger"></i>
                                                        <?php } ?>
                                                        <?php if (floor($result['avg_rating']) != ceil($result['avg_rating'])) { ?>
                                                            <i class="fas fa-star-half-alt text-danger"></i>
                                                            <?php for ($i=0; $i<(4-$final_avg_rating); $i++) { ?>
                                                                <i class="far fa-star text-danger"></i>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <?php for ($i=0; $i<(5-$final_avg_rating); $i++) { ?>
                                                                <i class="far fa-star text-danger"></i>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php for ($i=0; $i<5; $i++) { ?>
                                                            <i class="far fa-star text-danger"></i>
                                                        <?php } ?>
                                                        &nbsp; <span class="text-muted">0 Ratings</span>
                                                    <?php } ?>
                                                </div>
                                                <?php if (!empty($result['city'])) { ?>
                                                    <small class="text-muted"><i class="fas fa-map-marker-alt"></i>&nbsp;&nbsp;<?=$result['city']?></small>
                                                    <?php if (!empty($result['dining_style']) || (!empty($result['opening_time']) && !empty($result['closing_time']))) { ?>
                                                    &nbsp;
                                                    <?php } else { ?>
                                                    <br>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php if (!empty($result['dining_style'])) { ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-utensils"></i>&nbsp;&nbsp;<span style="text-transform: capitalize;"><?=$result['dining_style']?></span>
                                                    </small>
                                                    <?php if (!empty($result['opening_time']) && !empty($result['closing_time'])) { ?>
                                                    &nbsp;
                                                    <?php } else { ?>
                                                    <br>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php if (!empty($result['opening_time']) && !empty($result['closing_time'])) { ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock"></i>&nbsp;&nbsp;<?=date('h:i a', strtotime($result['opening_time']))?> to <?=date('h:i a', strtotime($result['closing_time']))?>
                                                    </small>
                                                    <br>
                                                <?php } ?>
                                                <?php $tags = json_decode($result['tags'], true);?>
                                                <?php if (!empty($tags)) {
                                                        foreach ($tags as $tag){
                                                ?>
                                                    <small class="text-muted">#<?=$tag?>&nbsp;</small>
                                                <?php } ?>
                                                    <br>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <?php if (isset($result['foods'])){ ?>
                                <ul class="list-group rest-foods mb-1">
                                <?php foreach ($result['foods'] as $key => $food) { ?>
                                    <a href="../order/food_list.php?visit_rest_id=<?=$result['rest_id']?>#food-<?=$food['item_id']?>" class="list-group-item-action">
                                    <li class="list-group-item d-flex">
                                        <img src="../../../../admin/uploads/food_menu_photo/<?=$food['image']?>" height="60" width="60">
                                        <span class="d-inline-block ms-2">
                                            <?=$food['item_name']?><br>
                                            <small class="text-muted">RM <?=number_format($food['price'], 2, '.', '')?></small>
                                        </span>
                                    </li>
                                    </a>
                                <?php } ?>
                                </ul>
                            <?php } ?>
                            <hr>
                        <?php } ?>
                        </div>
                    </div>
                <?php } ?>
                <div style="width: 248px;" id="filters-side-bar" class="px-2">
                    <div class="mb-4">
                        <i class="fas fa-sort-amount-down"></i>&nbsp;&nbsp;<strong>SORT BY</strong>
                        <select class="form-select mt-2 border border-4 rounded" style="padding: 0.7rem !important;" id="sort-by" onchange="reSearch($(this));">
                            <option value="rest_name"<?=$sort_by == 'rest_name' ? ' selected' : ''?>>Restaurant Name</option>
                            <option value="avg_rating"<?=$sort_by == 'avg_rating' ? ' selected' : ''?>>Highest Rating</option>
                            <option value="opening_time"<?=$sort_by == 'opening_time' ? ' selected' : ''?>>Opening Time</option>
                            <option value="join_date"<?=$sort_by == 'join_date' ? ' selected' : ''?>>Newest</option>
                        </select>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-thumbtack"></i>&nbsp;&nbsp;<strong>FOOD CATEGORY</strong>
                        <ul class="list-group">
                            <?php foreach ($food_categories as $food_category) { ?>
                                <li class="list-group-item pb-0">
                                    <input class="form-check-input me-1 filter-checkbox" type="checkbox" value="<?=$food_category['category_id']?>" id="food-category-<?=$food_category['category_id']?>" onchange="reSearch($(this), 'food-category-<?=$food_category['category_id']?>');"<?=isset($filters_applied) && array_key_exists('food-category-' . (string)$food_category["category_id"], $filters_applied) ? ' checked' : ''?>>
                                    <label for="food-category-<?=$food_category['category_id']?>" class="d-inline"><?=ucwords($food_category['category_name'])?></label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-utensils"></i>&nbsp;&nbsp;<strong>DINING STYLE</strong>
                        <ul class="list-group">
                            <?php foreach ($dining_styles as $dining_style) { ?>
                                <li class="list-group-item pb-0">
                                    <input class="form-check-input me-1 filter-checkbox" type="checkbox" value="<?=$dining_style['dining_style_id']?>" id="dining-style-<?=$dining_style['dining_style_id']?>" onchange="reSearch($(this), 'dining-style-<?=$dining_style['dining_style_id']?>');"<?=isset($filters_applied) && array_key_exists('dining-style-' . (string)$dining_style['dining_style_id'], $filters_applied) ? ' checked' : ''?>>
                                    <label for="dining-style-<?=$dining_style['dining_style_id']?>" class="d-inline"><?=ucwords($dining_style['dining_style'])?></label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-star"></i>&nbsp;&nbsp;<strong>RATINGS</strong>
                        <ul class="list-group">
                            <?php
                                $ratings = array(
                                    'null' => 'No Ratings', 
                                    '1'    => '1 Star',
                                    '2'    => '2 Stars',
                                    '3'    => '3 Stars',
                                    '4'    => '4 Stars',
                                    '5'    => '5 Stars'
                                );
                            ?>
                            <?php foreach ($ratings as $id => $rating) { ?>
                            <li class="list-group-item pb-0">
                                <input class="form-check-input me-1 filter-checkbox" type="checkbox" value="<?=$id?>" id="rating-<?=$id?>" onchange="reSearch($(this), 'rating-<?=$id?>');"<?=isset($filters_applied) && array_key_exists('rating-' . (string)$id, $filters_applied) ? ' checked' : ''?>>
                                <label for="rating-<?=$id?>" class="d-inline"><?=$rating?></label>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-bullhorn"></i>&nbsp;&nbsp;<strong>PROMOTIONS</strong>
                        <ul class="list-group">
                            <li class="list-group-item pb-0">
                                <input class="form-check-input me-1 filter-checkbox" type="checkbox" value="1" id="promotion" onchange="reSearch($(this), 'promotion');"<?=isset($filters_applied) && array_key_exists('promotion', $filters_applied) ? ' checked' : ''?>>
                                <label for="promotion" class="d-inline">With Promotions</label>
                            </li>
                        </ul>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-clock"></i>&nbsp;&nbsp;<strong>OPENING TIME</strong>
                        <ul class="list-group">
                            <?php
                                $open_times = array(
                                    'before-8' => '< 8am',
                                    '8-10'     => '8am - 10am',
                                    '10-12'    => '10am - 12pm',
                                    '12-14'    => '12pm - 2pm',
                                    '14-16'    => '2pm - 4pm',
                                    '16-18'    => '4pm - 6pm',
                                    '18-20'    => '6pm - 8pm',
                                    'after-20'    => '> 8pm',
                                );
                            ?>
                            <?php foreach ($open_times as $id => $open_time) { ?>
                            <li class="list-group-item pb-0">
                                <input class="form-check-input me-1 filter-checkbox" type="checkbox" value="<?=$id?>" id="opening-time-<?=$id?>" onchange="reSearch($(this), 'opening-time-<?=$id?>');"<?=isset($filters_applied) && array_key_exists('opening-time-' . (string)$id, $filters_applied) ? ' checked' : ''?>>
                                <label for="opening-time-<?=$id?>" class="d-inline"><?=$open_time?></label>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasFilters" aria-labelledby="offcanvasFilters">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Filters</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 position-relative">
            <div class="position-sticky top-0" style="background-color: white; z-index: 1;">
                <div class="d-flex justify-content-between pb-4 pt-0" style="padding: 1.5rem 1.5rem;">
                    <button type="button" class="btn btn-sm btn-danger text-center" onclick="$('#clear-filters-btn').click();">Reset</button>
                    <button type="button" class="btn btn-sm btn-primary text-center" onclick="reSearch($(this), 'apply-btn-offcanvas');">Apply</button>
                </div>
            </div>
            <div style="padding: 1.5rem 1.5rem;" class="pt-0">
                <div class="mb-4">
                    <i class="fas fa-sort-amount-down"></i>&nbsp;&nbsp;<strong>SORT BY</strong>
                    <select class="form-select mt-2 border border-4 rounded" style="padding: 0.7rem !important;" id="sort-by-offcanvas">
                        <option value="rest_name"<?=$sort_by == 'rest_name' ? ' selected' : ''?>>Restaurant Name</option>
                        <option value="avg_rating"<?=$sort_by == 'avg_rating' ? ' selected' : ''?>>Highest Rating</option>
                        <option value="opening_time"<?=$sort_by == 'opening_time' ? ' selected' : ''?>>Opening Time</option>
                        <option value="join_date"<?=$sort_by == 'join_date' ? ' selected' : ''?>>Newest</option>
                    </select>
                </div>
                <hr>
                <div class="mb-4">
                    <i class="fas fa-thumbtack"></i>&nbsp;&nbsp;<strong>FOOD CATEGORY</strong>
                    <ul class="list-group">
                        <?php foreach ($food_categories as $food_category) { ?>
                            <li class="list-group-item pb-0">
                                <input class="form-check-input me-1 filter-checkbox-offcanvas" type="checkbox" value="<?=$food_category['category_id']?>" id="offcanvas-food-category-<?=$food_category['category_id']?>"<?=isset($filters_applied) && array_key_exists('food-category-' . (string)$food_category["category_id"], $filters_applied) ? ' checked' : ''?>>
                                <label for="offcanvas-food-category-<?=$food_category['category_id']?>" class="d-inline"><?=ucwords($food_category['category_name'])?></label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <hr>
                <div class="mb-4">
                    <i class="fas fa-utensils"></i>&nbsp;&nbsp;<strong>DINING STYLE</strong>
                    <ul class="list-group">
                        <?php foreach ($dining_styles as $dining_style) { ?>
                            <li class="list-group-item pb-0">
                                <input class="form-check-input me-1 filter-checkbox-offcanvas" type="checkbox" value="<?=$dining_style['dining_style_id']?>" id="offcanvas-dining-style-<?=$dining_style['dining_style_id']?>"<?=isset($filters_applied) && array_key_exists('dining-style-' . (string)$dining_style['dining_style_id'], $filters_applied) ? ' checked' : ''?>>
                                <label for="offcanvas-dining-style-<?=$dining_style['dining_style_id']?>" class="d-inline"><?=ucwords($dining_style['dining_style'])?></label>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <hr>
                <div class="mb-4">
                    <i class="fas fa-star"></i>&nbsp;&nbsp;<strong>RATINGS</strong>
                    <ul class="list-group">
                        <?php foreach ($ratings as $id => $rating) { ?>
                        <li class="list-group-item pb-0">
                            <input class="form-check-input me-1 filter-checkbox-offcanvas" type="checkbox" value="<?=$id?>" id="offcanvas-rating-<?=$id?>"<?=isset($filters_applied) && array_key_exists('rating-' . (string)$id, $filters_applied) ? ' checked' : ''?>>
                            <label for="offcanvas-rating-<?=$id?>" class="d-inline"><?=$rating?></label>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <hr>
                <div class="mb-4">
                    <i class="fas fa-bullhorn"></i>&nbsp;&nbsp;<strong>PROMOTIONS</strong>
                    <ul class="list-group">
                        <li class="list-group-item pb-0">
                            <input class="form-check-input me-1 filter-checkbox-offcanvas" type="checkbox" value="1" id="offcanvas-promotion"<?=isset($filters_applied) && array_key_exists('promotion', $filters_applied) ? ' checked' : ''?>>
                            <label for="offcanvas-promotion" class="d-inline">With Promotions</label>
                        </li>
                    </ul>
                </div>
                <hr>
                <div class="mb-4">
                    <i class="fas fa-clock"></i>&nbsp;&nbsp;<strong>OPENING TIME</strong>
                    <ul class="list-group">
                        <?php foreach ($open_times as $id => $open_time) { ?>
                        <li class="list-group-item pb-0">
                            <input class="form-check-input me-1 filter-checkbox-offcanvas" type="checkbox" value="<?=$id?>" id="offcanvas-opening-time-<?=$id?>"<?=isset($filters_applied) && array_key_exists('opening-time-' . (string)$id, $filters_applied) ? ' checked' : ''?>>
                            <label for="offcanvas-opening-time-<?=$id?>" class="d-inline"><?=$open_time?></label>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <!-- custom javascript -->
    <script src="../../../javascript/signin_signup.js"></script>

    <script src="../../../javascript/search.js"></script>

    <script>
        function reSearch(this_element, id = ''){
            var filters_applied = {};
            if (this_element.is('button') && id == 'clear-filters-btn'){
                $('.filter-checkbox:checked').each(function() {
                    $(this).prop('checked', false);
                });
            } else if (this_element.is('button') && id != 'clear-filters-btn' && id != 'apply-btn-offcanvas'){
                $('#' + id).prop('checked', false);
            }

            if (this_element.is('button') && id == 'apply-btn-offcanvas'){
                $('.filter-checkbox-offcanvas:checked').each(function() {
                    filters_applied[$(this).attr('id').substring(10)] = $(this).next().text();
                });
            } else {
                $('.filter-checkbox:checked').each(function() {
                    filters_applied[$(this).attr('id')] = $(this).next().text();
                });
            }

            var url = 'search_list.php?search-restaurant=' + $('#search-bar').val() + '&search-location=' + $('#location-dropdown').val() + '&sort-by=' + $('#sort-by').val();

            if (this_element.is('button') && id == 'apply-btn-offcanvas'){
                url += '&sort-by=' + $('#sort-by-offcanvas').val();
            } else {
                url += '&sort-by=' + $('#sort-by').val();;
            }

            if (filters_applied)
                url += '&search-filters=' + JSON.stringify(filters_applied);

            window.location.href = url;
        }

        <?php if (isset($search_input)) { ?>
            $('#search-bar').val('<?=$search_input?>');
        <?php } ?>

        $(document).ready(function() {
            $("#search-form").submit(function(event){
                event.preventDefault();
                
                reSearch($('#search-bar'));
            });

            $('#view-all-result').parent().remove();
            $('#autocomplete-restaurant-food').css('border-bottom', '2px solid #d4d4d4');

            $('#filters-btn').click(function() {

            });

            $('#autocomplete-restaurant-food').css('display', 'none');
            $('#search-bar').trigger('input');

            $('#search-bar').on('click', function() {
                $('#autocomplete-restaurant-food').css('display', '');
            });
        });
    </script>

  </body>
</html>