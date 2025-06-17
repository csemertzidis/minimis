<?php

// function woomenuitems()
// {

//     $out = array();

//     $out['categories'] = prepareCategories();

//     return $out;
// }

// function prepareCategories()
// {

//     //creates an array of woocommerce categories with their link
//     $args = array(
//         'taxonomy' => 'product_cat',
//         'hide_empty' => false,
//     );

//     $categories = get_terms($args);

//     foreach ($categories as $category) {

//         //get products of category
//         $args = array(
//             'post_type' => 'product',
//             'posts_per_page' => -1,
//             'product_cat' => $category->slug,
//         );

//         $products = count(get_posts($args));


//         $cat_out[] = array(
//             'name' => $category->name,
//             'link' => get_term_link($category->term_id),
//             'count' => $products,
//         );
//     }

//     //pop up the uncategorized category
//     $uncategorized = array_shift($cat_out);

//     return $cat_out;
// }