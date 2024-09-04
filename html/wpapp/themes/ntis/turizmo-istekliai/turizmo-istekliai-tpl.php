<?php
/*
  Template Name: Turizmo išteklių sąrašas
*/
function not_found()
{
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
    exit();
}

try {
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
    } else {
        $current_lang = 'lt';
    }

    $object_id = get_query_var('object_id');
    if (!empty($object_id)) {
        $rest_url = NTIS_API_URL.'/public/forms/'.$object_id;
        $response = wp_remote_get(
            $rest_url,
            [
            'timeout'     => 120,
            'httpversion' => CURL_HTTP_VERSION_1_1,
            'sslverify' => WP_DEBUG ? false : true,
            'headers' => [
            'Accept' => 'application/json'
            ]
        ]
        );
        if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
            $response = (array)json_decode($response['body']);
            if (json_last_error() === JSON_ERROR_NONE) {
                get_header();

                $item = $response;
                $title = ($current_lang == 'lt') ? $item['nameLt'] : $item['nameEn'];
                $desc = ($current_lang == 'lt') ? $item['descriptionLt'] : $item['descriptionEn'];
                $url = ($current_lang == 'lt') ? $item['urlLt'] : $item['urlEn'];
                $url = NTIS_Tourism_Resources::fix_url($url);

                if (!empty($item['photos'])) {
                    $main_photo = $item['photos'][0]->url;
                } else {
                    $main_photo = "https://images.pexels.com/photos/1315891/pexels-photo-1315891.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2";
                }

                $season_labels = ['SUMMER' => __('Vasara', 'ntis'), 'AUTUMN' => __('Ruduo', 'ntis'), 'WINTER' => __('Žiema', 'ntis'), 'SPRING' => __('Pavasaris', 'ntis')];
                if (count($item['seasons']) == 4) {
                    $season_label = __('Visus metus', 'ntis');
                } else {
                    $season_label = '';
                    foreach ($item['seasons'] as $season) {
                        $season_label .= $season_labels[$season].', ';
                    }
                    $season_label = rtrim($season_label, ', ');
                }
                ?>
                <div class="tic-place">
                    <div class="tic-place__hero" style="background-image:url(<?php echo $main_photo;?>);">
                        <div class="tic-place__hero-container">
                        <div class="tic-place__hero-column">
                            <h1><?php echo $title;?></h1>
                            <?php if (!empty($item['categories'])) { ?>
                            <div class="tic-place__categories">
                                <?php foreach ($item['categories'] as $category) { ?>
                                <span class="tic-place__category"><?php echo $category;?></span>
                                <?php } ?>
                                <?php if (!empty($item['subCategories'])) { ?>
                                <?php foreach ($item['subCategories'] as $category) { ?>
                                <span class="tic-place__category"><?php echo $category;?></span>
                                <?php } ?>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                        </div>
                    </div>


                    <div class="tic-place__content single">
                        <div class="tic-place__column">
                        <div class="tic-place__details">
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7V5a2 2 0 0 1 2-2h2" />
                                <path d="M17 3h2a2 2 0 0 1 2 2v2" />
                                <path d="M21 17v2a2 2 0 0 1-2 2h-2" />
                                <path d="M7 21H5a2 2 0 0 1-2-2v-2" />
                                <path d="M7 8h8" />
                                <path d="M7 12h10" />
                                <path d="M7 16h6" />
                                </svg>
                            <div class="tic-place__detail__desc"><span><?php _e('ID', 'ntis');?></span><?php echo $item['id'];?></div>
                            </div>
                            
                            <!-- <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21" />
                                <line x1="9" x2="9" y1="3" y2="18" />
                                <line x1="15" x2="15" y1="6" y2="21" />
                                </svg>
                            <div class="tic-place__detail__desc"><span>Etnoregionas</span>Dzūkijos</div>
                            </div> -->

                            <?php if (!empty($season_label)) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 9a3 3 0 1 0 0 6" />
                                <path d="M2 12h1" />
                                <path d="M14 21V3" />
                                <path d="M10 4V3" />
                                <path d="M10 21v-1" />
                                <path d="m3.64 18.36.7-.7" />
                                <path d="m4.34 6.34-.7-.7" />
                                <path d="M14 12h8" />
                                <path d="m17 4-3 3" />
                                <path d="m14 17 3 3" />
                                <path d="m21 15-3-3 3-3" />
                                </svg>
                            <div class="tic-place__detail__desc"><span><?php _e('Sezoniškumas', 'ntis');?></span>
                                <?php echo $season_label;?>
                            </div>
                            </div>
                            <?php } ?>

                            <?php if (!empty($item['visitDuration'])) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16.5 12" />
                                </svg>
                            <div class="tic-place__detail__desc"><span><?php _e('Lankymo trukmė', 'ntis');?></span>
                            <?php if ($item['visitDuration']->isAllDay) { ?>
                                <?php _e('Visa diena', 'ntis');?>
                                <?php } else { ?>
                            <?php echo $item['visitDuration']->from;?> - <?php echo $item['visitDuration']->to;?> <?php _e('val.', 'ntis');?><?php } ?></div>
                            </div>
                            <?php } ?>

                            <!-- <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 16v-4" />
                                <path d="M12 8h.01" />
                                </svg>
                            <div class="tic-place__detail__desc"><span><?php _e('TIC', 'ntis');?></span>Alytaus TIC</div>
                            </div> -->

                            <?php if (isset($item['isPaid'])) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 10h12" />
                                <path d="M4 14h9" />
                                <path d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2" />
                                </svg>
                            <div class="tic-place__detail__desc"><span><?php _e('Kaina', 'ntis');?></span> <?php echo ($item['isPaid'] == true) ? __('Mokama', 'ntis') : __('Nemokama', 'ntis');?></div>
                            </div>
                            <?php } ?>
                        </div>

                        <?php if (!empty($item['additionalInfos'])) {?>   
                        <div class="tic-place__additional-details">
                            <div class="tic-place__title">
                            <?php _e('Papildomos paslaugos', 'ntis');?>
                            </div>
                            <div class="tic-place__grid">
                                <?php foreach ($item['additionalInfos'] as $info) {?>
                                    <?php if ($info == __('Tinkama su vaikais', 'ntis')) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 12h.01" />
                                    <path d="M15 12h.01" />
                                    <path d="M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5" />
                                    <path
                                    d="M19 6.3a9 9 0 0 1 1.8 3.9 2 2 0 0 1 0 3.6 9 9 0 0 1-17.6 0 2 2 0 0 1 0-3.6A9 9 0 0 1 12 3c2 0 3.5 1.1 3.5 2.5s-.9 2.5-2 2.5c-.8 0-1.5-.4-1.5-1" />
                                </svg>
                                <?php echo $info;?>
                            </div>
                            <?php } ?>
                            <?php if ($info == __('Galimybė atsiskaityti kortele', 'ntis')) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="5" rx="2" />
                                    <line x1="2" x2="22" y1="10" y2="10" />
                                </svg>
                                <?php echo $info;?>
                            </div>
                            <?php } ?>
                            <?php if ($info == __('Būtina rezervacija iš anksto', 'ntis')) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 20a1 1 0 0 1-1-1v-1a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v1a1 1 0 0 1-1 1Z" />
                                    <path d="M20 16a8 8 0 1 0-16 0" />
                                    <path d="M12 4v4" />
                                    <path d="M10 4h4" />
                                </svg>
                                <?php echo $info;?>
                            </div>
                            <?php } ?>
                            <?php if ($info == __('Galimybė lankytis neįgaliesiems', 'ntis')) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="16" cy="4" r="1" />
                                    <path d="m18 19 1-7-6 1" />
                                    <path d="m5 8 3-3 5.5 3-2.36 3.5" />
                                    <path d="M4.24 14.5a5 5 0 0 0 6.88 6" />
                                    <path d="M13.76 17.5a5 5 0 0 0-6.88-6" />
                                </svg>
                                <?php echo $info;?>
                            </div>
                            <?php } ?>
                            <?php if ($info == __('Galimybė atsivesti augintinius', 'ntis')) {?>
                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="4" r="2" />
                                    <circle cx="18" cy="8" r="2" />
                                    <circle cx="20" cy="16" r="2" />
                                    <path
                                    d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z" />
                                </svg>
                                <?php echo $info;?>
                            </div>
                            <?php } ?>
                            
                        <?php } ?>
                        </div>
                        </div>
                        <?php } ?>
                        <div class="tic-place__desc">
                            <?php echo $desc;?>
                        </div>
                        </div>
                        <div class="tic-place__column">
                            <?php if (!empty($url)) {?>
                        <a class="tic-place__url" href="<?php echo esc_attr($url);?>" target="_blank"><?php _e('Aplankykite svetainę', 'ntis');?>
                            <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 7h10v10" />
                            <path d="M7 17 17 7" />
                            </svg>
                        </a>
                        <?php } ?>
                        <?php if (!empty($item['photos'])) {?>
                        <div class="tic-place__pic">
                            <?php foreach ($item['photos'] as $photo) {?>
                            <img
                            src="<?php echo esc_attr($photo->url);?>" alt="<?php echo esc_attr($photo->name);?> <?php echo isset($photo->author) ? ', ©'.esc_attr($photo->author) : '';?>" />
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <div class="tic-place__map" data-geom="<?php echo esc_attr($item['geom']);?>">
                            <div id="tic-place__map"></div>
                        </div>
                        </div>
                    </div>
                <?php
                get_footer();
            }
        } else {
            not_found();
        }
    } else {
        
        $page_size = isset($_REQUEST['limit']) ? absint($_REQUEST['limit']) : 12;
        $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
        $rest_url = NTIS_API_URL.'/public/forms';
        $params = array(
            'page' => $paged,
            'pageSize' => $page_size,
            'sort' => '-createdAt',
        );

        if (isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])) {
            $params['title'] = isset($_REQUEST['filter']['title']) ? $_REQUEST['filter']['title'] : '';
            $params['price'] = isset($_REQUEST['filter']['price']) ? $_REQUEST['filter']['price'] : '';
            $params['category'] = isset($_REQUEST['filter']['category']) ? $_REQUEST['filter']['category'] : [];
            $params['subcategory'] = isset($_REQUEST['filter']['subcategory']) ? $_REQUEST['filter']['subcategory'] : [];
            $params['additional'] = isset($_REQUEST['filter']['additional']) ? $_REQUEST['filter']['additional'] : [];
        }

        $query_string = http_build_query($params);
        $rest_url = $rest_url . '?' . $query_string;

        $response = wp_remote_get(
            $rest_url,
            [
            'timeout'     => 120,
            'httpversion' => CURL_HTTP_VERSION_1_1,
            'sslverify' => WP_DEBUG ? false : true,
            'headers' => [
            'Accept' => 'application/json'
            ]
        ]
        );


        if ((!is_wp_error($response)) && (200 === wp_remote_retrieve_response_code($response))) {
            $response = (array)json_decode($response['body']);
            if (json_last_error() === JSON_ERROR_NONE) {

                $max_num_pages = $response['totalPages'];
                get_header();
                the_content();
                ?>
    <div class="tic-place">

        <div class="tic-place__content">
            <div class="tic-place__filters">
                <form method="GET" id="turizmo-istekliai-filter-form" action="<?php echo get_the_permalink();?>">
                    <div class="tic-place__filter">
                        <input type="checkbox" id="section1">
                        <label for="section1"><?php _e('Filtruoti pagal raktažodį', 'ntis');?></label>
                        <div class="content">
                            <label for="filter-title"><?php _e('Raktažodis', 'ntis');?></label>
                            <input type="text" id="filter-title" name="filter[title]" value="<?php echo isset($params['title']) ? sanitize_text_field($params['title']) : ''; ?>">
                        </div>
                    </div>

                    <div class="tic-place__filter">
                        <input type="checkbox" id="section2">
                        <label for="section2"><?php _e('Kaina', 'ntis');?></label>
                        <div class="content">
                            <div class="nested-checkbox"><input type="checkbox" name="filter[price][]" id="filter-price-paid" value="1" <?php checked($params['price'] ?? '', 1, true);?>><label for="filter-price-paid"><?php _e('Mokama', 'ntis');?></label></div>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[price][]" id="filter-price-free" value="0" <?php checked($params['price'] ?? '', 0, true);?>><label for="filter-price-free"><?php _e('Nemokama', 'ntis');?></label></div>
                        </div>
                    </div>

                    <div class="tic-place__filter">
                        <input type="checkbox" id="section3">
                        <label for="section3"><?php _e('Kategorija', 'ntis');?></label>
                        <div class="content">
                            <div class="nested-checkbox"><input type="checkbox" name="filter[category][]" id="filter-category-1" value="1" <?php checked(in_array(1, $params['category'] ?? []), true, true);?>><label for="filter-category-1"><?php _e('Turai', 'ntis');?></label></div>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[category][]" id="filter-category-2" value="2" <?php checked(in_array(2, $params['category'] ?? []), true, true);?>><label for="filter-category-2"><?php _e('Verslo turizmas', 'ntis');?></label></div>
                        </div>
                    </div>
                    <div class="tic-place__filter">
                        <input type="checkbox" id="section4">
                        <label for="section4"><?php _e('Subkategorija', 'ntis');?></label>
                        <div class="content">
                            <div class="nested-checkbox"><input type="checkbox" name="filter[subcategory][]" id="filter-subcategory-1" value="1" <?php checked(in_array(1, $params['subcategory'] ?? []), true, true);?>><label for="filter-subcategory-1"><?php _e('Parkai ir sodai', 'ntis');?></label></div>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[subcategory][]" id="filter-subcategory-2" value="2" <?php checked(in_array(2, $params['subcategory'] ?? []), true, true);?>><label for="filter-subcategory-2"><?php _e('Piliakalniai', 'ntis');?></label></div>
                        </div>
                    </div>                    
                    <div class="tic-place__filter">
                        <input type="checkbox" id="section5">
                        <label for="section5"><?php _e('Papildoma informacija', 'ntis');?></label>
                        <div class="content">
                            <div class="nested-checkbox"><input type="checkbox" name="filter[additional][]" id="filter-additional-1" value="1" <?php checked(in_array(1, $params['additional'] ?? []), true, true);?>><label for="filter-additional-1"><?php _e('Tinkama su vaikais', 'ntis');?></label></div>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[additional][]" id="filter-additional-2" value="2" <?php checked(in_array(2, $params['additional'] ?? []), true, true);?>><label for="filter-additional-2"><?php _e('Galimybė atsiskaityti kortele', 'ntis');?></label></div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="tic-place__column">
                <div class="tic-place__options">
                    <div></div>
                    <?php /*
                <button class="download-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" x2="12" y1="15" y2="3" />
                    </svg>
                    <?php _e('Atsisiųsti duomenis', 'ntis');?></button>
                    */?>
                    <div class="view-options">
                        <button class="icon icon-list active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" x2="20" y1="12" y2="12" />
                                <line x1="4" x2="20" y1="6" y2="6" />
                                <line x1="4" x2="20" y1="18" y2="18" />
                            </svg>
                            <?php _e('Sąrašas', 'ntis');?>
                        </button>
                        <button class="icon icon-grid">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="7" x="3" y="3" rx="1" />
                                <rect width="7" height="7" x="14" y="3" rx="1" />
                                <rect width="7" height="7" x="14" y="14" rx="1" />
                                <rect width="7" height="7" x="3" y="14" rx="1" />
                            </svg>
                            <?php _e('Tinklelis', 'ntis');?>
                        </button>
                    </div>
                </div>

                <?php
                if (isset($response['rows'])) { ?>
                <ul class="tic-place__places list">
                    <?php  foreach ($response['rows'] as $item) {
                        $title = ($current_lang == 'lt') ? $item->nameLt : $item->nameEn;
                        ?>
                    <li class="list-item">
                        <?php if (isset($item->photos[0]) && $item->photos[0]->url != '') {?>
                        <img src="<?php echo $item->photos[0]->url;?>"
                            alt="<?php echo esc_attr($item->photos[0]->name);?><?php echo isset($item->photos[0]->author) ? ' , ©'.esc_attr($item->photos[0]->author) : '';?>" />
                        <?php } else { ?>
                        <img src="<?php echo NTIS_THEME_URL;?>/assets/images/placeholder.png"
                            alt="<?php _e('Trūksta paveikslėlio');?>" />
                        <?php } ?>
                        <div class="tic-place__detail">
                            <span class="tic-place__detail__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M4 10h12" />
                                    <path d="M4 14h9" />
                                    <path
                                        d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2" />
                                </svg>
                            </span>
                            <div class="tic-place__detail__desc">
                                <?php echo $item->isPaid == true ? __('Mokama', 'ntis') : __('Nemokama', 'ntis');?></div>
                        </div>
                        <a href="<?php echo get_the_permalink();?><?php echo sanitize_title($title);?>/<?php echo $item->id;?>"
                            class="item-title"><?php echo $title;?></a>
                        <?php if (!empty($item->categories)) { ?>
                        <div class="tic-place__categories">
                            <?php foreach ($item->categories as $category) { ?>
                            <span class="tic-place__category"><?php echo $category;?></span>
                            <?php } ?>
                            <?php if (!empty($item->subCategories)) { ?>
                            <?php foreach ($item->subCategories as $category) { ?>
                            <span class="tic-place__category"><?php echo $category;?></span>
                            <?php } ?>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
                <?php echo NTIS_Tourism_Resources::loop_pagination($paged, $max_num_pages);?>
                <?php } ?>

            </div>

        </div>
    </div>
    <?php
                        get_footer();
            }
        }
    }

} catch (Exception $ex) {
    not_found();
}
