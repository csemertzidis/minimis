<?php

function contentblock($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    //print ($context['fields']['content']);

    //render the block
    Timber::render('/blocks/contentblock/contentblock.twig', $context);
}