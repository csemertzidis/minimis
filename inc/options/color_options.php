<?php

/**
 * Register a Colors options page using Advanced Custom Fields (ACF)
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Colors',
        'menu_title' => 'Colors',
        'menu_slug' => 'colors',
        'capability' => 'edit_posts',
        'position' => 3,
        'icon_url' => 'dashicons-art',
        'redirect' => false
    ));
}

//
