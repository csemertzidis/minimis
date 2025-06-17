<?php

if (!class_exists('Timber')) {
    echo 'Timber not activated. Make sure you activate the plugin in <a href="/wp-admin/plugins.php#timber">/wp-admin/plugins.php</a>';

    return;
}

$context = Timber::context();
$context['sidebar'] = Timber::get_widgets('shop-sidebar');

if (is_singular('product')) {

    ///////////////////////////////////////////////////
    ///////// SINGLE PRODUCT PAGE /////////////////////
    ///////////////////////////////////////////////////

    $context['post'] = Timber::get_post();
    $product = wc_get_product($context['post']->ID);
    $context['product']['price'] = $product->get_price_html();
    //sku
    $context['product']['sku'] = $product->get_sku();

    //category of the product
    $context['product']['terms'] = get_the_terms($context['post']->ID, 'product_cat');

    // Get related products
    $related_limit = wc_get_loop_prop('columns');
    $related_ids = wc_get_related_products($context['post']->id, $related_limit);
    $context['related_products'] = Timber::get_posts($related_ids);

    // Restore the context and loop back to the main query loop.
    wp_reset_postdata();

    Timber::render('views/woo/single-product.twig', $context);
} else {

    ///////////////////////////////////////////////////
    ///////// PRODUCT ARCHIVE PAGE ////////////////////
    ///////////////////////////////////////////////////

    $context['user'] = get_my_user();

    
    

    $args= array(
        'post_type' => 'product',
        'posts_per_page' => -1, // Show all products
    );

    if (isset($_GET['category']) && $_GET['category'] != '' && $_GET['category'] != 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['category']),
            ),
        );

        $context['category_selected'] = $_GET['category'];
    }

    $total_products = Timber::get_posts($args);
    $context['total_products'] = count($total_products);
    //pages
    $context['total_pages'] = ceil($context['total_products'] / 12); // Assuming 12 products per page
    $context['current_page'] = get_query_var('paged') ? get_query_var('paged') : 1;
    $context['current_url'] = home_url(strtok($_SERVER['REQUEST_URI'], '?'));
    

    //paged
    if (get_query_var('paged')) {
        $args['paged'] = get_query_var('paged');
        //limit the number of products per page
        $args['posts_per_page'] = 12;
    } else {
        $args['paged'] = 1;
        $args['posts_per_page'] = 12;
    }


    $posts = Timber::get_posts($args);
    
    
    if (isset($_GET['color']) && $_GET['color'] != '') {
        
        foreach ($context['filter_color_to_attribute'] as $c){
                if ($c['title'] == $_GET['color']){
                    $bottles = $c['bottle_colors'];
                }
        }
        $bottles_terms = array();
        foreach ($bottles as $bottle){
            $bottles_terms[] = $bottle->term_id;
        }

        $context['products'] = archive_page_products_with_color($posts, $bottles_terms);

    }
    else{
        $context['products'] = archive_page_products($posts);
    }

    
   
    
   

   

    
    

   

   Timber::render('views/woo/archive.twig', $context);


   
}





/*
////////////////////////////////////////////////////////////////////
///////// ADDITIONAL CHECKS FOR FILTERED ARCHIVES //////////////////
////////////////////////////////////////////////////////////////////

if ( is_shop() ) {
    // Main shop page
} elseif ( is_product_category() ) {
    // Product category archive
} elseif ( is_product_tag() ) {
    // Product tag archive
} elseif ( is_tax() && taxonomy_is_product_attribute( get_queried_object()->taxonomy ) ) {
    // Product attribute archive
} elseif ( is_search() ) {
    // Product search results
} 
// ... (Add checks for any custom filtered archives if applicable)

*/



