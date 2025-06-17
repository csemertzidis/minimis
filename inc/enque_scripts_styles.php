<?php

//styles to enqueue

function theme_styles_and_js()
{

    $v = time();
    //node swiper js from node modules
    wp_enqueue_script('swiper', get_template_directory_uri() . '/node_modules/swiper/swiper-bundle.min.js', array('jquery'), '11.1.15', true);


    wp_enqueue_style('main', get_template_directory_uri() . '/css/style.css', array(), $v, 'all');

    //bootstrap from node
    //wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/node_modules/bootstrap/dist/css/bootstrap.min.css', array(), null, 'all' );


    //bootrap js from node
    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js', array('jquery'), '2.11.8', array('strategy' => 'defer'));
    //wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', array('jquery','popper'), null, array( 'strategy' => 'defer' ) );

    //enqueue wishlist
    wp_enqueue_script('wishlist-script', get_template_directory_uri() . '/js/wishlist.js', array('jquery'), '1.0', true, array('strategy' => 'defer')); // Replace '1.0' with your script's version number
    wp_localize_script(
        'wishlist-script',
        'ajax_object',
        array('ajax_url' => admin_url('admin-ajax.php', 'relative'))
    );// Add 'relative' to get the correct path

    // enqueue script js/search.js
    wp_enqueue_script('search', get_template_directory_uri() . '/js/search.js', array('jquery'), '1.0', true, array('strategy' => 'defer')); // Replace '1.0' with your script's version number
    wp_localize_script(
        'search_minimis',
        'ajax_object',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );



    //swiper css from node modules
    wp_enqueue_style('swiper-css', get_template_directory_uri() . '/node_modules/swiper/swiper-bundle.min.css', array(), '11.1.15', 'all');


}

add_action('wp_enqueue_scripts', 'theme_styles_and_js');


add_action('wp_enqueue_scripts', 'remove_wp_block_library_css');

function remove_wp_block_library_css()
{
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style(
        'wp-block-library-theme'
    );
    wp_dequeue_style('global-styles'); // If you want to remove Gutenberg's global styles as well
    wp_dequeue_style('wp-block-library-css');
    wp_dequeue_style('wp-block-library-min-css');
}

//remove wmpl block style css
define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);

add_action('wp_enqueue_scripts', 'test_remove_style', 11, 2);
function test_remove_style()
{
    wp_dequeue_style('wpml-blocks');
}




function dequeue_woocommerce_small_screen_css()
{

    // Remove the general WooCommerce stylesheet
    wp_dequeue_style('woocommerce-general');

    // Remove the WooCommerce layout stylesheet
    wp_dequeue_style('woocommerce-layout');

    wp_dequeue_style('woocommerce-smallscreen');
}
add_action('wp_enqueue_scripts', 'dequeue_woocommerce_small_screen_css', 100);



//Remove jQuery Migrate
function remove_jquery_Migrate($scripts)
{
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        if ($script->deps) {
            // Check whether the script has any dependencies
            $script->deps = array_diff($script->deps, array('jquery-Migrate'));
        }
    }
}

add_action('wp_default_scripts', 'remove_jquery_Migrate');




// //dequeue flexible shipping css

// add_action('wp_print_styles', 'dequeue_flexible_shipping_css');
// function dequeue_flexible_shipping_css()
// {
//     if (!is_checkout()) {
//         wp_dequeue_style('flexible-shipping-frontend');
//     }

// }




//enqueue https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js
function enqueue_spectrum_js()
{
    wp_enqueue_script('spectrum-js', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js', array('jquery'), '1.8.0', true);
    wp_enqueue_style('spectrum-css', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css', array(), '1.8.0');
}
add_action('wp_enqueue_scripts', 'enqueue_spectrum_js');
