<?php

function productsblock($block, $content = '', $is_preview = false)
{
    // Enqueue the productsblock JavaScript file
    wp_enqueue_script('productsblock-js', get_template_directory_uri() . '/js/productsblock.js', array('jquery'), '1.0.0', true);
    
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    //print_r($context['fields']);

    $products = array();

    foreach ($context['fields']['products'] as $product) {
        
        $p_output = array();

        $prod = get_post($product);
        $p_output['id'] = $prod->ID;
        $p_output['link'] = get_permalink($prod->ID);
        $p_output['image'] = get_the_post_thumbnail_url($prod->ID, 'full');
       
        //alt
        $p_output['alt'] = get_post_meta(get_post_thumbnail_id($prod->ID), '_wp_attachment_image_alt', true);

        //srcset
        $p_output['srcset'] = wp_get_attachment_image_srcset(get_post_thumbnail_id($prod->ID), 'large');


       
        array_push($products, $p_output);
    }

    $context['products'] = $products;

    //loadmore
    $context['loadmore'] = __('Load more', 'textdomain');

   // print_r($context['products']);

     Timber::render('/blocks/productsblock/productsblock.twig', $context);
}