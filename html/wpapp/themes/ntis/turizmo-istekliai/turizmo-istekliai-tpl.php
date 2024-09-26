<?php
use proj4php\Point as ProjPoint;
use proj4php\Proj;
use proj4php\Proj4php;
use Brick\Geo\Point;
use Brick\Geo\IO\EWKBReader;

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

function request_error($error_code = 400, $error_title = '', $error_message = '')
{
    get_header();?>
<div id="content" class="error-message site-content-<?php echo $error_code;?>" role="main">
    <h1 class="title"><?php echo !empty($error_title) ? $error_title : __('Informacijos nėra', 'ntis'); ?></h1>
    <div class="content">
        <p>
            <?php echo !empty($error_message) ? $error_message : __('Blogai suformuota užklausa arba rezultatų nėra.', 'ntis'); ?>
        </p>
    </div><!-- .page-content -->
</div><!-- #content -->
<?php
    get_footer();
    exit();
}


try {
    $current_lang = (function_exists('pll_current_language')) ? pll_current_language() : 'lt';
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
                if (isset($item['seasons']) && count($item['seasons']) == 4) {
                    $season_label = __('Visus metus', 'ntis');
                } else {
                    $season_label = '';
                    if (isset($item['seasons'])) {
                        foreach ($item['seasons'] as $season) {
                            $season_label .= $season_labels[$season].', ';
                        }
                        $season_label = rtrim($season_label, ', ');
                    }
                }
                ?>
<div class="tic-place">
    <div class="tic-place__hero" style="background-image:url(<?php echo $main_photo;?>);">
        <div class="tic-place__hero-container">
            <div class="tic-place__hero-column">
                <div class="tic-place__hero__back">
                    <a href="<?php echo get_the_permalink();?>"><svg xmlns="http://www.w3.org/2000/svg" width="16"
                            height="16" viewBox="0 0 24 24" fill="none" stroke="#003c3a" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left">
                            <path d="m12 19-7-7 7-7" />
                            <path d="M19 12H5" /></svg> <?php _e('Grįžti atgal', 'ntis');?></a>
                </div>
                <h1><?php echo $title;?></h1>
                <?php if (!empty($item['categories'])) { ?>
                <div class="tic-place__categories tags-wrapper">
                    <?php foreach ($item['categories'] as $category) {
                        $category_name = ($current_lang == 'lt') ? $category->name : $category->nameEn; ?>
                    <span class="tic-place__category tag"><?php echo $category_name;?></span>
                    <?php } ?>
                    <?php if (!empty($item['subCategories'])) { ?>
                    <?php foreach ($item['subCategories'] as $category) {
                        $category_name = ($current_lang == 'lt') ? $category->name : $category->nameEn;?>
                    <span class="tic-place__category tag"><?php echo $category_name;?></span>
                    <?php } ?>
                    <?php } ?>
                    <span class="tic-place__category more-button"><?php _e('...', 'ntis');?></span>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    

    <div class="tic-place__content single">
        <?php if(isset($item['isActive']) && $item['isActive']!=1){ ?>
        <div class="tic-is__inactive">
            <?php _e('Informuojame, kad šiuo metu šis objektas yra laikinai uždarytas.','ntis');?>
        </div>
        <?php } ?>
        <div class="tic-place__column">
        
            <div class="tic-place__details">
                <div class="tic-place__detail">
                    <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M3 7V5a2 2 0 0 1 2-2h2" />
                        <path d="M17 3h2a2 2 0 0 1 2 2v2" />
                        <path d="M21 17v2a2 2 0 0 1-2 2h-2" />
                        <path d="M7 21H5a2 2 0 0 1-2-2v-2" />
                        <path d="M7 8h8" />
                        <path d="M7 12h10" />
                        <path d="M7 16h6" />
                    </svg>
                    <div class="tic-place__detail__desc"><span><?php _e('ID', 'ntis');?></span><?php echo $item['id'];?>
                    </div>
                </div>

                <?php if (!empty($season_label)) {?>
                <div class="tic-place__detail">
                    <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
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

                <?php
                        if (!empty(get_object_vars($item['visitDuration']))) {?>
                <div class="tic-place__detail">
                    <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16.5 12" />
                    </svg>
                    <div class="tic-place__detail__desc"><span><?php _e('Lankymo trukmė', 'ntis');?></span>
                        <?php if (isset($item['visitDuration']->isAllDay)) { ?>
                        <?php _e('Visa diena', 'ntis');?>
                        <?php } else { ?>
                        <?php echo $item['visitDuration']->from;?> - <?php echo $item['visitDuration']->to;?>
                        <?php _e('val.', 'ntis');?><?php } ?></div>
                </div>
                <?php } ?>

                <?php if (!empty($item['tenant'])) { ?>
                <div class="tic-place__detail">
                    <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <div class="tic-place__detail__desc">
                        <span><?php _e('TIC', 'ntis');?></span><?php echo $item['tenant']->name;?></div>
                </div>
                <?php } ?>

                <?php if (isset($item['isPaid'])) {?>
                <div class="tic-place__detail">
                    <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M4 10h12" />
                        <path d="M4 14h9" />
                        <path d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2" />
                    </svg>
                    <div class="tic-place__detail__desc"><span><?php _e('Kaina', 'ntis');?></span>
                        <?php echo ($item['isPaid'] == true) ? __('Mokama', 'ntis') : __('Nemokama', 'ntis');?></div>
                </div>
                <?php } ?>

                <?php if (isset($item['isAdaptedForForeigners'])) {?>
                <div class="tic-place__detail">
                    <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-message-square">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                    <div class="tic-place__detail__desc"><span><?php _e('Paslauga anglų kalba', 'ntis');?></span>
                        <?php echo ($item['isAdaptedForForeigners'] == true) ? __('Teikiama', 'ntis') : __('Neteikiama', 'ntis');?>
                    </div>
                </div>
                <?php } ?>
            </div>

            <?php if (!empty($item['visitInfo'])) {?>
            <div class="tic-place__visitinfo-details">
                <div class="tic-place__detail">
                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" >
<circle cx="10.58" cy="3.54" r="1"/>
<polyline points="16.69 9.69 12.08 9.69 10.48 7.18 9.37 12.67 12.52 15.5 13.04 21.46"/>
<line x1="9.3" y1="15.61" x2="7.31" y2="21.46"/>
<line x1="15.84" y1="7.73" x2="15.84" y2="21.46"/>
<line x1="8.18" y1="6.74" x2="7.38" y2="10.72"/>
</svg>
                    <div class="tic-place__detail__desc"><span><?php _e('Ar objektas pritaikytas lankymui?', 'ntis');?></span><?php echo ($current_lang == 'lt') ? $item['visitInfo']->name : $item['visitInfo']->nameEn;?>
                    </div>
                </div>
                
            </div>
            <?php } ?>

            <?php if (!empty($item['additionalInfos'])) {?>
            <div class="tic-place__additional-details">
                <div class="tic-place__title">
                    <?php _e('Papildomos paslaugos', 'ntis');?>
                </div>
                <div class="tic-place__grid">
                    <?php foreach ($item['additionalInfos'] as $info) {?>
                    <?php $name = ($current_lang == 'lt') ? $info->name : $info->nameEn; ?>
                    <div class="tic-place__detail">
                        <img class="tic-place__detail__icon" src="<?php echo $info->icon;?>" alt="<?php echo esc_attr($name);?>">
                        <?php echo $name;?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            
            <div class="tic-place__desc">
                <?php echo wpautop($desc);?>
            </div>

            <?php if (!empty($item['photos'])) {?>
            <div class="swiper tic-swiper">
                <div class="tic-place__pic swiper-wrapper">
                    <?php foreach ($item['photos'] as $photo) {
                        $photo_name = $photo->name ?? '';
                        $photo_author = $photo->author ?? ''; ?>
                    <div class="swiper-slide"><a href="<?php echo esc_attr($photo->url);?>"
                            data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="6d1b18c"
                            data-elementor-lightbox-title="<?php echo !empty($photo_name) ? esc_attr($photo_name).', ' : '';?><?php echo isset($photo_author) ? '©'.esc_attr($photo_author) : '';?>">
                            <img src="<?php echo esc_attr($photo->url);?>"
                                alt="<?php echo !empty($photo_name) ? esc_attr($photo_name).', ' : '';?> <?php echo isset($photo_author) ? '©'.esc_attr($photo_author) : '';?>" />
                        </a></div>
                    <?php } ?>
                </div>
                <div class="swiper-bottom">
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-wrap">
                        <div class="swiper-button-prev btn btn-dark btn-outlined"></div>
                        <div class="swiper-button-next btn btn-dark btn-outlined"></div>
                    </div>
                </div>
            </div>
            <?php } ?>

        </div>
        <div class="tic-place__column">
            <?php if (!empty($url)) {?>
            <a class="tic-place__url" href="<?php echo esc_attr($url);?>"
                target="_blank"><?php _e('Aplankykite svetainę', 'ntis');?>
                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M7 7h10v10" />
                    <path d="M7 17 17 7" />
                </svg>
            </a>
            <?php } ?>


            <?php if (!empty($item['geom'])) {
                $reader = new EWKBReader();
                $point = $reader->read(hex2bin($item['geom']));
                $y = $point->y();
                $x = $point->x();

                $proj4 = new Proj4php();
                $projLKS = new Proj('EPSG:3346', $proj4);
                $projWGS = new Proj('EPSG:4326', $proj4);
                $pointLKS = new ProjPoint($x, $y, $projLKS);
                $pointWGS = $proj4->transform($projWGS, $pointLKS);

                $latitude = $pointWGS->y;
                $longitude = $pointWGS->x;
                ?>
            <div class="tic-place__map" data-lat="<?php echo $latitude;?>" data-lng="<?php echo $longitude;?>">
                <div id="tic-place__map"></div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php
                get_footer();
            }
        } else {
            request_error(200, __('Informacijos nėra', 'ntis'), __('Užklausa iš API negrąžino duomenų', 'ntis'));
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
            $filter_title = isset($_REQUEST['filter']['title']) ? $_REQUEST['filter']['title'] : '';
            $filter_price = isset($_REQUEST['filter']['price']) ? $_REQUEST['filter']['price'] : [];
            $filter_category = isset($_REQUEST['filter']['category']) ? $_REQUEST['filter']['category'] : [];
            $filter_subcategory = isset($_REQUEST['filter']['subcategory']) ? $_REQUEST['filter']['subcategory'] : [];
            $filter_additional = isset($_REQUEST['filter']['additional']) ? $_REQUEST['filter']['additional'] : [];

            if (!empty($filter_title)) {

                if ($current_lang == 'lt') {
                    $params['query[nameLt][$ilike]'] = '%'.sanitize_text_field($filter_title).'%';
                } else {
                    $params['query[nameEn][$ilike]'] = '%'.sanitize_text_field($filter_title).'%';
                }
            }

            if (!empty($filter_price)) {
                if (count($filter_price) == 1) {
                    $params['query[isPaid]'] = in_array('true', $filter_price) ? 'true' : 'false';
                }
            }

            if (!empty($filter_category)) {
                $params['query[categories][id][$in]'] = $filter_category;

            }
            if (!empty($filter_subcategory)) {
                $params['query[subCategories][id][$in]'] = $filter_subcategory;
            }

            if (!empty($filter_additional)) {
                $params['query[additionalInfos][id][$in]'] = $filter_additional;
            }

        } else {
            $filter_title = '';
            $filter_price = $filter_additional = $filter_subcategory = $filter_category = [];
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
                    <input type="hidden" name="action" value="get_tourism_resources">
                    <div class="tic-place__filter">
                        <input type="checkbox" id="section1">
                        <label for="section1"><?php _e('Filtruoti pagal raktažodį', 'ntis');?></label>
                        <div class="content">
                            <label for="filter-title"><?php _e('Raktažodis', 'ntis');?></label>
                            <input type="text" id="filter-title" name="filter[title]"
                                value="<?php echo isset($filter_title) ? sanitize_text_field($filter_title) : ''; ?>">
                        </div>
                    </div>

                    <div class="tic-place__filter">
                        <input type="checkbox" id="section2">
                        <label for="section2"><?php _e('Kaina', 'ntis');?></label>
                        <div class="content">
                            <div class="nested-checkbox"><input type="checkbox" name="filter[price][]"
                                    id="filter-price-paid" value="true"
                                    <?php checked(in_array('true', $filter_price), true, true);?>><label
                                    for="filter-price-paid"><?php _e('Mokama', 'ntis');?></label></div>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[price][]"
                                    id="filter-price-free" value="false"
                                    <?php checked(in_array('false', $filter_price), true, true);?>><label
                                    for="filter-price-free"><?php _e('Nemokama', 'ntis');?></label></div>
                        </div>
                    </div>
                    <?php $categories = NTIS_Tourism_Resources::fetch_endpoint('/categories/enum');?>
                    <?php if (!empty($categories)) {?>
                    <div class="tic-place__filter">
                        <input type="checkbox" id="section3">
                        <label for="section3"><?php _e('Kategorija', 'ntis');?></label>
                        <div class="content">
                            <?php echo NTIS_Tourism_Resources::generate_tree_category($current_lang, $filter_category, $filter_subcategory, $categories);?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php $additionalInfos = NTIS_Tourism_Resources::fetch_endpoint('/additionalInfos/enum');?>
                    <?php if (!empty($additionalInfos)) {?>
                    <div class="tic-place__filter">
                        <input type="checkbox" id="section5">
                        <label for="section5"><?php _e('Papildoma informacija', 'ntis');?></label>
                        <div class="content">
                            <?php foreach ($additionalInfos as $k => $additionalInfo) {
                                $v = $additionalInfo['name']; ?>
                            <div class="nested-checkbox"><input type="checkbox" name="filter[additional][]"
                                    id="filter-additional-<?php echo $k;?>" value="<?php echo $v;?>"
                                    <?php checked(in_array($v, $filter_additional ?? []), true, true);?>><label
                                    for="filter-additional-<?php echo $k;?>"><?php echo $v;?></label></div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </form>
            </div>

            <div class="tic-place__column">
                <div class="tic-place__options">
                    <div></div>
                    <div class="view-options">
                        <button class="icon icon-list active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="4" x2="20" y1="12" y2="12" />
                                <line x1="4" x2="20" y1="6" y2="6" />
                                <line x1="4" x2="20" y1="18" y2="18" />
                            </svg>
                            <?php _e('Sąrašas', 'ntis');?>
                        </button>
                        <button class="icon icon-grid">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect width="7" height="7" x="3" y="3" rx="1" />
                                <rect width="7" height="7" x="14" y="3" rx="1" />
                                <rect width="7" height="7" x="14" y="14" rx="1" />
                                <rect width="7" height="7" x="3" y="14" rx="1" />
                            </svg>
                            <?php _e('Tinklelis', 'ntis');?>
                        </button>
                    </div>
                </div>

                <div id="tic-wrapper">
                    <?php
                if (isset($response['rows']) && $response['total'] > 0) { ?>
                    <ul class="tic-place__places list">
                        <?php  foreach ($response['rows'] as $item) {
                            $title = ($current_lang == 'lt') ? $item->nameLt : $item->nameEn;
                            ?>
                        <li class="list-item">
                            <?php
                            $photo = $item->photos[0] ?? null;
                            $photoUrl = $photo->url ?? '';
                            $photoName = !empty($photo->name) ? $photo->name.', ' : '';
                            $photoAuthor = $photo->author ?? '';

                            if (!empty($photoUrl)) { ?>
                            <img src="<?php echo esc_url($photoUrl); ?>"
                                alt="<?php echo esc_attr($photoName); ?><?php echo !empty($photoAuthor) ? ' ©' . esc_attr($photoAuthor) : ''; ?>" />
                            <?php } else { ?>
                            <img src="<?php echo esc_url(NTIS_THEME_URL . '/assets/images/placeholder.png'); ?>"
                                alt="<?php _e('Trūksta paveikslėlio'); ?>" />
                            <?php } ?>

                            <div class="tic-place__detail">
                                <svg class="tic-place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="18"
                                    height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 10h12" />
                                    <path d="M4 14h9" />
                                    <path
                                        d="M19 6a7.7 7.7 0 0 0-5.2-2A7.9 7.9 0 0 0 6 12c0 4.4 3.5 8 7.8 8 2 0 3.8-.8 5.2-2" />
                                </svg>
                                <div class="tic-place__detail__desc">
                                    <?php echo $item->isPaid == true ? __('Mokama', 'ntis') : __('Nemokama', 'ntis');?>
                                </div>
                            </div>
                            <a href="<?php echo get_the_permalink();?><?php echo sanitize_title($title);?>/id:<?php echo $item->id;?>/"
                                class="item-title"><?php echo $title;?></a>
                            <?php if (!empty($item->categories)) { ?>
                            <div class="tic-place__categories tags-wrapper">
                                <?php foreach ($item->categories as $category) { ?>
                                <span
                                    class="tic-place__category tag"><?php echo ($current_lang == 'lt') ? $category->name : $category->nameEn;?></span>
                                <?php } ?>
                                <?php if (!empty($item->subCategories)) { ?>
                                <?php foreach ($item->subCategories as $category) { ?>
                                <span
                                    class="tic-place__category tag"><?php echo ($current_lang == 'lt') ? $category->name : $category->nameEn;?></span>
                                <?php } ?>
                                <?php } ?>
                                <span class="tic-place__category more-button"><?php _e('...', 'ntis');?></span>
                            </div>
                            <?php } ?>
                        </li>
                        <?php } ?>
                    </ul>
                    <?php echo NTIS_Tourism_Resources::loop_pagination($paged, $max_num_pages);?>
                </div>
                <?php } else { ?>
                <?php _e('Pagal pateiktus filtro kriterijus paieška rezultatų negrąžino.', 'ntis');?>
                <?php } ?>

            </div>

        </div>
    </div>
    <?php
                                get_footer();
            }
        } else {
            request_error(200, __('Informacijos nėra', 'ntis'), __('Užklausa iš API negrąžino duomenų', 'ntis'));
        }
    }

} catch (Exception $ex) {
    not_found();
}
