<form class="form-inline my-3 mx-5 position-relative" id="search-form" autocomplete="off" action="../restaurant/search_list.php" method="GET" style="z-index: 10;">
    <div class="input-group">
        <input type="search" class="form-control" id="search-bar" name="search-restaurant" placeholder="Restaurant, Keywords, Food">
        <select class="form-select text-start" name="search-location" id="location-dropdown" style="max-width: fit-content;">
            <option value="" selected>-- Location --</option>
            <option value="Kuala Lumpur" id="location-Kuala-Lumpur">Kuala Lumpur</option>
            <option value="Selangor" id="location-Selangor">Selangor</option>
            <option value="Johor" id="location-Johor">Johor</option>
            <option value="Kedah" id="location-Kedah">Kedah</option>
            <option value="Kelantan" id="location-Kelantan">Kelantan</option>
            <option value="Melacca" id="location-Melacca">Melacca</option>
            <option value="Negeri Sembilan" id="location-Negeri-Sembilan">Negeri Sembilan</option>
            <option value="Pahang" id="location-Pahang">Pahang</option>
            <option value="Penang" id="location-Penang">Penang</option>
            <option value="Perak" id="location-Perak">Perak</option>
            <option value="Perlis" id="location-Perlis">Perlis</option>
            <option value="Terengganu" id="location-Terengganu">Terengganu</option>
            <option value="Sabah" id="location-Sabah">Sabah</option>
            <option value="Sarawak" id="location-Sarawak">Sarawak</option>
        </select>
        <button class="btn btn-dark align-items-stretch" id="search-btn" style="margin: 0; line-height: 30px;" type="submit"><i class="fas fa-search"></i>&nbsp;&nbsp;Search</button>
    </div>
    <div class="position-absolute start-0 end-0 d-none" style="background-color: white; border-radius: 0.25rem;" id="autocomplete-restaurant-food">
        <div id="autocomplete-restaurant-list" class="d-none"></div>
        <div id="autocomplete-food-list" class="d-none"></div>
        <div class="p-3 pt-1" style="border: 2px solid #d4d4d4; border-top: 0px;">
            <button class="btn btn-dark m-auto d-block" id="view-all-result" style="line-height: 30px; width: 99%" type="button">View All Results</button>
        </div>
    </div>
</form>