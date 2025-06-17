<?php

function recycledbottles($block, $content = '', $is_preview = false){
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    $context['subtitle'] = __('Already recycled bottles', 'minimis');

    Timber::render('/blocks/recycledbottles/recycledbottles.twig', $context);

    
}


