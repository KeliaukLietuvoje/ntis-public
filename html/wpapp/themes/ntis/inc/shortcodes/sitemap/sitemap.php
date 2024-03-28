<?php

class NTIS_Sitemap
{
    public function __construct()
    {
        add_shortcode('sitemap', [$this,'sitemap']);
    }
    public function sitemap($atts)
    {
        wp_enqueue_style('sitemap-shortcode-styles', NTIS_THEME_URL . '/inc/shortcodes/sitemap/sitemap.css', [], '1.0', 'all');

        $custom_posts_heading = isset($atts['custom_posts_heading']) && !empty($atts['custom_posts_heading']) ? sanitize_text_field($atts['custom_posts_heading']) : '';
        $custom_pages_heading = isset($atts['custom_pages_heading']) && !empty($atts['custom_pages_heading']) ? sanitize_text_field($atts['custom_pages_heading']) : '';
        $custom_archives_heading = isset($atts['custom_archives_heading']) && !empty($atts['custom_archives_heading']) ? sanitize_text_field($atts['custom_archives_heading']) : '';
        $custom_tax_lists_heading = isset($atts['custom_tax_lists_heading']) && !empty($atts['custom_tax_lists_heading']) ? sanitize_text_field($atts['custom_tax_lists_heading']) : '';

        $custom_tags_heading = isset($atts['custom_tags_heading']) && !empty($atts['custom_tags_heading']) ? sanitize_text_field($atts['custom_tags_heading']) : '';
        $custom_categories_heading = isset($atts['custom_categories_heading']) && !empty($atts['custom_categories_heading']) ? sanitize_text_field($atts['custom_categories_heading']) : '';
        $custom_cpt_heading = isset($atts['custom_cpt_heading']) && !empty($atts['custom_cpt_heading']) ? sanitize_text_field($atts['custom_cpt_heading']) : '';

        $type = isset($atts['type']) ? sanitize_text_field($atts['type']) : '';
        $is_title_displayed = isset($atts['display_title']) && $atts['display_title'] == 'false' ? false : true;
        $is_get_only_private = isset($atts['only_private']) && $atts['only_private'] == 'true' ? true : false;
        $display_nofollow = isset($atts['nofollow']) && $atts['nofollow'] == 'false' ? false : true;
        $exclude_pages = isset($atts['exclude']) && !empty($atts['exclude']) ? $this->keep_numeric_or_coma($atts['exclude']) : '';
        $exclude_cpt = isset($atts['exclude_cpt']) && !empty($atts['exclude_cpt']) ? sanitize_text_field($atts['exclude_cpt']) : '';
        $exclude_taxonomy = isset($atts['exclude_taxonomy']) && !empty($atts['exclude_taxonomy']) ? sanitize_text_field($atts['exclude_taxonomy']) : '';
        $sort  = isset($atts['sort']) ? sanitize_text_field($atts['sort']) : null;
        $order = isset($atts['order']) ? sanitize_text_field($atts['order']) : null;

        if(isset($atts['exclude_password_protected']) && $atts['exclude_password_protected'] == 'true') {
            global $wpdb;
            $sql = 'SELECT ID FROM '.$wpdb->posts.' WHERE post_status = \'publish\' AND post_password <> \'\' ';
            $password_pages = $wpdb->get_col($sql);
            if (!empty($password_pages)) {
                $exclude = implode(',', $password_pages);
                if (!empty($exclude_pages)) {
                    $exclude_pages .= ','.$exclude_pages;
                } else {
                    $exclude_pages = $exclude;
                }
            }
        }

        switch ($type) {
            case 'page':
                return $this->get_pages($custom_pages_heading, $is_title_displayed, $is_get_only_private, $display_nofollow, $exclude_pages, $sort);
                break;
            case 'post':
                return $this->get_posts($custom_posts_heading, $is_title_displayed, $display_nofollow, $display_post_only_once, $is_category_title_wording_displayed, $exclude_pages, $sort, $sort, $order);
                break;
            case 'archive':
                return $this->get_archives($custom_archives_heading, $is_title_displayed, $display_nofollow);
                break;
            case 'category':
                return $this->get_categories($custom_categories_heading, $is_title_displayed, $display_nofollow, $sort);
                break;
            case 'tag':
                return $this->get_tags($custom_tags_heading, $is_title_displayed, $display_nofollow);
                break;
            case '':
                break;
            default:
                $cpt = get_post_type_object($type);
                if (!empty($cpt)) {
                    return $this->get_items($is_title_displayed, $display_nofollow, $cpt, $type, $exclude_pages, $sort);
                }
                $taxonomy_obj = get_taxonomy($type);
                if (!empty($taxonomy_obj)) {
                    return $this->get_tax_items($custom_tax_items_heading, $is_title_displayed, $display_nofollow, $taxonomy_obj, $exclude_pages);
                }
        }
        $return = '';
        $return .= $this->get_pages($custom_pages_heading, $is_title_displayed, $is_get_only_private, $display_nofollow, $exclude_pages, $sort);
        $return .= $this->get_posts($custom_posts_heading, $is_title_displayed, $is_get_only_private, $display_nofollow, $exclude_pages, $sort);
        $return .= $this->get_cpt_lists($custom_cpt_heading, $is_title_displayed, $display_nofollow, $exclude_cpt, $exclude_pages);
        $return .= $this->get_tax_lists($custom_tax_lists_heading, $is_title_displayed, $display_nofollow, $exclude_taxonomy);
        $return .= $this->get_archives($custom_archives_heading, $is_title_displayed, $display_nofollow);
        return $return;
    }
    private function keep_numeric_or_coma($str = '')
    {
        return preg_replace('/[^,0-9]/', '', $str);
    }
    public function get_pages($custom_heading = '', $is_title_displayed = true, $is_get_only_private = false, $display_nofollow = false, $exclude_pages = array(), $sort = null)
    {
        $return = '';

        $args = array();
        $args['title_li'] = '';
        $args['echo']     = '0';

        if ($sort !== null) {
            $args['sort_column'] = $sort;
        }
        if (!empty($exclude_pages)) {
            $args['exclude'] = $exclude_pages;
        }
        if ($is_get_only_private == true) {
            $args['post_status'] = 'private';
        }
        $list_pages = wp_list_pages($args);
        if (empty($list_pages)) {
            return '';
        }
        if ($is_title_displayed == true) {
            $return .= '<h2 class="pages-title">'.(empty($custom_heading) ? esc_html__('Puslapiai', 'tip') : esc_attr($custom_heading)).'</h2>'."\n";
        }
        $return .= '<ul class="pages-list">'."\n";
        $return .= $list_pages;
        $return .= '</ul>'."\n";
        return $return;
    }

    public function get_posts(
        $custom_heading = '',
        $is_title_displayed = true,
        $display_nofollow = false,
        $display_post_only_once = true,
        $is_category_title_wording_displayed = true,
        $exclude_pages = array(),
        $sort_categories = null,
        $sort = null,
        $order = null
    ) {
        $return = '';
        $args = array();
        if ($sort_categories !== null) {
            $args['orderby'] = $sort_categories;
        }
        $cats = get_categories($args);
        if (empty($cats)) {
            return '';
        }
        $cats = $this->build_multi_array($cats);
        if ($is_title_displayed == true) {
            $return .= '<h2 class="posts-title">'.(empty($custom_heading) ? esc_html__('Įrašai pagal kategoriją', 'tip') : esc_attr($custom_heading)).'</h2>'."\n";
        }
        $return .= $this->multi_array_to_html(
            $cats,
            true,
            $display_post_only_once,
            $is_category_title_wording_displayed,
            $display_nofollow,
            $exclude_pages,
            $sort,
            $order
        );
        return $return;
    }

    public function get_categories($custom_heading = '', $is_title_displayed = true, $display_nofollow = false, $sort = null)
    {
        $return = '';
        $args = array();
        if ($sort !== null) {
            $args['orderby'] = $sort;
        }
        $cats = get_categories($args);
        if (empty($cats)) {
            return '';
        }
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');
        if ($is_title_displayed == true) {
            $return .= '<h2 class="categories-title">'.(empty($custom_heading) ? esc_html__('Kategorijos', 'tip') : esc_attr($custom_heading)).'</h2>'."\n";
        }
        $return .= '<ul class="wsp-categories-list">'."\n";
        foreach ($cats as $cat) {
            $return .= "\t".'<li><a href="'.get_category_link($cat->cat_ID).'"'.$attr_nofollow.'>'.$cat->name.'</a></li>'."\n";
        }
        $return .= '</ul>'."\n";
        return $return;
    }
    public function get_tags($custom_heading = '', $is_title_displayed = true, $display_nofollow = false)
    {
        $return = '';
        $args = array();
        $posttags = get_tags($args);
        if (empty($posttags)) {
            return '';
        }
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');
        if ($is_title_displayed == true) {
            $return .= '<h2 class="tags-title">'.(empty($custom_heading) ? esc_html__('Žymos', 'tip') : esc_attr($custom_heading)).'</h2>'."\n";
        }
        $return .= '<ul class="tags-list">'."\n";
        foreach($posttags as $tag) {
            $return .= "\t".'<li><a href="'.get_tag_link($tag->term_id).'"'.$attr_nofollow.'>'.$tag->name.'</a></li>'."\n";
        }
        $return .= '</ul>'."\n";
        return $return;
    }
    public function get_archives($custom_heading = '', $is_title_displayed = true, $display_nofollow = false)
    {
        $return = '';
        $args = array();
        $args['echo'] = 0;
        $list_archives = wp_get_archives($args);
        if (empty($list_archives)) {
            return '';
        }
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');
        if ($is_title_displayed == true) {
            $return .= '<h2 class="archives-title">'.(empty($custom_heading) ? esc_html__('Archyvai', 'tip') : esc_attr($custom_heading)).'</h2>'."\n";
        }
        $return .= '<ul class="archives-list">'."\n";
        $return .= $list_archives;
        $return .= '</ul>'."\n";
        return $return;
    }
    public function get_items($is_title_displayed = true, $display_nofollow = false, $cpt = '', $post_type = '', $exclude_pages = '', $sort = null)
    {
        $return = '';
        $list_pages = '';
        $args = array();
        $args['post_type'] = $post_type;
        $args['posts_per_page'] = 999999;
        $args['suppress_filters'] = 0;
        if (!empty($exclude_pages)) {
            $args['exclude'] = $exclude_pages;
        }
        if ($sort !== null) {
            $args['orderby'] = $sort;
        }
        $posts_cpt = get_posts($args);
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');
        if (!empty($posts_cpt)) {
            foreach($posts_cpt as $post_cpt) {
                $list_pages .= '<li><a href="'.get_permalink($post_cpt->ID).'"'.$attr_nofollow.'>'.$post_cpt->post_title.'</a></li>'."\n";
            }
        }
        if (!empty($list_pages)) {
            if ($is_title_displayed == true) {
                $return .= '<h2 class="'.$post_type.'s-title">' . $cpt->label . '</h2>'."\n";
            }
            $return .= '<ul class="'.$post_type.'s-list">'."\n";
            $return .= $list_pages;
            $return .= '</ul>'."\n";
        }
        return $return;
    }
    public function get_tax_lists($custom_heading = '', $is_title_displayed = true, $display_nofollow = false, $exclude_taxonomy = '')
    {
        $return = '';

        $args = array(
            'public'   => true,
            '_builtin' => false
            );
        $taxonomies_names = get_taxonomies($args);
        if (empty($taxonomies_names)) {
            return '';
        }

        if (!empty($custom_heading)) {
            $return .= '<h2 class="tax-title">'.esc_attr($custom_heading).'</h2>'."\n";
        }

        foreach ($taxonomies_names as $taxonomy_name) {
            $taxonomy_obj = get_taxonomy($taxonomy_name);
            if (empty($exclude_taxonomy)) {
                $return .= $this->get_tax_items($is_title_displayed, $display_nofollow, $taxonomy_obj, $exclude_taxonomy);
            }
        }
        return $return;
    }
    public function get_tax_items($is_title_displayed = true, $display_nofollow = false, $taxonomy_obj = null, $exclude_pages = '')
    {
        $return = '';
        $list_pages = '';
        $taxonomy_name = $taxonomy_obj->name;
        $taxonomy_label = $taxonomy_obj->label;
        $taxonomies = array( $taxonomy_name );
        $args = array();
        $terms = get_terms($taxonomies, $args);
        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');
        if (!empty($terms)) {
            foreach($terms as $terms_obj) {
                $list_pages .= '<li><a href="'.get_term_link($terms_obj).'"'.$attr_nofollow.'>'.$terms_obj->name.'</a></li>'."\n";
            }
        }
        if (!empty($list_pages)) {
            if ($is_title_displayed == true) {
                $return .= '<h2 class="'.$taxonomy_name.'s-title">' . $taxonomy_label . '</h2>'."\n";
            }
            $return .= '<ul class="'.$taxonomy_name.'s-list">'."\n";
            $return .= $list_pages;
            $return .= '</ul>'."\n";
        }
        return $return;
    }
    public function display_posts_by_cat($cat_id, $display_post_only_once = true, $display_nofollow = false, $exclude_pages = array(), $sort = null, $order = null)
    {

        global $the_post_id;

        $html = '';

        $args = array();
        $args['numberposts'] = 999999;
        $args['cat'] = $cat_id;

        if (!empty($exclude_pages)) {
            $args['exclude'] = $exclude_pages;
        }

        if ($sort !== null) {
            $args['orderby'] = $sort;
        }
        if ($order !== null) {
            $args['order'] = $order;
        }

        $the_posts = get_posts($args);

        if (empty($the_posts)) {
            return '';
        }

        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');
        $posts_by_category = sprintf(__('<a href="{permalink}"%1$s>{title}</a> ({year}-{monthnum}-{day})', 'tip'), $attr_nofollow);
        $posts_by_category = $this->esc_html_tags($posts_by_category);
        foreach ($the_posts as $the_post) {
            $get_category = get_the_category($the_post->ID);
            if ($display_post_only_once == false || ($display_post_only_once == true && $get_category[0]->cat_ID == $cat_id)) {
                $the_post_id = $the_post->ID;
                $title = get_the_title($the_post_id);
                $permalink = get_permalink($the_post_id);
                $year = get_the_time('Y', $the_post_id);
                $monthnum = get_the_time('m', $the_post_id);
                $day = get_the_time('d', $the_post_id);
                $html .= "\t\t".'<li class="post">'.str_replace(['{permalink}','{title}','{monthnum}','{day}','{year}'], [$permalink, $title, $monthnum, $day, $year], $posts_by_category).'</li>'."\n";
            }
        }

        return $html;
    }
    public function get_cpt_lists($custom_heading = '', $is_title_displayed = true, $display_nofollow = false, $exclude_cpt = '', $exclude_pages = '')
    {

        $return = '';
        $args = array(
            'public'   => true,
            '_builtin' => false
        );
        $post_types = get_post_types($args, 'names');
        if (empty($post_types)) {
            return '';
        }

        if (!empty($custom_heading)) {
            $return .= '<h2 class="cpt-title">'.esc_attr($custom_heading).'</h2>'."\n";
        }
        $exclude_cpt = is_array($exclude_cpt) ? $exclude_cpt : explode(',', $exclude_cpt);

        foreach ($post_types as $post_type) {
            if(!in_array($post_type, $exclude_cpt)) {
                $cpt = get_post_type_object($post_type);
                $return .= $this->get_items($is_title_displayed, $display_nofollow, $cpt, $post_type, $exclude_pages);
            }
        }
        return $return;
    }
    public function build_multi_array(array $arr = array(), $parent = 0)
    {
        if (empty($arr)) {
            return array();
        }
        $pages = array();
        foreach($arr as $k => $page) {
            if ($page->parent == $parent) {
                $page->sub = isset($page->sub) ? $page->sub : $this->build_multi_array($arr, $page->cat_ID);
                $pages[] = $page;
            }
        }

        return $pages;
    }
    public function multi_array_to_html(
        array $nav = array(),
        $useUL = true,
        $display_post_only_once = true,
        $is_category_title_wording_displayed = true,
        $display_nofollow = false,
        $exclude_pages = array(),
        $sort = null,
        $order = null
    ) {

        if (empty($nav)) {
            return '';
        }

        $html = '';
        if ($useUL === true) {
            $html .= '<ul class="posts-list">'."\n";
        }

        $attr_nofollow = ($display_nofollow == true ? ' rel="nofollow"' : '');

        foreach ($nav as $page) {
            $category_link = '<a href="'.get_category_link($page->cat_ID).'"'.$attr_nofollow.'>'.esc_html($page->name).'</a>';
            if ($is_category_title_wording_displayed) {
                $category_link_display = esc_html__('Category: ', 'tip').$category_link;
            } else {
                $category_link_display = $category_link;
            }
            $html .= "\t".'<li><strong class="category-title">'.$category_link_display.'</strong>'."\n";

            $post_by_cat = $this->display_posts_by_cat($page->cat_ID, $display_post_only_once, $display_nofollow, $exclude_pages, $sort, $order);

            $category_recursive = '';
            if (!empty($page->sub)) {
                $category_recursive = $this->multi_array_to_html(
                    $page->sub,
                    false,
                    $display_post_only_once,
                    $is_category_title_wording_displayed,
                    $display_nofollow,
                    $exclude_pages,
                    $sort,
                    $order
                );
            }

            if (!empty($post_by_cat) || !empty($category_recursive)) {
                $html .= '<ul class="posts-list">';
            }
            if (!empty($post_by_cat)) {
                $html .= $post_by_cat;
            }
            if (!empty($category_recursive)) {
                $html .= $category_recursive;
            }
            if (!empty($post_by_cat) || !empty($category_recursive)) {
                $html .= '</ul>';
            }

            $html .= '</li>'."\n";
        }

        if ($useUL === true) {
            $html .= '</ul>'."\n";
        }
        return $html;
    }


    public function add_no_follow_to_links($output)
    {
        return str_replace('<a href=', '<a rel="nofollow" href=', $output);
    }
    private function esc_html_tags($str = '')
    {
        $str = strip_tags($str, '<a><br><strong><b><em><i><ul><li><h1><h2><h3><h4><h5><h6><p>');
        $arr_tag_a = array(
            'href' => array(),
            'title' => array(),
            'class' => array(),
            'style' => array()
        );
        $arr_tag_usual = array(
            'title' => array(),
            'class' => array(),
            'style' => array()
        );
        $arr = array(
            'a'  => $arr_tag_a,
            'br' => $arr_tag_usual,
            'strong' => $arr_tag_usual,
            'b'  => $arr_tag_usual,
            'em' => $arr_tag_usual,
            'i'  => $arr_tag_usual,
            'ul' => $arr_tag_usual,
            'li' => $arr_tag_usual,
            'h1' => $arr_tag_usual,
            'h2' => $arr_tag_usual,
            'h3' => $arr_tag_usual,
            'h4' => $arr_tag_usual,
            'h5' => $arr_tag_usual,
            'h6' => $arr_tag_usual,
            'p' => $arr_tag_usual,
        );
        return wp_kses($str, $arr);
    }
}
new NTIS_Sitemap();
