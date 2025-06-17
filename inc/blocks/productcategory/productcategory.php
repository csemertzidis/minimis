<?php

// Ensure ACF plugin is active
if (!function_exists('get_fields')) {
    return;
}

function productcategory($block, $content = '', $is_preview = false)
{

    $folder = get_template_directory_uri() . '/inc/custom_blocks/heroSlider/';

    //enqueue styles and scripts
    // slider_enqueue_styles($folder);
    // slider_enqueue_scripts();

    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    //print_r($context['fields']);

    $items = array();
    foreach ($context['fields']['product_categories'] as $category) {
        $term_obj = get_term($category, 'product_cat');
        //print_r($term_obj);
        // Get ACF fields of the term
        $image = get_field('image', 'term_' . $term_obj->term_id);

        $item = array();
        $item['name'] = $term_obj->name;

        //limit description to 20 words
        $item['description'] = wp_trim_words($term_obj->description, 20, '...');

        $item['link'] = get_term_link($term_obj);
        $item['image'] = $image['url'];
        $item['srcset'] = wp_get_attachment_image_srcset($image['id'], 'medium');

        $item['alt'] = get_post_meta($image['id'], '_wp_attachment_image_alt', TRUE) ? get_post_meta($image['id'], '_wp_attachment_image_alt', TRUE) : "Image for the category " . $term_obj->name;

        array_push($items, $item);

    }

    //print_r($items);

    $context['items'] = $items;


    // Render the block view.
    Timber::render('/blocks/productcategory/productcategory.twig', $context);
}
