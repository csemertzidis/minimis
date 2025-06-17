<?php

function headera($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;



    //render the block
    Timber::render('/blocks/headera/headera.twig', $context);
}