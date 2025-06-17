<?php


function video($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    //print_r($context['fields']);

    Timber::render('/blocks/video/video.twig', $context);
}