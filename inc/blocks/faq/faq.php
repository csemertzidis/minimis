<?php


function faq($block, $content = '', $is_preview = false){

    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    Timber::render('/blocks/faq/faq.twig', $context);
}