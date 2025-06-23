<?php


/**
 * Prepare context variables for theme templates
 * 
 * @param array $context Context variables
 * @return array Modified context
 */
function minimis_prepare_context($context = []) {
    // Basic site info
    $context['site_title'] = get_bloginfo('name');
    $context['site_description'] = get_bloginfo('description');
    $context['home_url'] = home_url();
    $context['shoppage'] = wc_get_page_permalink( 'shop' );
    
    // Current year for copyright
    $context['current_year'] = date('Y');
    
    // Primary menu
    if (has_nav_menu('primary')) {
        $context['primary_menu'] = wp_nav_menu([
            'theme_location' => 'primary',
            'container' => false,
            'echo' => false,
        ]);
    }

    $context['logo_center'] = get_template_directory_uri() . '/assets/logo_center.png';
    $context['logo_menu'] = get_template_directory_uri() . '/assets/logo_menu.png';
    
    // Social links
    $context['social_links'] = [
       'insta' => [
        'icon' => get_template_directory_uri() . '/assets/social/insta.png',
        'url' => 'https://www.instagram.com/minimisgr/',
        'label' => 'MiNiMiS Instagram'
       ],
       'tiktok' => [
        'icon' => get_template_directory_uri() . '/assets/social/tiktok.png',
        'url' => 'https://www.tiktok.com/@minimisgr',
        'label' => 'MiNiMiS TikTok'
       ],
       'yt' => [
        'icon' => get_template_directory_uri() . '/assets/social/yt.png',
        'url' => 'https://www.youtube.com/@minimisgr',
        'label' => 'MiNiMiS YouTube'
        ],
         'fb' => [
          'icon' => get_template_directory_uri() . '/assets/social/fb.png',
          'url' => 'https://www.facebook.com/minimisgr/',
          'label' => 'MiNiMiS Facebook'
         ],
        
        
    ];

    //check the language
    if (function_exists('icl_get_languages')) {

        //echo the language
        $context['languages'] = icl_get_languages('skip_missing=0&orderby=code');

        //get the current language
        $context['current_language'] = ICL_LANGUAGE_CODE;

        //if language is set to greek
        if ($context['current_language'] == 'el') {
            $context['lang'] = '';
            
            //wishlist url
            $context['wishlist_url'] = '/lista-epithymion';
            //cart url
            $context['cart_url'] = '/cart';

        } elseif ($context['current_language'] == 'en') {
            $context['lang'] = 'en';

            //wishlist url
            $context['wishlist_url'] = 'en/wishlist';
            //cart url
            $context['cart_url'] = 'en/cart';
        }

    }

    //main bar menu search, shopping cart and wishlist
    $context['mainbar_menu'] = [
        'search' => [
            'icon' => get_template_directory_uri() . '/assets/search.png',
            'url' => '#',
            'databs' => 'data-bs-toggle="offcanvas" data-bs-target="#offcanvasTop" aria-controls="offcanvasTop"',
            'label' => 'Search'
        ],
        'account' => [
            'icon' => get_template_directory_uri() . '/assets/account_icon.png',
            'url' => '/my-account',
            'label' => 'Account'
        ],
        
        'wishlist' => [
            'icon' => get_template_directory_uri() . '/assets/heart.png',
            'url' => $context['wishlist_url'],
            'label' => 'Wishlist'
        ],'cart' => [
            'icon' => get_template_directory_uri() . '/assets/shopping-bag.png',
            'url' => $context['cart_url'],
            'label' => 'Cart'
        ],
    ];

    $context['offcanvas'] = [
        'topright' => [
            'icon' => get_template_directory_uri() . '/assets/logo_offcanvas.png',
            'url' => '/',
            'label' => 'Home'
        ]
    ];



    //footer menu get menu items
    /*'footer-col-1'  => __( 'Footer Column 1', 'minimis' ),
        'footer-col-2'  => __( 'Footer Column 2', 'minimis' ),
        'footer-col-3'  => __( 'Footer Column 3', 'minimis' ),
    */
    
    //Main Menu
    $context['menu'] = Timber::get_menu('header-menu');

    $context['footer_col_1'] = footermenuitems('footer-col-1');
    $context['footer_col_2'] = footermenuitems('footer-col-2');
    $context['footer_col_3'] = footermenuitems('footer-col-3');

    $context['footer_be_part'] = __('Be part of the change,<br/>join our newsletter', 'minimis');


    // add product categories to context
    $context['filters_categories'] = products_categories();
    $context['filters_attributes'] = productAttributes();
    $context['filter_color_to_attribute'] = get_field('colors', 'option');
    $context['color_select_color'] = __('Color', 'minimis');
    //iterate through the array of filter_color_to_attribute
    // and if there is GET parameter in the url
    //add select=>selected to the color
    if (isset($_GET['color'])) {
        foreach ($context['filter_color_to_attribute'] as $key => $value) {
            if ($value['title'] == $_GET['color']) {
                $context['filter_color_to_attribute'][$key]['select'] = 'selected';
            }
        }

        $context['color_selected'] = $_GET['color'];    
    }


    
    // Featured image for singular posts/pages
    if (is_singular()) {
        $context['featured_image'] = get_the_post_thumbnail_url(null, 'large');
    }
    

    //get current page url 
    $context['current_url'] = home_url(strtok($_SERVER['REQUEST_URI'], '?'));


    return $context;
}

// Hook to filter if using Timber
add_filter('timber/context', 'minimis_prepare_context');





function products_categories(){
    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    );

    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
    );

    $terms = get_terms($args);

    $o = array();
    foreach ($terms as $t){

        $x = array(
            'count' => $t->count,
            'name' => $t->name,
            'link' => get_term_link($t->term_id),
            'slug' => $t->slug,
        );

        // Add the term to the array
        $o[] = $x;

    }

    // All categories
    $a = [
        'count' => 333,
        'name' => __('All', 'minimis'),
        'link' => '/shop',
        'slug' => 'all',
    ];

    // Add the "All Categories" item to the beginning of the array
    array_unshift($o, $a);

    //print_r($o);
    return $o;
}

function productAttributes(){
    //pa_bottle-color
    $args = array(
        'taxonomy'   => 'pa_bottle-color',
        'hide_empty' => false,
    );
    $terms = get_terms($args);
    $o = array();
    foreach ($terms as $t){

        $x = array(
            'count' => $t->count,
            'name' => $t->name,
            'link' => get_term_link($t->term_id),
        );

        // Add the term to the array
        $o[] = $x;

    }
    // return the array of terms
    return $o;
}

