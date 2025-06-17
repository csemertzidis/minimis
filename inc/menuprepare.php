<?php

//register menu locations
register_nav_menus(
    array(
        'header-menu' => __('Header Menu'),
        'footer-menu' => __('Footer Menu'),
        'mobile-menu' => __('Mobile Menu'),
        'footer-col-1'  => __( 'Footer Column 1', 'minimis' ),
        'footer-col-2'  => __( 'Footer Column 2', 'minimis' ),
        'footer-col-3'  => __( 'Footer Column 3', 'minimis' ),
    )
);



function getmenuitems($menulocation)
{
    $menu_items = array();
    $locations = get_nav_menu_locations();
    $menu = wp_get_nav_menu_object($locations[$menulocation]);
    $menu_items = wp_get_nav_menu_items($menu->term_id);
    return $menu_items;
}

function footermenuitems($menulocation)
{
    $menu_items = array();
    $locations = get_nav_menu_locations();
    $menu = wp_get_nav_menu_object($locations[$menulocation]);
    $menu_items = wp_get_nav_menu_items($menu->term_id);

    $out = array();
    foreach ($menu_items as $item) {
        
        $out[$item->ID] = array(
            'title' => $item->title,
            'url' => $item->url,
            'description' => $item->description,
            'target' => $item->target,
            'classes' => $item->classes,
        
        );

    }

    return $out;
}