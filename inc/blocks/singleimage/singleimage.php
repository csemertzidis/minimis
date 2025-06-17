<?php

function singleimage($block, $content = '', $is_preview = false){
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    $im = get_field('image');

    $image = array();

    $image['url'] = $im['url'];
    $image['alt'] = $im['alt'];
    //srcset
    $image['srcset'] = wp_get_attachment_image_srcset($im);

    $context['image'] = $image;

    Timber::render('/blocks/singleimage/singleimage.twig', $context);
}