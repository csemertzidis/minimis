<?php

function listblock($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    // print_r($context['fields']);

    $items = array();
    foreach ($context['fields']['items'] as $item) {
        $item_o = array();

        $item_o['image'] = $item['image']['url'];
        $item_o['srcset'] = wp_get_attachment_image_srcset($item['image']['id'], 'medium');
        $item_o['alt'] = get_post_meta($item['image']['id'], '_wp_attachment_image_alt', TRUE) ? get_post_meta($item['image']['id'], '_wp_attachment_image_alt', TRUE) : "image of " . $item['title'] . " color jewelry";
        $item_o['sizes'] = "100vw";
        $item_o['title'] = $item['title'];
        $item_o['subtitle'] = $item['subtitle'];
        $item_o['link'] = $item['link'] ? $item['link'] : '#';

        array_push($items, $item_o);
    }
    $context['items'] = $items;

    //render the block
    Timber::render('/blocks/listblock/listblock.twig', $context);
}