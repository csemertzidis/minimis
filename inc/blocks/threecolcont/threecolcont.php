<?php

function threecolcont($block, $content = '', $is_preview = false){
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    $image = array();

    $image['url'] = get_field('image')['url'];
    $image['alt'] = get_field('image')['alt'];
    //srcset
    $image['srcset'] = wp_get_attachment_image_srcset(get_field('image'));

    $context['image'] = $image;

   // print_r($context['image']);

    Timber::render('/blocks/threecolcont/threecolcont.twig', $context);
}