<?php

function fourproducts($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;


    $products = array();

    foreach ($context['fields']['products'] as $p) {
       $p_out = array();
         $product = wc_get_product($p);
       $p_out['id'] = $product->id;
       $p_out['image'] = get_the_post_thumbnail_url($product->id, 'large');
       $p_out['srcset'] = get_post_meta($product->id, 'product_set', true);
       $p_out['permalink'] = get_permalink($product->id);

       array_push($products, $p_out);
    }

   // print_r($products);

    $context['products'] = $products;


    Timber::render('/blocks/fourproducts/fourproducts.twig', $context);
}