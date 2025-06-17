<?php

function mainslider($block, $content = '', $is_preview = false)
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
    //generate a uuid

    //print_r($context['fields']);

    $slides = array();
    foreach ($context['fields']['slider'] as $slide) {

        if ($slide['image_or_video'] == 1) {
            $slide_item = array();
            $slide_item['type'] = 'image';
            $slide_item['image'] = $slide['image']['url'];
            //srcset
            $slide_item['srcset'] = wp_get_attachment_image_srcset($slide['image']['id'], 'medium');

            //alt
            $slide_item['alt'] = get_post_meta($slide['image']['id'], '_wp_attachment_image_alt', TRUE) ? get_post_meta($slide['image']['id'], '_wp_attachment_image_alt', TRUE) : "MINIMIS SLIDER IMAGE";

            //sizes
            $slide_item['sizes'] = wp_get_attachment_image_sizes($slide['image']['id'], 'large');

        }

        if ($slide['image_or_video'] == 0) {
            $slide_item = array();
            $slide_item['type'] = 'video';
            $slide_item['video'] = $slide['video'];
            $slide_item['duration'] = $slide['video_duration'] * 1000;
        }
        array_push($slides, $slide_item);
    }

    $context['slides'] = $slides;

    slider_enqueue_scripts();
    // Render the block view.
    Timber::render('/blocks/mainslider/mainslider.twig', $context);
}

// enqueue mainslider.js
function slider_enqueue_scripts()
{
    wp_enqueue_script('mainslider', get_template_directory_uri() . '/inc/blocks/mainslider/mainslider.js', array('swiper'), '1.0.0', true);
}
