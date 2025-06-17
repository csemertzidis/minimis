<?php

function productcarousel($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    $items = array();
    foreach ($context['fields']['products'] as $prod) {

        $item = array();
        $product = wc_get_product($prod);

        //check if is simple or variable
        if ($product->is_type('simple')) {
            $item['id'] = $product->get_id();
            $item['name'] = $product->get_name();
            $item['price'] = $product->get_price();
            $item['price_html'] = $product->get_price_html();
            $item['image'] = wp_get_attachment_image_src($product->get_image_id(), 'medium')[0];
            $item['alt'] = get_post_meta($product->get_image_id(), '_wp_attachment_image_alt', TRUE) ? get_post_meta($product->get_image_id(), '_wp_attachment_image_alt', TRUE) : $product->get_name();
            $item['link'] = get_permalink($product->get_id());
            //get the product categories
            $item['categories'] = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
        } else {
            $item['id'] = $product->get_id();
            $item['name'] = $product->get_name();
            $item['price'] = $product->get_price();
            $item['price_html'] = $product->get_price_html();
            $item['image'] = wp_get_attachment_image_src($product->get_image_id(), 'medium')[0];
            $item['alt'] = get_post_meta($product->get_image_id(), '_wp_attachment_image_alt', TRUE) ? get_post_meta($product->get_image_id(), '_wp_attachment_image_alt', TRUE) : $product->get_name();
            $item['link'] = get_permalink($product->get_id());
            //get the product categories
            $item['categories'] = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
        }

        array_push($items, $item);

    }



    $context['items'] = $items;
    productcarousel_js();
    //render the block
    Timber::render('/blocks/productcarousel/productcarousel.twig', $context);
}

function productcarousel_js()
{
    wp_enqueue_script('productcarousel-js', get_template_directory_uri() . '/inc/blocks/productcarousel/productcarousel.js', array('swiper'), '1.0.0', true);
}
