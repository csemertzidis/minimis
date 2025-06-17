<?php

function blackpoints($block, $content = '', $is_preview = false){
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    foreach ($context['fields']['points'] as $point) {

    $img = $point['icon'];
    // Get the image URL
    $img_url = isset($img['url']) ? $img['url'] : '';
    $img_alt = isset($img['alt']) ? $img['alt'] : '';
    
    // Generate srcset if the image ID is available
    $srcset = '';
    if (isset($img['ID'])) {
      $srcset = wp_get_attachment_image_srcset($img['ID'], 'full');
    }
    
    $context['items'][] = array(
        'title' => $point['title'],
        'text' => $point['text'],
        'icon' => $img_url,
        'srcset' => $srcset,
        'alt' => $img_alt,
    );
    }

   //print_r($context['items']);
    Timber::render('/blocks/blackpoints/blackpoints.twig', $context);
}