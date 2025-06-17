<?php

function textwithbottle($block, $content = '', $is_preview = false)
{
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['content'] = $content;
    $context['is_preview'] = $is_preview;

    //print ($context['fields']['content']);

    //print_r($context['fields']);
    //render the block
    Timber::render('/blocks/textwithbottle/textwithbottle.twig', $context);
}